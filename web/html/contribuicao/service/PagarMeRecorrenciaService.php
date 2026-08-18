<?php
require_once 'ApiRecorrenciaServiceInterface.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once '../dao/ContribuicaoLogDAO.php';
require_once '../dao/GatewayPagamentoDAO.php';

class PagarMeRecorrenciaService implements ApiRecorrenciaServiceInterface {
    public function criarAssinatura(Recorrencia $recorrencia) {
        $contribuicaoLogDao = new ContribuicaoLogDAO();
        $agradecimento = $contribuicaoLogDao->getAgradecimento();
        
        $gatewayPagamentoDao = new GatewayPagamentoDAO();
        $gatewayPagamento = $gatewayPagamentoDao->buscarPorId($recorrencia->getGatewayPagamento()->getId());

        $headers = [
            'Authorization: Basic ' . base64_encode($gatewayPagamento['token'] . ':'),
            'Content-Type: application/json;charset=UTF-8'
        ];

        //Dados do cartão
        $cardId = filter_input(INPUT_POST, 'card_id', FILTER_SANITIZE_SPECIAL_CHARS);
        
        $code = $recorrencia->getCodigo();
        $cpfSemMascara = Util::limpaCpf($recorrencia->getSocio()->getDocumento());
        $telefone = Util::limpaTelefone($recorrencia->getSocio()->getTelefone());

        // Estrutura de dados para criação de assinatura Pagar.me
        $data = [
            'code' => $code,
            'payment_method' => 'credit_card',
            'currency' => 'BRL',
            'interval' => 'month',
            'interval_count' => 1,
            'billing_type' => 'prepaid',
            'installments' => 1,
            'statement_descriptor' => substr($agradecimento, 0, 13),
            'customer' => [
                'name' => $recorrencia->getSocio()->getFullName(),
                'email' => $recorrencia->getSocio()->getEmail(),
                'type' => 'individual',
                'document_type' => 'CPF',
                'document' => $cpfSemMascara,
                'phones' => [
                    'mobile_phone' => [
                        'country_code' => '55',
                        'area_code' => substr($telefone, 0, 2),
                        'number' => substr($telefone, 2)
                    ]
                ]
            ],
            'card_id' => $cardId,
            'items' => [
                [
                    'description' => $agradecimento,
                    'quantity' => 1,
                    'pricing_scheme' => [
                        'scheme_type' => 'unit',
                        'price' => intval($recorrencia->getValor() * 100) // Converter para centavos
                    ]
                ]
            ]
        ];

        $jsonData = json_encode($data);

        // Fazer requisição para o endpoint de assinaturas (subscriptions)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $gatewayPagamento['endPoint']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            error_log("Erro de conexão: " . curl_error($ch));
            throw new PaymentServiceException(
                'Não foi possível criar a assinatura no momento.',
                'Erro cURL ao criar assinatura na API Pagar.me: ' . curl_error($ch),
                502
            );
        }
        curl_close($ch);

        $responseData = json_decode($response, true);

        if ($httpCode === 200 || $httpCode === 201) {
            if (empty($responseData['id'])) {
                throw new PaymentServiceException(
                    'Não foi possível criar a assinatura no momento.',
                    'ID da assinatura não retornado pela API Pagar.me.',
                    502
                );
            }
            return (string)$responseData['id'];
        } else {
            $this->tratarErroApi($responseData, $httpCode);
        }
    }
    /**
     * Tratar erros da API
     */
    private function tratarErroApi($responseData, $httpCode) {
        $errorMsg = "Erro HTTP $httpCode";
        
        if (!empty($responseData['errors'])) {
            foreach ($responseData['errors'] as $error) {
                if (isset($error['code'])) {
                    switch ($error['code']) {
                        case 'invalid_card':
                            throw new PaymentServiceException('Não foi possível criar a assinatura no momento.', 'Cartão inválido. Verifique os dados e tente novamente.', 400);
                        case 'card_declined':
                            throw new PaymentServiceException('Não foi possível criar a assinatura no momento.', 'Cartão recusado. Entre em contato com seu banco.', 400);
                        case 'insufficient_funds':
                            throw new PaymentServiceException('Não foi possível criar a assinatura no momento.', 'Saldo insuficiente no cartão.', 400);
                        case 'expired_card':
                            throw new PaymentServiceException('Não foi possível criar a assinatura no momento.', 'Cartão expirado.', 400);
                        default:
                            $errorMsg .= " - " . ($error['message'] ?? 'Erro desconhecido');
                    }
                }
            }
        }
        switch ($httpCode) {
            case 400:
                $errorMsg .= " - Requisição inválida";
                break;
            case 401:
                $errorMsg .= " - Chave de API inválida";
                break;
            case 403:
                $errorMsg .= " - Acesso bloqueado por IP/Domínio";
                break;
            case 404:
                $errorMsg .= " - Recurso não encontrado";
                break;
            case 412:
                $errorMsg .= " - Parâmetros válidos mas requisição falhou";
                break;
            case 422:
                $errorMsg .= " - Parâmetros inválidos";
                // Erros PSP (integração)
                if (!empty($responseData['gateway_response']['errors'])) {
                    foreach ($responseData['gateway_response']['errors'] as $error) {
                        $errorMsg .= "-" . (is_array($error) ? ($error['message'] ?? '') : $error);
                    }
                }
                // Erros de validação de campos
                if (!empty($responseData['errors'])) {
                    foreach ($responseData['errors'] as $field => $messages) {
                        $errorMsg .= "\n$field: " . implode(', ', (array)$messages);
                    }
                }
                break;
            case 429:
                $errorMsg .= " - Muitas requisições. Tente novamente mais tarde.";
                break;
            case 500:
                $errorMsg .= " - Erro interno do servidor Pagar.me";
                break;
        }
        
        if (!empty($responseData['message'])) {
            $errorMsg .= " - " . $responseData['message'];
        }
        
        throw new PaymentServiceException(
            'Não foi possível criar a assinatura no momento.',
            $errorMsg,
            502
        );
    }
}