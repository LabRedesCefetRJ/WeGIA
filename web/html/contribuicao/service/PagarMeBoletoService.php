<?php
require_once 'ApiBoletoServiceInterface.php';
require_once 'PdfDownloadService.php';
require_once '../model/ContribuicaoLog.php';
require_once '../dao/GatewayPagamentoDAO.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
class PagarMeBoletoService implements ApiBoletoServiceInterface
{
    public function gerarBoleto(ContribuicaoLog $contribuicaoLog)
    {
        //gerar um número para o documento
        $numeroDocumento = Util::gerarNumeroDocumento(16);

        //Tipo do boleto
        $type = 'DM';

        //Validar regras
        try {
            //Buscar Url da API e token no BD
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamento = $gatewayPagamentoDao->buscarPorId(1); //Pegar valor do id dinamicamente

            //Buscar mensagem de agradecimento no BD
            $msg = $contribuicaoLog->getAgradecimento();
            //Configurar cabeçalho da requisição
            $headers = [
                'Authorization: Basic ' . base64_encode($gatewayPagamento['token'] . ':'),
                'Content-Type: application/json;charset=utf-8',
            ];

            //Montar array de Boleto

            $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());

            $boleto = [
                "items" => [
                    [
                        "amount" => $contribuicaoLog->getValor() * 100,
                        "description" => "Donation",
                        "quantity" => 1,
                        "code" => $contribuicaoLog->getCodigo()
                    ]
                ],
                "customer" => [
                    "name" => $contribuicaoLog->getSocio()->getFullName(),
                    "email" => $contribuicaoLog->getSocio()->getEmail(),
                    "document_type" => "CPF",
                    "document" => $cpfSemMascara,
                    "type" => "Individual",
                    "address" => [
                        "line_1" => $contribuicaoLog->getSocio()->getLogradouro() . ", n°" . $contribuicaoLog->getSocio()->getNumeroEndereco() . ", " . $contribuicaoLog->getSocio()->getBairro(),
                        "line_2" => $contribuicaoLog->getSocio()->getComplemento(),
                        "zip_code" => $contribuicaoLog->getSocio()->getCep(),
                        "city" => $contribuicaoLog->getSocio()->getCidade(),
                        "state" => $contribuicaoLog->getSocio()->getEstado(),
                        "country" => "BR"
                    ],
                ],
                "payments" => [
                    [
                        "payment_method" => "boleto",
                        "boleto" => [
                            "instructions" => $msg,
                            "document_number" => $numeroDocumento,
                            "due_at" => $contribuicaoLog->getDataVencimento(),
                            "type" => $type
                        ]
                    ]
                ]
            ];

            // Transformar o boleto em JSON
            $boleto_json = json_encode($boleto);

            //Iniciar requisição

            // Iniciar a requisição cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $gatewayPagamento['endPoint']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $boleto_json);

            // Executar a requisição cURL
            $response = curl_exec($ch);

            // Lidar com a resposta da API

            // Verifica por erros no cURL
            if (curl_errno($ch)) {
                curl_close($ch);
                throw new PaymentServiceException(
                    'Não foi possível gerar o boleto no momento. Tente novamente mais tarde.',
                    'Erro cURL ao gerar boleto na API Pagar.me: ' . curl_error($ch),
                    502
                );
            }

            // Obtém o código de status HTTP
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Fecha a conexão cURL
            curl_close($ch);

            // Verifica o código de status HTTP
            if ($httpCode === 200 || $httpCode === 201) {
                $responseData = json_decode($response, true);
                $pdf_link = $responseData['charges'][0]['last_transaction']['pdf'];

                //pegar o id do pedido na plataforma
                $idPagarMe = $responseData['id'];

                //armazena copia para segunda via
                $contribuicaoLog->setCodigo($idPagarMe);
                $this->guardarSegundaVia($pdf_link, $contribuicaoLog);

                //envia resposta para o front-end
                echo json_encode(['link' => $pdf_link]);
            } else {
                throw new PaymentServiceException(
                    'Não foi possível gerar o boleto no momento. Tente novamente mais tarde.',
                    "A API Pagar.me retornou o código de status HTTP $httpCode",
                    $httpCode
                );
            }

            return $idPagarMe;
        } catch (Throwable $e) {
            if ($e instanceof PaymentServiceException) {
                throw $e;
            }

            throw new PaymentServiceException(
                'Não foi possível gerar o boleto no momento. Tente novamente mais tarde.',
                'Falha inesperada ao gerar boleto na API Pagar.me: ' . $e->getMessage(),
                502,
                $e
            );
        }
    }
    public function guardarSegundaVia($pdf_link, ContribuicaoLog $contribuicaoLog)
    {
        // Diretório onde os arquivos serão armazenados
        $saveDir = '../pdfs/';

        // Verifica se o diretório existe, se não, cria o diretório
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento()); //preg_replace('/\D/', '', $contribuicaoLog->getSocio()->getDocumento());

        //$numeroAleatorio = gerarCodigoAleatorio();
        $ultimaDataVencimento = $contribuicaoLog->getDataVencimento();
        $ultimaDataVencimento = str_replace('-', '', $ultimaDataVencimento);
        $codigo = str_replace('_', '-', $contribuicaoLog->getCodigo());
        $nomeArquivo = $saveDir . $codigo . '_' . $cpfSemMascara . '_' . $ultimaDataVencimento . '_' . $contribuicaoLog->getValor() . '.pdf';

        $fileContent = PdfDownloadService::baixarConteudo($pdf_link, 'boleto');
        file_put_contents($nomeArquivo, $fileContent);
    }
}
