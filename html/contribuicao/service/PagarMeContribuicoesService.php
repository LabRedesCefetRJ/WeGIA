<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ApiContribuicoesServiceInterface.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'GatewayPagamentoDAO.php';

class PagarMeContribuicoesService implements ApiContribuicoesServiceInterface
{
    public function getContribuicoes(?string $status): ContribuicaoLogCollection
    {
        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamento = $gatewayPagamentoDao->buscarPorPlataforma('PagarMe');

            if (!$gatewayPagamento) {
                http_response_code(400);
                echo json_encode(['erro' => 'Gateway de pagamento não encontrado no sistema']);
                exit();
            }

            $url = $gatewayPagamento->getEndpoint() . '?page=1&size=30';

            // Verificar o parâmetro de status
            if (!is_null($status)) {
                $url .= "&status=$status";
            }

            // Definir o período de tempo de análise
            $dataAtual = new DateTime();
            $anoAtual = intval($dataAtual->format('Y'));

            $anoAnalise = $anoAtual - 1;
            $dataAnalise = new DateTime("{$anoAnalise}-12-01");
            $dataAnaliseFormatada = $dataAnalise->format('Y-m-d');

            $url .= "&created_since=$dataAnaliseFormatada";

            $gatewayPagamento->setEndpoint($url);

            // Realizar requisições
            $pedidosArray = $this->requisicaoPedidos($gatewayPagamento);

            // Transformar os pedidos na estrutura de uma ContribuicaoLog
            $contribuicaoLogCollection = new ContribuicaoLogCollection();
            foreach ($pedidosArray as $pedido) {
                $contribuicaoLog = new ContribuicaoLog();
                $contribuicaoLog->setCodigo($pedido['id']);

                //transformar a data de pagamento para a estrtutura aceita pelo MySQL
                $dataPagamento = DateTime::createFromFormat(DateTime::ATOM, $pedido['updated_at'])->format('Y-m-d H:i:s');

                $contribuicaoLog->setDataPagamento($dataPagamento);
                $contribuicaoLogCollection->add($contribuicaoLog);
            }

            // Retornar contribuições
            return $contribuicaoLogCollection;
        } catch (PDOException $e) {
            http_response_code(500);
            //adicionar sistema de armazenamento de logs de erro posteriomente
            echo json_encode(['erro' => 'Problema no servidor']);
            exit();
        }
    }

    private function requisicaoPedidos(GatewayPagamento $gatewayPagamento): mixed
    {
        $pedidosTotais = [];

        $headers = [
            'Authorization: Basic ' . base64_encode($gatewayPagamento->getToken() . ':'),
            'Content-Type: application/json;charset=utf-8',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $gatewayPagamento->getEndpoint());

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            http_response_code(500);
            echo "Erro: " . curl_error($ch);
            curl_close($ch);
            exit();
        }

        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data['data'])) {
            http_response_code(500);
            echo "Erro: Resposta inválida da API.";
            curl_close($ch);
            exit();
        }

        $this->atribuirPedidos($pedidosTotais, $data['data']);

        // Paginação
        $parsedUrl = parse_url($gatewayPagamento->getEndpoint());
        parse_str($parsedUrl['query'], $queryParams);

        $size = intval($queryParams['size']);
        $totalPedidos = isset($data['paging']['total']) ? intval($data['paging']['total']) : 0;
        $paginasQtd = ceil($totalPedidos / $size);

        if ($paginasQtd > 1) {
            for ($i = 2; $i <= $paginasQtd; $i++) {
                $queryParams['page'] = $i;
                $novaUrl = "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$parsedUrl['path']}?" . http_build_query($queryParams);
                curl_setopt($ch, CURLOPT_URL, $novaUrl);
                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    echo http_response_code(500);
                    echo "Erro: " . curl_error($ch);
                    curl_close($ch);
                    exit();
                }

                $data = json_decode($response, true);
                if (!is_array($data) || !isset($data['data'])) {
                    echo http_response_code(500);
                    echo "Erro: Resposta inválida da API.";
                    curl_close($ch);
                    exit();
                }

                $this->atribuirPedidos($pedidosTotais, $data['data']);
            }
        }

        curl_close($ch);
        return $pedidosTotais;
    }

    private function atribuirPedidos(array &$pedidosTotais, array $pedidosRequisicao): void
    {
        $pedidosTotais = array_merge($pedidosTotais, $pedidosRequisicao);
    }
}
