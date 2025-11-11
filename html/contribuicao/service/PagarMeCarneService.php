<?php
require_once '../model/ContribuicaoLogCollection.php';
require_once '../model/ContribuicaoLog.php';
require_once '../helper/Util.php';
require_once 'ApiCarneServiceInterface.php';
require_once '../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

class PagarMeCarneService implements ApiCarneServiceInterface
{
    public function gerarCarne(ContribuicaoLogCollection $contribuicaoLogCollection)
    {
        //definir constantes que serão usadas em todas as parcelas

        $cpfSemMascara = Util::limpaCpf($contribuicaoLogCollection->getIterator()->current()->getSocio()->getDocumento()); //Ignorar erro do VSCode para método não definida em ->current() caso esteja utilizando intelephense

        //Tipo do boleto
        $type = 'DM';

        //Validar regras

        //Buscar Url da API e token no BD
        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamento = $gatewayPagamentoDao->buscarPorId(1); //Pegar valor do id dinamicamente

            //Configurar cabeçalho da requisição
            $headers = [
                'Authorization: Basic ' . base64_encode($gatewayPagamento['token'] . ':'),
                'Content-Type: application/json;charset=utf-8',
            ];

            //Montar array de parcelas
            $parcelas = [];

            foreach ($contribuicaoLogCollection as $contribuicaoLog) {
                //gerar um número para o documento
                $numeroDocumento = Util::gerarNumeroDocumento(16);
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
                        "name" => $contribuicaoLog->getSocio()->getNome(),
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
                                "instructions" => $contribuicaoLog->getAgradecimento(),
                                "document_number" => $numeroDocumento,
                                "due_at" => $contribuicaoLog->getDataVencimento(),
                                "type" => $type
                            ]
                        ]
                    ]
                ];

                // Transformar o boleto em JSON e inserir no array de parcelas
                $parcelas[] = json_encode($boleto);
            }

            //Implementar requisição para API
            $pdf_links = [];
            $codigosAPI = [];

            // Iniciar a requisição cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $gatewayPagamento['endPoint']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            foreach ($parcelas as $boleto_json) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $boleto_json);

                // Executar a requisição cURL
                $response = curl_exec($ch);

                // Verifica por erros no cURL
                if (curl_errno($ch)) {
                    curl_close($ch);
                    throw new LogicException(curl_error($ch), 500);
                }

                // Obtém o código de status HTTP
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                // Fecha a conexão cURL
                curl_close($ch);

                // Verifica o código de status HTTP
                if ($httpCode === 200 || $httpCode === 201) {
                    $responseData = json_decode($response, true);
                    $pdf_links[] = $responseData['charges'][0]['last_transaction']['pdf'];
                    $codigosAPI[] = $responseData['id'];
                } else {
                    throw new LogicException("A API retornou o código de status HTTP $httpCode", $httpCode);
                }
            }

            //Pega os códigos retornados pela API e atribui na propriedade codigo das contribuicoes de contribuicaoLogCollection
            foreach ($contribuicaoLogCollection as $index => $contribuicaoLog) {
                $contribuicaoLog->setCodigo($codigosAPI[$index]);
            }

            $arquivos = $this->salvarTemp($pdf_links);

            //guardar segunda via
            $logArray = $contribuicaoLogCollection->getIterator()->getArrayCopy();
            $ultimaParcela = end($logArray);
            $caminho = $this->guardarSegundaVia($arquivos, $cpfSemMascara, $ultimaParcela);
            $this->removerTemp();

            if (!$caminho || empty($caminho)) {
                return false;
            }

            //Retorna o link e a coleção de contribuições
            return ['link' => $caminho, 'contribuicoes' => $contribuicaoLogCollection];
        } catch (Exception $e) {
            Util::tratarException($e);
            return false;
        }
    }

    public function salvarTemp($pdf_links)
    {
        // Diretório onde os arquivos serão armazenados
        $saveDir = '../pdfs/';
        $saveDirTemp = $saveDir . 'temp/';

        // Verifica se o diretório existe, se não, cria o diretório
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        if (!is_dir($saveDirTemp)) {
            mkdir($saveDirTemp, 0755, true);
        }

        foreach ($pdf_links as $indice => $url) {
            // Extrai o nome do arquivo a partir da URL
            $pathParts = explode('/', $url);
            $fileName = $indice . '_' . $pathParts[count($pathParts) - 2] . '.pdf';

            // Caminho completo para salvar o arquivo
            $savePath = $saveDirTemp . $fileName;

            // Inicia uma sessão cURL
            $ch = curl_init($url);

            // Configurações da sessão cURL
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, true);

            // Executa a sessão cURL e obtém a resposta com cabeçalhos
            $response = curl_exec($ch);

            // Verifica se ocorreu algum erro durante a execução do cURL
            if (curl_errno($ch)) {
                throw new LogicException('Erro ao estabelecer conexão para baixar o arquivo', 500);
            } else {
                // Verifica o código de resposta HTTP
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpCode == 200) {
                    // Separa os cabeçalhos do corpo da resposta
                    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                    $headers = substr($response, 0, $headerSize);
                    $fileContent = substr($response, $headerSize);

                    // Verifica o tipo de conteúdo
                    if (strpos($headers, 'Content-Type: application/pdf') !== false) {
                        // Salva o conteúdo do arquivo no diretório especificado
                        file_put_contents($savePath, $fileContent);
                        $arquivos[] = $savePath;
                    } else {
                        throw new LogicException('Erro: O conteúdo da URL não é um PDF.', 400);
                    }
                } else {
                    throw new LogicException("Erro ao baixar o arquivo: HTTP $httpCode", $httpCode);
                }
            }

            // Fecha a sessão cURL
            curl_close($ch);
        }

        return $arquivos;
    }

    public function removerTemp()
    {
        $dir = '../pdfs/temp';
        // Verifica se o diretório existe
        if (!file_exists($dir)) {
            return false;
        }

        // Verifica se é um diretório
        if (!is_dir($dir)) {
            return false;
        }

        // Abre o diretório
        $dirHandle = opendir($dir);

        // Percorre todos os arquivos e diretórios dentro do diretório
        while (($file = readdir($dirHandle)) !== false) {
            if ($file != '.' && $file != '..') {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file;

                // Se for um diretório, chama a função recursivamente
                if (is_dir($filePath)) {
                    removeDirectory($filePath);
                } else {
                    // Se for um arquivo, remove o arquivo
                    unlink($filePath);
                }
            }
        }

        // Fecha o diretório
        closedir($dirHandle);

        // Remove o diretório
        return rmdir($dir);
    }

    public function guardarSegundaVia($arquivos, $cpfSemMascara, $ultimaParcela)
    {
        $pdf = new Fpdi();

        // Itera sobre cada arquivo PDF
        foreach ($arquivos as $file) {
            $pageCount = $pdf->setSourceFile($file);
            // Itera sobre cada página do PDF atual
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        }


        $numeroAleatorio = str_replace('_', '-', $ultimaParcela->getCodigo());
        $ultimaDataVencimento = $ultimaParcela->getDataVencimento();
        $ultimaDataVencimento = str_replace('-', '', $ultimaDataVencimento);

        // Salva o arquivo PDF unido
        $pdf->Output('F', '../pdfs/' . $numeroAleatorio . '_' . $cpfSemMascara . '_' . $ultimaDataVencimento . '_' . $ultimaParcela->getValor() . '.pdf');

        return 'pdfs/' . $numeroAleatorio . '_' . $cpfSemMascara . '_' . $ultimaDataVencimento . '_' . $ultimaParcela->getValor() . '.pdf';
    }
}
