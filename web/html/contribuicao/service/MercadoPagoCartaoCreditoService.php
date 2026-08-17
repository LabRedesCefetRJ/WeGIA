<?php
require_once 'ApiCartaoCreditoServiceInterface.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once '../model/ContribuicaoLog.php';
require_once '../dao/GatewayPagamentoDAO.php';
require_once 'MercadoPagoCardTokenTrait.php';

/**
 * Implementação do processamento de cartão de crédito via API do Mercado Pago.
 *
 * Fluxo (Checkout API / Core Methods), ambas as chamadas no mesmo host do
 * endPoint cadastrado para o gateway — nada de URL fixa no código, igual ao
 * restante do sistema:
 *  1) Gera um card_token a partir dos dados do cartão (POST /v1/card_tokens);
 *  2) Cria o pagamento usando o token gerado (POST /v1/payments), no mesmo
 *     endPoint já cadastrado para o gateway (o mesmo utilizado pelo Pix/Boleto).
 *
 * IMPORTANTE (segurança): o ideal, recomendado pelo próprio Mercado Pago, é
 * gerar o card_token no FRONT-END com o SDK MercadoPago.js / Secure Fields,
 * para que o número do cartão e o CVV nunca trafeguem pelo seu servidor
 * (reduz o escopo de PCI-DSS). Esta implementação segue o mesmo padrão já
 * usado em PagarMeCartaoCreditoService.php (dados brutos do cartão chegam
 * via POST ao backend) para manter compatibilidade com o formulário atual
 * (view/components/contribuicao_cartao.php). Se possível, migre para
 * tokenização no front-end futuramente.
 */
class MercadoPagoCartaoCreditoService implements ApiCartaoCreditoServiceInterface
{
    use MercadoPagoCardTokenTrait;

    public function processarCartaoCredito(ContribuicaoLog $contribuicaoLog)
    {
        $gatewayPagamentoDao = new GatewayPagamentoDAO();
        $gatewayPagamento = $gatewayPagamentoDao->buscarPorId($contribuicaoLog->getGatewayPagamento()->getId());

        if (!$gatewayPagamento) {
            throw new PaymentServiceException(
                'Não foi possível processar o pagamento com cartão de crédito no momento.',
                'Gateway de pagamento Mercado Pago não encontrado ou inativo.',
                502
            );
        }

        $accessToken = $gatewayPagamento['token'];
        $endpoint = $gatewayPagamento['endPoint']; // ex: https://api.mercadopago.com/v1/payments

        // Mesmo host do endPoint cadastrado para o gateway, igual ao padrão usado em
        // MercadoPagoContribuicoesService.php — não fica fixo em api.mercadopago.com.
        $host = parse_url($endpoint, PHP_URL_HOST) ?: 'api.mercadopago.com';
        $urlCardTokens = 'https://' . $host . '/v1/card_tokens';

        // Dados do cartão informados no formulário
        $cardNumber = preg_replace('/\D/', '', (string) filter_input(INPUT_POST, 'card_number'));
        $cardExpMonth = filter_input(INPUT_POST, 'card_exp_month');
        $cardExpYear = filter_input(INPUT_POST, 'card_exp_year');
        $cardHolderName = filter_input(INPUT_POST, 'card_holder_name');
        $cardCvv = filter_input(INPUT_POST, 'card_cvv');

        if (!$cardNumber || !$cardExpMonth || !$cardExpYear || !$cardHolderName || !$cardCvv) {
            throw new PaymentServiceException(
                'Não foi possível processar o pagamento com cartão de crédito no momento.',
                'Dados do cartão de crédito incompletos.',
                400
            );
        }

        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());
        $anoExpiracao = strlen((string) $cardExpYear) === 2 ? '20' . $cardExpYear : (string) $cardExpYear;

        // 1) Gerar o token do cartão. A API de pagamentos do Mercado Pago não aceita
        // o número do cartão em texto puro; é obrigatório usar um token.
        $cardTokenId = $this->criarCardToken(
            $urlCardTokens,
            $accessToken,
            $cardNumber,
            (int) $cardExpMonth,
            (int) $anoExpiracao,
            $cardCvv,
            $cardHolderName,
            $cpfSemMascara,
            'Não foi possível processar o pagamento com cartão de crédito no momento.'
        );

