<?php
require_once 'ApiCartaoCreditoServiceInterface.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once '../model/ContribuicaoLog.php';
require_once '../dao/GatewayPagamentoDAO.php';
require_once 'MercadoPagoCardTokenTrait.php';

/**
 * Implementação do processamento de cartão de crédito via API do Mercado Pago.
 *
 * O card_token é gerado no FRONT-END (SDK MercadoPago.js v2, ver
 * cartao_credito.js) — como recomendado pelo próprio Mercado Pago — e chega
 * pronto via POST. O backend só cria o pagamento (POST /v1/payments, no
 * endPoint cadastrado para o gateway). O número do cartão e o CVV nunca
 * trafegam pelo nosso servidor.
 *
 * A Public Key do gateway é OBRIGATÓRIA para esse fluxo: sem ela, o
 * front-end não consegue tokenizar, e o pagamento é recusado explicitamente
 * (ver validação abaixo) em vez de cair num fallback de tokenização pelo
 * servidor — que faria a chamada à API do Mercado Pago chegar com o IP do
 * servidor, descasado do device fingerprint do security.js, aumentando a
 * recusa por antifraude (cc_rejected_high_risk).
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

        $accessToken = $gatewayPagamento['private_token'];
        $endpoint = $gatewayPagamento['endPoint']; // ex: https://api.mercadopago.com/v1/payments

        // Token do cartão e BIN (6 primeiros dígitos), gerados no navegador do
        // pagador pelo SDK MercadoPago.js v2 (ver cartao_credito.js). Sem eles, a
        // Public Key do gateway não está configurada — recusa explicitamente em
        // vez de tokenizar pelo servidor.
        $cardTokenId = filter_input(INPUT_POST, 'card_token');
        $cardBin = filter_input(INPUT_POST, 'card_bin');

        if (empty($cardTokenId) || empty($cardBin)) {
            throw new PaymentServiceException(
                'Não foi possível processar o pagamento com cartão de crédito no momento.',
                'Token do cartão (tokenização no navegador) não informado. Verifique se a Public Key do Mercado Pago está configurada no Gateway de Pagamento.',
                400
            );
        }

        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());

        // Identificar a bandeira pelo BIN, exigida no payment_method_id da cobrança
        $paymentMethodId = $this->identificarBandeira($cardBin);

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

        // Criar o pagamento propriamente dito
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

        // approved/authorized: aprovado de fato | in_process/pending: o Mercado
        // Pago ainda está analisando (pode virar aprovado OU recusado depois —
        // NÃO é sinônimo de aprovado, mesmo que pareça um "sucesso" síncrono) |
        // qualquer outro: recusado.
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

        return [
            'transacao_id' => (string) $responseData['id'],
            'status' => in_array($status, ['approved', 'authorized'], true) ? 'aprovado' : 'em_analise'
        ];
    }

    /**
     * Identifica a bandeira do cartão a partir do BIN (6 primeiros dígitos do
     * número), necessária para o campo payment_method_id exigido pela API do
     * Mercado Pago.
     */
    private function identificarBandeira($cardBin)
    {
        $bin = substr($cardBin, 0, 6);

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