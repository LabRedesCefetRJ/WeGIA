<?php

/**
 * POST em JSON e tokenização de cartão (POST /v1/card_tokens) compartilhados entre
 * MercadoPagoCartaoCreditoService e MercadoPagoRecorrenciaService — ambos precisam
 * do mesmo token de cartão antes de criar o pagamento/assinatura na API do Mercado Pago.
 */
trait MercadoPagoCardTokenTrait
{
    /**
     * Gera um token de cartão (POST /v1/card_tokens), pré-requisito para operações que
     * não podem trafegar o número do cartão em texto puro na API do Mercado Pago.
     */
    private function criarCardToken($urlCardTokens, $accessToken, $cardNumber, $expMonth, $expYear, $cvv, $holderName, $cpf, string $mensagemErroCliente)
    {
        $data = [
            'card_number' => $cardNumber,
            'expiration_month' => $expMonth,
            'expiration_year' => $expYear,
            'security_code' => $cvv,
            'cardholder' => [
                'name' => $holderName,
                'identification' => [
                    'type' => 'CPF',
                    'number' => $cpf
                ]
            ]
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ];

        [$httpCode, $responseData, $curlError] = $this->post($urlCardTokens, $data, $headers);

        if ($curlError) {
            throw new PaymentServiceException(
                $mensagemErroCliente,
                'Erro cURL ao gerar o token do cartão na API Mercado Pago: ' . $curlError,
                502
            );
        }

        if (($httpCode !== 200 && $httpCode !== 201) || empty($responseData['id'])) {
            throw new PaymentServiceException(
                $mensagemErroCliente,
                'Falha ao gerar o token do cartão. Verifique os dados informados. HTTP ' . $httpCode . ' - ' . json_encode($responseData),
                400
            );
        }

        return $responseData['id'];
    }

    /**
     * Executa um POST em JSON e devolve [httpCode, responseDataAssoc, curlErrorOuNull]
     */
    private function post($url, array $data, array $headers)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $curlError = curl_errno($ch) ? curl_error($ch) : null;
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responseData = json_decode((string) $response, true);

        return [$httpCode, is_array($responseData) ? $responseData : [], $curlError];
    }
}
