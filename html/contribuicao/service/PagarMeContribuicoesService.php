<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ApiContribuicoesServiceInterface.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'GatewayPagamentoDAO.php';

class PagarMeContribuicoesService implements ApiContribuicoesServiceInterface
{
    private $pedidosArray = [];

    //Aproveitar função abaixo
    public function getContribuicoes(?string $status): ContribuicaoLogCollection
    {
        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewaysPagamento = $gatewayPagamentoDao->buscarPorPlataforma('PagarMe');

            if (!$gatewaysPagamento || empty($gatewaysPagamento)) {
                http_response_code(400);
                echo json_encode(['erro' => 'Gateway de pagamento não encontrado no sistema']);
                exit();
            }

            foreach ($gatewaysPagamento as $gatewayPagamento) {

                //verificar endpoint
                $endpointFragmentado = explode('/', $gatewayPagamento->getEndpoint());

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

                if (end($endpointFragmentado) === 'orders') {
                    // Realizar requisições
                    $this->atribuirPedidos($this->pedidosArray, $this->requisicaoPedidos($gatewayPagamento));
                } elseif (end($endpointFragmentado) === 'subscriptions') {
                    $this->atribuirPedidos($this->pedidosArray, $this->getInvoices($gatewayPagamento));
                }
            }

            // Transformar os pedidos na estrutura de uma ContribuicaoLog
            $contribuicaoLogCollection = new ContribuicaoLogCollection();
            foreach ($this->pedidosArray as $pedido) {
                $contribuicaoLog = new ContribuicaoLog();

                if (key_exists('subscription', $pedido)) {
                    $contribuicaoLog->setCodigo($pedido['subscription']['id']);
                    //transformar a data de pagamento para a estrtutura aceita pelo MySQL
                    $dataPagamento = DateTime::createFromFormat(DateTime::ATOM, $pedido['charge']['paid_at'])->format('Y-m-d H:i:s');
                } else {
                    $contribuicaoLog->setCodigo($pedido['id']);
                    //transformar a data de pagamento para a estrtutura aceita pelo MySQL
                    $dataPagamento = DateTime::createFromFormat(DateTime::ATOM, $pedido['charges'][0]['paid_at'])->format('Y-m-d H:i:s');
                }

                $contribuicaoLog->setDataPagamento($dataPagamento);
                $contribuicaoLogCollection->add($contribuicaoLog);
            }

            // Retornar contribuições
            return $contribuicaoLogCollection;
        } catch (PDOException $e) {
            error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
            http_response_code(500);
            echo json_encode(['erro' => 'Problema no servidor']);
            exit();
        }
    }

    /**Retorna as faturas do gateway de pagamento */
    public function getInvoices(GatewayPagamento $gatewayPagamento):array
    {
        $gatewayPagamento->setEndpoint(str_replace('subscriptions', 'invoices', $gatewayPagamento->getEndpoint()));
        return $this->requisicaoPedidos($gatewayPagamento);
    }

    private function requisicaoPedidos(GatewayPagamento $gatewayPagamento)
    {
        $pedidosArray = [];

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

        $this->atribuirPedidos($pedidosArray, $data['data']);

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

                $this->atribuirPedidos($pedidosArray, $data['data']);
            }
        }

        curl_close($ch);

        return $pedidosArray;
    }

    private function atribuirPedidos(array &$pedidosTotais, array $pedidosRequisicao): void
    {
        $pedidosTotais = array_merge($pedidosTotais, $pedidosRequisicao);
    }
}
