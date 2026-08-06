<?php
require_once '../model/ContribuicaoLogCollection.php';
require_once '../model/ContribuicaoLog.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once 'ApiCarneServiceInterface.php';
require_once '../dao/GatewayPagamentoDAO.php';
require_once '../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

/**
 * Gera um carnê (série de boletos "bolbradesco", um por parcela) via API do
 * Mercado Pago, baixa o PDF de cada boleto e mescla tudo em um único arquivo,
 * seguindo o mesmo fluxo já usado em PagarMeCarneService.php.
 */
class MercadoPagoCarneService implements ApiCarneServiceInterface
{
    public function gerarCarne(ContribuicaoLogCollection $contribuicaoLogCollection)
    {
        $primeiraContribuicao = $contribuicaoLogCollection->getIterator()->current(); // Ignorar erro do VSCode/Intelephense em ->current()
        $cpfSemMascara = Util::limpaCpf($primeiraContribuicao->getSocio()->getDocumento());

        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamento = $gatewayPagamentoDao->buscarPorId($primeiraContribuicao->getGatewayPagamento()->getId());

            if (!$gatewayPagamento) {
                throw new PaymentServiceException(
                    'Não foi possível gerar o carnê no momento. Tente novamente mais tarde.',
                    'Gateway de pagamento Mercado Pago não encontrado ou inativo.',
                    502
                );
            }

            $accessToken = $gatewayPagamento['token'];
            $endpoint = $gatewayPagamento['endPoint']; // ex: https://api.mercadopago.com/v1/payments

            $pdfLinks = [];
            $codigosAPI = [];

            foreach ($contribuicaoLogCollection as $contribuicaoLog) {
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
                    // Cada parcela precisa de uma chave de idempotência própria, senão o
                    // Mercado Pago pode devolver o mesmo boleto já criado anteriormente.
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
                        'Não foi possível gerar o carnê no momento. Tente novamente mais tarde.',
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
                        'Não foi possível gerar o carnê no momento. Tente novamente mais tarde.',
                        "A API Mercado Pago retornou o código de status HTTP $httpCode - $mensagem | cause: $causa | resposta completa: " . json_encode($responseData),
                        $httpCode
                    );
                }

                if (empty($responseData['transaction_details']['external_resource_url']) || empty($responseData['id'])) {
                    throw new PaymentServiceException(
                        'Não foi possível gerar o carnê no momento. Tente novamente mais tarde.',
                        'A resposta da API Mercado Pago não contém o link do boleto ou o id da transação.',
                        502
                    );
                }

                $pdfLinks[] = $responseData['transaction_details']['external_resource_url'];
                $codigosAPI[] = $responseData['id'];
            }

            // Atribui os códigos retornados pela API às respectivas contribuições da coleção
            foreach ($contribuicaoLogCollection as $index => $contribuicaoLog) {
                $contribuicaoLog->setCodigo($codigosAPI[$index]);
            }

            $arquivos = $this->salvarTemp($pdfLinks);

            $logArray = $contribuicaoLogCollection->getIterator()->getArrayCopy();
            $ultimaParcela = end($logArray);
            $caminho = $this->guardarSegundaVia($arquivos, $cpfSemMascara, $ultimaParcela);
            $this->removerTemp();

            if (!$caminho || empty($caminho)) {
                return false;
            }

            return ['link' => $caminho, 'contribuicoes' => $contribuicaoLogCollection];
        } catch (Throwable $e) {
            if ($e instanceof PaymentServiceException) {
                throw $e;
            }

            throw new PaymentServiceException(
                'Não foi possível gerar o carnê no momento. Tente novamente mais tarde.',
                'Falha inesperada ao gerar carnê na API Mercado Pago: ' . $e->getMessage(),
                502,
                $e
            );
        }
    }

    /**
     * Baixa os PDFs de cada boleto para um diretório temporário
     */
    public function salvarTemp($pdf_links)
    {
        $saveDir = '../pdfs/';
        $saveDirTemp = $saveDir . 'temp/';

        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        if (!is_dir($saveDirTemp)) {
            mkdir($saveDirTemp, 0755, true);
        }

        $arquivos = [];

        foreach ($pdf_links as $indice => $url) {
            $pathParts = explode('/', $url);
            $fileName = $indice . '_' . $pathParts[count($pathParts) - 2] . '.pdf';
            $savePath = $saveDirTemp . $fileName;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, true);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new PaymentServiceException(
                    'Não foi possível concluir a geração do carnê no momento.',
                    'Erro ao baixar o PDF do boleto: ' . curl_error($ch),
                    502
                );
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode == 200) {
                $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $headers = substr($response, 0, $headerSize);
                $fileContent = substr($response, $headerSize);

                if (stripos($headers, 'Content-Type: application/pdf') !== false) {
                    file_put_contents($savePath, $fileContent);
                    $arquivos[] = $savePath;
                } else {
                    curl_close($ch);
                    throw new PaymentServiceException(
                        'Não foi possível concluir a geração do carnê no momento.',
                        'Erro: o conteúdo da URL não é um PDF.',
                        400
                    );
                }
            } else {
                curl_close($ch);
                throw new PaymentServiceException(
                    'Não foi possível concluir a geração do carnê no momento.',
                    "Erro ao baixar o PDF do boleto: HTTP $httpCode",
                    $httpCode
                );
            }

            curl_close($ch);
        }

        return $arquivos;
    }

    public function removerTemp()
    {
        $dir = '../pdfs/temp';

        if (!file_exists($dir) || !is_dir($dir)) {
            return false;
        }

        $dirHandle = opendir($dir);

        while (($file = readdir($dirHandle)) !== false) {
            if ($file != '.' && $file != '..') {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file;

                if (is_dir($filePath)) {
                    removeDirectory($filePath);
                } else {
                    unlink($filePath);
                }
            }
        }

        closedir($dirHandle);

        return rmdir($dir);
    }

    public function guardarSegundaVia($arquivos, $cpfSemMascara, $ultimaParcela)
    {
        $pdf = new Fpdi();

        foreach ($arquivos as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        }

        $numeroAleatorio = str_replace('_', '-', $ultimaParcela->getCodigo());
        $ultimaDataVencimento = str_replace('-', '', $ultimaParcela->getDataVencimento());

        $nomeArquivo = '../pdfs/' . $numeroAleatorio . '_' . $cpfSemMascara . '_' . $ultimaDataVencimento . '_' . $ultimaParcela->getValor() . '.pdf';
        $pdf->Output('F', $nomeArquivo);

        return 'pdfs/' . $numeroAleatorio . '_' . $cpfSemMascara . '_' . $ultimaDataVencimento . '_' . $ultimaParcela->getValor() . '.pdf';
    }
}