        // 2) Identificar a bandeira pelo BIN, exigida no payment_method_id da cobrança
        $paymentMethodId = $this->identificarBandeira($cardNumber);

        // Endereço e telefone do sócio: a API aceita o pagamento sem esses dados,
        // mas o Mercado Pago recomenda enviá-los (payer.address, payer.phone e
        // additional_info) para dar mais contexto ao antifraude e reduzir
        // rejeições por cc_rejected_high_risk.
        $socio = $contribuicaoLog->getSocio();
        $telefoneSomenteDigitos = preg_replace('/\D/', '', (string) $socio->getTelefone());
        $ddd = substr($telefoneSomenteDigitos, 0, 2);
        $numeroTelefone = substr($telefoneSomenteDigitos, 2);

        // Só monta o objeto de telefone se o sócio de fato tiver um cadastrado —
        // {"area_code":"","number":""} é um valor malformado pro Mercado Pago e
        // pode derrubar uma cobrança que, sem o campo, seria aceita normalmente.
        $telefone = ($ddd !== '' && $numeroTelefone !== '')
            ? ['area_code' => $ddd, 'number' => $numeroTelefone]
            : null;

        $endereco = [
            'zip_code' => preg_replace('/\D/', '', (string) $socio->getCep()),
            'street_name' => $socio->getLogradouro(),
            'street_number' => (string) $socio->getNumeroEndereco()
        ];

        $payer = [
            'email' => $socio->getEmail(),
            'first_name' => $socio->getNome(),
            'last_name' => $socio->getSobrenome(),
            'identification' => [
                'type' => 'CPF',
                'number' => $cpfSemMascara
            ],
            'address' => $endereco
        ];

        $additionalInfoPayer = [
            'first_name' => $socio->getNome(),
            'last_name' => $socio->getSobrenome(),
            'address' => $endereco
        ];

        if ($telefone !== null) {
            $payer['phone'] = $telefone;
            $additionalInfoPayer['phone'] = $telefone;
        }

        // 3) Criar o pagamento propriamente dito
        $data = [
            'transaction_amount' => (float) $contribuicaoLog->getValor(),
            'token' => $cardTokenId,
            'description' => $contribuicaoLog->getAgradecimento() ?: 'Doação',
            'installments' => 1,
            'payment_method_id' => $paymentMethodId,
            'payer' => $payer,
            'additional_info' => [
                'items' => [[
                    'id' => $contribuicaoLog->getCodigo(),
                    'title' => $contribuicaoLog->getAgradecimento() ?: 'Doação',
                    'description' => 'Contribuição via cartão de crédito',
                    'category_id' => 'donations',
                    'quantity' => 1,
                    'unit_price' => (float) $contribuicaoLog->getValor()
                ]],
                'payer' => $additionalInfoPayer,
                'ip_address' => Util::getUserIp()
            ],
            'external_reference' => $contribuicaoLog->getCodigo(),
            'notification_url' => 'https://wegia.org'
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'X-Idempotency-Key: ' . $contribuicaoLog->getCodigo()
        ];

        // Device Fingerprint (gerado pelo security.js no front-end): sinaliza pro
        // antifraude do Mercado Pago informações do dispositivo do pagador. Sem
        // isso, cobranças legítimas ficam mais propensas a cair em cc_rejected_high_risk.
        $deviceId = filter_input(INPUT_POST, 'device_id');
        if (!empty($deviceId)) {
            $headers[] = 'X-meli-session-id: ' . $deviceId;
        }

        [$httpCode, $responseData, $curlError] = $this->post($endpoint, $data, $headers);

        if ($curlError) {
            throw new PaymentServiceException(
                'Não foi possível processar o pagamento com cartão de crédito no momento.',
                'Erro cURL ao processar cartão na API Mercado Pago: ' . $curlError,
                502
            );
        }

        if ($httpCode !== 200 && $httpCode !== 201) {
            $this->tratarErroApi($responseData, $httpCode);
        }

