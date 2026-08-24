<?php
require_once 'ApiBoletoServiceInterface.php';
require_once '../model/ContribuicaoLog.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once '../dao/GatewayPagamentoDAO.php';

/**
 * Gera um boleto "bolbradesco" via API do Mercado Pago, no mesmo padrão do
 * PagarMeBoletoService.php.
 *
 * O Mercado Pago limita o campo date_of_expiration do bolbradesco a no máximo
 * 29 dias a partir da criação (erro "The expiration date can not be greater
 * than 29 days") — testado tanto na API de pagamentos clássica quanto na
 * Orders API, sem diferença. Por isso o boleto precisa ser gerado avulso,
 * perto do vencimento de cada contribuição, e não antecipado em lote (carnê).
 */
class MercadoPagoBoletoService implements ApiBoletoServiceInterface
{
    public function gerarBoleto(ContribuicaoLog $contribuicaoLog)
    {
        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());

        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamento = $gatewayPagamentoDao->buscarPorId($contribuicaoLog->getGatewayPagamento()->getId());

            if (!$gatewayPagamento) {
                throw new PaymentServiceException(
                    'Não foi possível gerar o boleto no momento. Tente novamente mais tarde.',
                    'Gateway de pagamento Mercado Pago não encontrado ou inativo.',
                    502
                );
            }

            $accessToken = $gatewayPagamento['private_token'];
            $endpoint = $gatewayPagamento['endPoint']; // ex: https://api.mercadopago.com/v1/payments

            $dataVencimento = $contribuicaoLog->getDataVencimento() . 'T12:59:59.000-04:00';

            $data = [
                'description' => $contribuicaoLog->getAgradecimento() ?: 'Doação',
                'transaction_amount' => (float) $contribuicaoLog->getValor(),
                'payment_method_id' => 'bolbradesco',
                'date_of_expiration' => $dataVencimento,
                'payer' => [
                    'first_name' => $contribuicaoLog->getSocio()->getNome(),
                    'last_name' => $contribuicaoLog->getSocio()->getSobrenome(),
                    'email' => $contribuicaoLog->getSocio()->getEmail(),
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $cpfSemMascara
                    ],
                    'address' => [
                        // A API do Mercado Pago exige o CEP apenas com dígitos (ex: "88000000"),
                        // sem traço. O dado salvo no cadastro do sócio vem formatado (ex: "28625-230").
                        'zip_code' => preg_replace('/\D/', '', (string) $contribuicaoLog->getSocio()->getCep()),
                        'city' => $contribuicaoLog->getSocio()->getCidade(),
                        'street_name' => $contribuicaoLog->getSocio()->getLogradouro(),
                        'street_number' => (string) $contribuicaoLog->getSocio()->getNumeroEndereco(),
                        'neighborhood' => $contribuicaoLog->getSocio()->getBairro(),
                        'federal_unit' => $contribuicaoLog->getSocio()->getEstado()
                    ]
                ],
                'notification_url' => 'https://wegia.org',
                'external_reference' => $contribuicaoLog->getCodigo()
            ];

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
                'X-Idempotency-Key: ' . Util::gerarNumeroDocumento(16) . '-' . $contribuicaoLog->getCodigo()
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $erro = curl_error($ch);
                curl_close($ch);
                throw new PaymentServiceException(
                    'Não foi possível gerar o boleto no momento. Tente novamente mais tarde.',
                    'Erro cURL ao gerar boleto na API Mercado Pago: ' . $erro,
                    502
                );
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode((string) $response, true);
            $responseData = is_array($responseData) ? $responseData : [];

            if ($httpCode !== 200 && $httpCode !== 201) {
                $mensagem = $responseData['message'] ?? '';
                // "cause" costuma trazer o código/descrição específica do motivo da rejeição
                $causa = !empty($responseData['cause']) ? json_encode($responseData['cause']) : '(sem detalhe de causa)';
                throw new PaymentServiceException(
                    'Não foi possível gerar o boleto no momento. Tente novamente mais tarde.',
                    "A API Mercado Pago retornou o código de status HTTP $httpCode - $mensagem | cause: $causa | resposta completa: " . json_encode($responseData),
                    $httpCode
                );
            }

            if (empty($responseData['transaction_details']['external_resource_url']) || empty($responseData['id'])) {
                throw new PaymentServiceException(
                    'Não foi possível gerar o boleto no momento. Tente novamente mais tarde.',
                    'A resposta da API Mercado Pago não contém o link do boleto ou o id da transação.',
                    502
                );
            }

            $pdfLink = $responseData['transaction_details']['external_resource_url'];
            $contribuicaoLog->setCodigo($responseData['id']);

            $this->guardarSegundaVia($pdfLink, $contribuicaoLog);

            // O controller (criarBoleto) não trata o retorno do link, então a service
            // responde direto ao front-end aqui, no mesmo padrão do PagarMeBoletoService.
            echo json_encode(['link' => $pdfLink]);

            return $responseData['id'];
        } catch (Throwable $e) {
            if ($e instanceof PaymentServiceException) {
                throw $e;
            }

            throw new PaymentServiceException(
                'Não foi possível gerar o boleto no momento. Tente novamente mais tarde.',
                'Falha inesperada ao gerar boleto na API Mercado Pago: ' . $e->getMessage(),
                502,
                $e
            );
        }
    }

    public function guardarSegundaVia($pdf_link, ContribuicaoLog $contribuicaoLog)
    {
        $saveDir = '../pdfs/';

        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());
        $dataVencimento = str_replace('-', '', $contribuicaoLog->getDataVencimento());
        $codigo = str_replace('_', '-', $contribuicaoLog->getCodigo());
        $nomeArquivo = $saveDir . $codigo . '_' . $cpfSemMascara . '_' . $dataVencimento . '_' . $contribuicaoLog->getValor() . '.pdf';

        $ch = curl_init($pdf_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $erro = curl_error($ch);
            curl_close($ch);
            throw new PaymentServiceException(
                'Não foi possível concluir a emissão do boleto no momento.',
                'Erro ao baixar o PDF do boleto: ' . $erro,
                502
            );
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200) {
            curl_close($ch);
            throw new PaymentServiceException(
                'Não foi possível concluir a emissão do boleto no momento.',
                "Erro ao baixar o PDF do boleto: HTTP $httpCode",
                $httpCode
            );
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $fileContent = substr($response, $headerSize);
        curl_close($ch);

        if (stripos($headers, 'Content-Type: application/pdf') === false) {
            throw new PaymentServiceException(
                'Não foi possível concluir a emissão do boleto no momento.',
                'Erro: o conteúdo da URL não é um PDF.',
                400
            );
        }

        file_put_contents($nomeArquivo, $fileContent);
    }
}
