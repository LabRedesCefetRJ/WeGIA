<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ApiContribuicoesServiceInterface';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'GatewayPagamentoDAO.php';

class PagarMeContribuicoesService implements ApiContribuicoesServiceInterface
{
    public function getContribuicoes(?string $status): ContribuicaoLogCollection
    {
        $url = 'https://api.pagar.me/core/v5/orders?page=1&size=30';

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

        $url .= "&created_until=$dataAnaliseFormatada";

        // Realizar requisições
        $pedidosArray = $this->requisicaoPedidos($url);

        // Transformar os pedidos na estrutura de uma ContribuicaoLog

        // Retornar contribuições
        $contribuicaoLogCollection = new ContribuicaoLogCollection();
        
        return $contribuicaoLogCollection;
    }

    private function requisicaoPedidos(string $url): mixed
    {
        $pedidosTotais = [];

        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamento = $gatewayPagamentoDao->buscarPorId(1);
        } catch (PDOException $e) {
            echo 'Erro: ' . $e->getMessage();
            exit();
        }

        $headers = [
            'Authorization: Basic ' . base64_encode($gatewayPagamento['token'] . ':'),
            'Content-Type: application/json;charset=utf-8',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "Erro: " . curl_error($ch);
            curl_close($ch);
            exit();
        }

        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data['data'])) {
            echo "Erro: Resposta inválida da API.";
            curl_close($ch);
            exit();
        }

        $this->atribuirPedidos($pedidosTotais, $data['data']);

        // Paginação
        $parsedUrl = parse_url($url);
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
                    echo "Erro: " . curl_error($ch);
                    curl_close($ch);
                    exit();
                }

                $data = json_decode($response, true);
                if (!is_array($data) || !isset($data['data'])) {
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
