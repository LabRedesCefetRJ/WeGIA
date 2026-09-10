<?php
require_once 'ApiRecorrenciaServiceInterface.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once '../dao/GatewayPagamentoDAO.php';
require_once 'MercadoPagoCardTokenTrait.php';

/**
 * Cria uma assinatura recorrente via API do Mercado Pago, usando o produto de
 * Assinaturas (Preapproval). Cada cobrança futura gerada por essa assinatura é
 * um "authorized_payment", que MercadoPagoContribuicoesService::getInvoices()
 * já sabe consultar.
 *
 * O card_token é gerado no FRONT-END (SDK MercadoPago.js v2, ver
 * recorrencia.js) e chega pronto via POST — obrigatório, ver validação
 * abaixo (mesmo racional de MercadoPagoCartaoCreditoService.php).
 *
 * Fluxo (todas as chamadas no mesmo host do endPoint cadastrado para o
 * gateway, igual ao restante do sistema — nada de URL fixa no código):
 *  1) Cria um preapproval_plan com o valor e a frequência da contribuição
 *     (POST /preapproval_plan) — nessa conta, autorizar a assinatura direto
 *     com card_token_id (sem redirecionar o pagador pro checkout do Mercado
 *     Pago) só funciona vinculado a um plano. Etapa interna;
 *  2) Cria o preapproval propriamente dito (POST {endPoint cadastrado no
 *     gateway}), vinculado ao plano, autorizado imediatamente com o
 *     card_token_id. Essa é a operação principal.
 */
class MercadoPagoRecorrenciaService implements ApiRecorrenciaServiceInterface
{
    use MercadoPagoCardTokenTrait;

    public function criarAssinatura(Recorrencia $recorrencia)
    {
        $gatewayPagamentoDao = new GatewayPagamentoDAO();
        $gatewayPagamento = $gatewayPagamentoDao->buscarPorId($recorrencia->getGatewayPagamento()->getId());

        if (!$gatewayPagamento) {
            throw new PaymentServiceException(
                'Não foi possível criar a assinatura no momento. Tente novamente mais tarde.',
                'Gateway de pagamento Mercado Pago não encontrado ou inativo.',
                502
            );
        }

        $accessToken = $gatewayPagamento['private_token'];
        $endpoint = $gatewayPagamento['endPoint']; // ex: https://api.mercadopago.com/preapproval

        // Mesmo host do endPoint cadastrado para o gateway, igual ao padrão usado em
        // MercadoPagoContribuicoesService.php — não fica fixo em api.mercadopago.com.
        $host = parse_url($endpoint, PHP_URL_HOST) ?: 'api.mercadopago.com';
        $urlPreapprovalPlan = 'https://' . $host . '/preapproval_plan';

        $socio = $recorrencia->getSocio();

        // Device Fingerprint (security.js no front-end), mesmo racional do
        // MercadoPagoCartaoCreditoService.php: ajuda o antifraude a não tratar
        // a autorização da assinatura como suspeita por falta de contexto.
        $deviceId = filter_input(INPUT_POST, 'device_id');

        // Token do cartão, gerado no navegador do pagador pelo SDK MercadoPago.js
        // v2 (ver recorrencia.js). Sem ele, a Public Key do gateway não está
        // configurada — recusa explicitamente em vez de tokenizar pelo servidor.
        $cardTokenId = filter_input(INPUT_POST, 'card_token');

        if (empty($cardTokenId)) {
            throw new PaymentServiceException(
                'Não foi possível criar a assinatura no momento. Tente novamente mais tarde.',
                'Token do cartão (tokenização no navegador) não informado. Verifique se a Public Key do Mercado Pago está configurada no Gateway de Pagamento.',
                400
            );
        }

        // 1) Criar o plano com o valor e a frequência dessa contribuição
        $planId = $this->criarPlano($urlPreapprovalPlan, $accessToken, $recorrencia, $deviceId);

        // 2) Autorizar a assinatura vinculada ao plano, direto com o card_token_id
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'X-Idempotency-Key: ' . Util::gerarNumeroDocumento(16) . '-' . $recorrencia->getCodigo()
        ];

        if (!empty($deviceId)) {
            $headers[] = 'X-meli-session-id: ' . $deviceId;
        }