        $status = $responseData['status'] ?? null;

        // approved: aprovado | in_process/pending: em análise | qualquer outro: recusado
        if (!in_array($status, ['approved', 'authorized', 'in_process', 'pending'], true)) {
            $this->tratarPagamentoRecusado($responseData);
        }

        if (empty($responseData['id'])) {
            throw new PaymentServiceException(
                'Não foi possível processar o pagamento com cartão de crédito no momento.',
                'ID da transação não encontrado na resposta da API Mercado Pago.',
                502
            );
        }

        return (string) $responseData['id'];
    }

    /**
     * Identifica a bandeira do cartão a partir do BIN (primeiros dígitos do número),
     * necessária para o campo payment_method_id exigido pela API do Mercado Pago.
     */
    private function identificarBandeira($cardNumber)
    {
        $bin = substr($cardNumber, 0, 6);

        if (preg_match('/^4/', $bin)) {
            return 'visa';
        }
        if (preg_match('/^(5[1-5]|2(2[2-9]|[3-6]\d|7[01]|720))/', $bin)) {
            return 'master';
        }
        if (preg_match('/^3[47]/', $bin)) {
            return 'amex';
        }
        if (preg_match('/^(4011|4312|4389|4514|4573|6277|6362|6363|650[3-9]|6516|6550)/', $bin)) {
            return 'elo';
        }
        if (preg_match('/^(606282|3841)/', $bin)) {
            return 'hipercard';
        }

        throw new PaymentServiceException(
            'Não foi possível processar o pagamento com cartão de crédito no momento.',
            'Não foi possível identificar a bandeira do cartão informado.',
            400
        );
    }

    /**
     * Traduz o status_detail de um pagamento recusado em uma mensagem amigável
     */
    private function tratarPagamentoRecusado($responseData)
    {
        $statusDetail = $responseData['status_detail'] ?? '';

        $mensagens = [
            'cc_rejected_insufficient_amount'     => 'Saldo insuficiente no cartão.',
            'cc_rejected_bad_filled_card_number'  => 'Número do cartão inválido.',
            'cc_rejected_bad_filled_date'         => 'Data de validade inválida.',
            'cc_rejected_bad_filled_security_code' => 'Código de segurança (CVV) inválido.',
            'cc_rejected_bad_filled_other'        => 'Dados do cartão inválidos.',
            'cc_rejected_call_for_authorize'      => 'Pagamento não autorizado. Entre em contato com o banco emissor do cartão.',
            'cc_rejected_card_disabled'           => 'Cartão desabilitado. Entre em contato com o banco emissor.',
            'cc_rejected_duplicated_payment'      => 'Já existe um pagamento com os mesmos dados. Aguarde alguns instantes.',
            'cc_rejected_high_risk'               => 'O pagamento foi recusado por segurança.',
            'cc_rejected_max_attempts'            => 'Limite de tentativas de pagamento atingido.',
            'cc_rejected_card_type_not_allowed'   => 'Este tipo de cartão não é aceito.',
            'cc_rejected_invalid_installments'    => 'Número de parcelas inválido.',
        ];

        $mensagemCliente = $mensagens[$statusDetail] ?? 'O pagamento com cartão de crédito foi recusado.';

        throw new PaymentServiceException(
            $mensagemCliente,
            'Pagamento recusado pela API Mercado Pago. status: ' . ($responseData['status'] ?? '') . ' status_detail: ' . $statusDetail,
            400
        );
    }

    private function tratarErroApi($responseData, $httpCode)
    {
        $errorMsg = "Erro HTTP $httpCode";

        if (!empty($responseData['message'])) {
            $errorMsg .= ' - ' . $responseData['message'];
        }

        if (!empty($responseData['cause']) && is_array($responseData['cause'])) {
            foreach ($responseData['cause'] as $cause) {
                $errorMsg .= "\n" . (is_array($cause) ? ($cause['description'] ?? json_encode($cause)) : $cause);
            }
        }

        throw new PaymentServiceException(
            'Não foi possível processar o pagamento com cartão de crédito no momento.',
            $errorMsg,
            502
        );
    }
}