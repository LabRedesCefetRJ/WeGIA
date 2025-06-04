<?php
require_once 'ApiRecorrenciaServiceInterface.php';
require_once '../helper/Util.php';
require_once '../model/ContribuicaoLog.php';
require_once '../dao/GatewayPagamentoDAO.php';

class PagarMeRecorrenciaService implements ApiRecorrenciaServiceInterface {
    public function criarAssinatura(ContribuicaoLog $contribuicaoLog) {
        error_log("Iniciando criação de assinatura para: " . $contribuicaoLog->getSocio()->getDocumento());
        
        $gatewayPagamentoDao = new GatewayPagamentoDAO();
        $gatewayPagamento = $gatewayPagamentoDao->buscarPorId($contribuicaoLog->getGatewayPagamento()->getId());

        $headers = [
            'Authorization: Basic ' . base64_encode($gatewayPagamento['token'] . ':'),
            'Content-Type: application/json;charset=UTF-8'
        ];

        //Dados do cartão
        $cardNumber = preg_replace('/\D/', '', filter_input(INPUT_POST, 'card_number'));
        $cardExpMonth = filter_input(INPUT_POST, 'card_exp_month');
        $cardExpYear = filter_input(INPUT_POST, 'card_exp_year');
        $cardHolderName = filter_input(INPUT_POST, 'card_holder_name');
        $cardCvv = filter_input(INPUT_POST, 'card_cvv');
        
        $code = $contribuicaoLog->getCodigo();
        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());
        $telefone = Util::limpaTelefone($contribuicaoLog->getSocio()->getTelefone());

        // Estrutura de dados para criação de assinatura Pagar.me
        $data = [
            'code' => $code,
            'payment_method' => 'credit_card',
            'currency' => 'BRL',
            'interval' => 'month',
            'interval_count' => 1,
            'billing_type' => 'prepaid',
            'installments' => 1,
            'statement_descriptor' => substr($contribuicaoLog->getAgradecimento(), 0, 13),
            'customer' => [
                'name' => $contribuicaoLog->getSocio()->getNome(),
                'email' => $contribuicaoLog->getSocio()->getEmail(),
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
            'card' => [
                'number' => $cardNumber,
                'holder_name' => $cardHolderName,
                'exp_month' => (int)$cardExpMonth,
                'exp_year' => (int)$cardExpYear,
                'cvv' => $cardCvv,
                'billing_address' => [
                    'line_1' => $contribuicaoLog->getSocio()->getLogradouro() . ", " . $contribuicaoLog->getSocio()->getNumeroEndereco(),
                    'zip_code' => preg_replace('/\D/', '', $contribuicaoLog->getSocio()->getCep()),
                    'city' => $contribuicaoLog->getSocio()->getCidade(),
                    'state' => $contribuicaoLog->getSocio()->getEstado(),
                    'country' => 'BR'
                ]
            ],
            'items' => [
                [
                    'description' => $contribuicaoLog->getAgradecimento(),
                    'quantity' => 1,
                    'pricing_scheme' => [
                        'scheme_type' => 'unit',
                        'price' => intval($contribuicaoLog->getValor() * 100) // Converter para centavos
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
            throw new Exception("Erro de conexão: " . curl_error($ch));
        }
        curl_close($ch);

        $responseData = json_decode($response, true);
        error_log("Resposta Pagar.me: " . print_r($responseData, true));

        if ($httpCode === 200 || $httpCode === 201) {
            if (empty($responseData['id'])) {
                throw new Exception("ID da assinatura não retornado pela API");
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
                            throw new Exception("Cartão inválido. Verifique os dados e tente novamente.");
                        case 'card_declined':
                            throw new Exception("Cartão recusado. Entre em contato com seu banco.");
                        case 'insufficient_funds':
                            throw new Exception("Saldo insuficiente no cartão.");
                        case 'expired_card':
                            throw new Exception("Cartão expirado.");
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
        
        throw new Exception($errorMsg);
    }
}