<?php

/**
 * POST em JSON compartilhado entre MercadoPagoCartaoCreditoService e
 * MercadoPagoRecorrenciaService.
 *
 * A tokenização do cartão (POST /v1/card_tokens) é obrigatória no FRONT-END
 * (SDK MercadoPago.js v2, ver cartao_credito.js/recorrencia.js) — o backend só
 * recebe o card_token já pronto via POST. Isso é exigido porque a chamada de
 * tokenização precisa vir do navegador do pagador: se o servidor tokenizasse
 * o cartão, a chamada chegaria à API do Mercado Pago com o IP do servidor,
 * descasado do device fingerprint do security.js, aumentando a recusa por
 * antifraude (cc_rejected_high_risk).
 */
trait MercadoPagoCardTokenTrait
{
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