        $data = [
            'preapproval_plan_id' => $planId,
            'reason' => $recorrencia->getCodigo(),
            'external_reference' => $recorrencia->getCodigo(),
            'payer_email' => $socio->getEmail(),
            'card_token_id' => $cardTokenId,
            'status' => 'authorized'
        ];

        // O plano criado no passo anterior às vezes demora um instante pra propagar
        // nos servidores do Mercado Pago; nesse intervalo, tentar autorizar a
        // assinatura com ele retorna 404 "The template with id ... does not exist".
        // Tenta de novo algumas vezes antes de desistir.
        $tentativasRestantes = 3;
        $esperaSegundos = 1;

        do {
            [$httpCode, $responseData, $curlError] = $this->post($endpoint, $data, $headers);

            if ($curlError) {
                throw new PaymentServiceException(
                    'Não foi possível criar a assinatura no momento. Tente novamente mais tarde.',
                    'Erro cURL ao criar preapproval na API Mercado Pago: ' . $curlError,
                    502
                );
            }

            $planoAindaNaoPropagou = $httpCode === 404
                && stripos((string) ($responseData['message'] ?? ''), 'does not exist') !== false;

            $tentativasRestantes--;

            if ($planoAindaNaoPropagou && $tentativasRestantes > 0) {
                sleep($esperaSegundos);
                $esperaSegundos *= 2;
            }
        } while ($planoAindaNaoPropagou && $tentativasRestantes > 0);

        if ($httpCode !== 200 && $httpCode !== 201) {
            $this->tratarErroApi($responseData, $httpCode);
        }

        if (empty($responseData['id'])) {
            throw new PaymentServiceException(
                'Não foi possível criar a assinatura no momento. Tente novamente mais tarde.',
                'ID da assinatura (preapproval) não encontrado na resposta da API Mercado Pago.',
                502
            );
        }

        // Pedimos status "authorized" na criação (autorização imediata com
        // card_token_id), mas o Mercado Pago pode devolver "pending" quando não
        // consegue autorizar de imediato — isso NÃO é uma assinatura ativa ainda.
        $status = $responseData['status'] ?? null;

        return [
            'transacao_id' => (string) $responseData['id'],
            'status' => $status === 'authorized' ? 'aprovado' : 'em_analise'
        ];
    }

    /**
     * Cria um preapproval_plan (POST /preapproval_plan) com o valor e a frequência
     * mensal dessa contribuição — pré-requisito, nessa conta, para autorizar a
     * assinatura direto com card_token_id sem redirecionar o pagador.
     */
    private function criarPlano($urlPreapprovalPlan, $accessToken, Recorrencia $recorrencia, ?string $deviceId)
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'X-Idempotency-Key: ' . Util::gerarNumeroDocumento(16) . '-plano-' . $recorrencia->getCodigo()
        ];

        if (!empty($deviceId)) {
            $headers[] = 'X-meli-session-id: ' . $deviceId;
        }

        $data = [
            'reason' => $recorrencia->getCodigo(),
            'auto_recurring' => [
                'frequency' => 1,
                'frequency_type' => 'months',
                'transaction_amount' => (float) $recorrencia->getValor(),
                'currency_id' => 'BRL'
            ],
            'back_url' => 'https://wegia.org'
        ];

        [$httpCode, $responseData, $curlError] = $this->post($urlPreapprovalPlan, $data, $headers);

        if ($curlError) {
            throw new PaymentServiceException(
                'Não foi possível criar a assinatura no momento. Tente novamente mais tarde.',
                'Erro cURL ao criar o plano de assinatura na API Mercado Pago: ' . $curlError,
                502
            );
        }

        if (($httpCode !== 200 && $httpCode !== 201) || empty($responseData['id'])) {
            $this->tratarErroApi($responseData, $httpCode);
        }

        return $responseData['id'];
    }

    private function tratarErroApi($responseData, $httpCode)
    {
        $errorMsg = "Erro HTTP $httpCode";

        if (!empty($responseData['message'])) {
            $errorMsg .= ' - ' . $responseData['message'];
        }

        if (!empty($responseData['cause'])) {
            $errorMsg .= ' | cause: ' . json_encode($responseData['cause']);
        }

        throw new PaymentServiceException(
            'Não foi possível criar a assinatura no momento. Tente novamente mais tarde.',
            $errorMsg,
            $httpCode >= 400 && $httpCode < 600 ? $httpCode : 502
        );
    }
}
