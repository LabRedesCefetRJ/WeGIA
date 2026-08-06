<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ApiContribuicoesServiceInterface.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'GatewayPagamentoDAO.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

/**
 * Consulta os pagamentos registrados no Mercado Pago (Pix, Boleto, Cartão de
 * Crédito e Carnê — todos criados em /v1/payments) e devolve os que estão
 * pagos, para que ContribuicaoLogController::sincronizarStatus() atualize o
 * status das contribuições correspondentes no banco de dados local.
 *
 * Sem esta classe, os pagamentos feitos via Mercado Pago nunca são marcados
 * como pagos no sistema (o mesmo mecanismo já existe para a Pagar.me em
 * PagarMeContribuicoesService.php).
 */
class MercadoPagoContribuicoesService implements ApiContribuicoesServiceInterface
{
    // sincronizarStatus() chama getContribuicoes('paid') usando a nomenclatura da Pagar.me;
    // aqui traduzimos isso para o status equivalente da API do Mercado Pago ('approved').
    private const STATUS_SINONIMOS_PAGOS = ['paid', 'approved', 'accredited'];

    public function getContribuicoes(?string $status): ContribuicaoLogCollection
    {
        $contribuicaoLogCollection = new ContribuicaoLogCollection();

        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewaysPagamento = $gatewayPagamentoDao->buscarPorPlataforma('MercadoPago');

            if (!$gatewaysPagamento || empty($gatewaysPagamento)) {
                return $contribuicaoLogCollection;
            }

            $statusMercadoPago = null;
            if (!is_null($status)) {
                $statusMercadoPago = in_array(strtolower($status), self::STATUS_SINONIMOS_PAGOS, true)
                    ? 'approved'
                    : $status;
            }

            // Janela de análise: últimos 12 meses (mesmo critério usado no PagarMeContribuicoesService)
            $dataInicio = (new DateTime())->modify('-12 months')->format('Y-m-d\TH:i:s.000P');
            $dataFim = (new DateTime())->format('Y-m-d\TH:i:s.000P');

            foreach ($gatewaysPagamento as $gatewayPagamento) {
                $pagamentos = $this->buscarPagamentos($gatewayPagamento, $statusMercadoPago, $dataInicio, $dataFim);

                foreach ($pagamentos as $pagamento) {
                    if (empty($pagamento['id']) || empty($pagamento['date_approved'])) {
                        continue;
                    }

                    $dataPagamento = DateTime::createFromFormat(DateTime::ATOM, $pagamento['date_approved']);

                    if (!$dataPagamento instanceof DateTime) {
                        continue;
                    }

                    $contribuicaoLog = new ContribuicaoLog();
                    $contribuicaoLog
                        ->setCodigo((string) $pagamento['id'])
                        ->setDataPagamento($dataPagamento->format('Y-m-d H:i:s'));

                    $contribuicaoLogCollection->add($contribuicaoLog);
                }
            }

            return $contribuicaoLogCollection;
        } catch (Throwable $e) {
            Util::tratarException($e);
            exit();
        }
    }

    /**
     * Consulta GET /v1/payments/search paginando até obter todos os resultados
     */
    private function buscarPagamentos(GatewayPagamento $gatewayPagamento, ?string $status, string $dataInicio, string $dataFim): array
    {
        $baseUrl = rtrim($gatewayPagamento->getEndpoint(), '/'); // ex: https://api.mercadopago.com/v1/payments
        $searchUrl = str_ends_with($baseUrl, '/search') ? $baseUrl : $baseUrl . '/search';

        $limit = 50;
        $offset = 0;
        $total = 0;
        $pagamentos = [];

        do {
            $query = [
                'sort' => 'date_created',
                'criteria' => 'desc',
                'range' => 'date_created',
                'begin_date' => $dataInicio,
                'end_date' => $dataFim,
                'limit' => $limit,
                'offset' => $offset,
            ];

            if (!is_null($status)) {
                $query['status'] = $status;
            }

            $url = $searchUrl . '?' . http_build_query($query);

            $headers = [
                'Authorization: Bearer ' . $gatewayPagamento->getToken(),
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $erro = curl_error($ch);
                curl_close($ch);
                throw new PaymentServiceException(
                    'Não foi possível sincronizar os status de pagamento no momento.',
                    'Erro cURL ao consultar pagamentos na API Mercado Pago: ' . $erro,
                    502
                );
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode((string) $response, true);

            if ($httpCode !== 200 || !is_array($data) || !isset($data['results'])) {
                throw new PaymentServiceException(
                    'Não foi possível sincronizar os status de pagamento no momento.',
                    "A API Mercado Pago retornou o código de status HTTP $httpCode ao consultar pagamentos.",
                    $httpCode
                );
            }

            $pagamentos = array_merge($pagamentos, $data['results']);
            $total = $data['paging']['total'] ?? count($pagamentos);
            $offset += $limit;
        } while ($offset < $total);

        return $pagamentos;
    }
}