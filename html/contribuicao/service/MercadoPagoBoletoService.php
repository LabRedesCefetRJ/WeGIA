<?php
require_once 'ApiBoletoServiceInterface.php';
require_once '../model/ContribuicaoLog.php';
require_once '../dao/GatewayPagamentoDAO.php';
require_once '../helper/Util.php';
class MercadoPagoBoletoService implements ApiBoletoServiceInterface
{
    public function gerarBoleto(ContribuicaoLog $contribuicaoLog)
    {
        //Xablau
        //gerar um número para o documento
        $numeroDocumento = Util::gerarCodigoAleatorio();

        //Validar regras

        //Buscar Url da API e token no BD
        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamento = $gatewayPagamentoDao->buscarPorId(3); //Pegar valor do id dinamicamente
        } catch (PDOException $e) {
            //Implementar tratamento de erro
            echo 'Erro: ' . $e->getMessage();
        }

        //Buscar mensagem de agradecimento no BD
        $msg = $contribuicaoLog->getAgradecimento();

        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());//preg_replace('/\D/', '', $contribuicaoLog->getSocio()->getDocumento());

        $dateOfExpiration = $contribuicaoLog->getDataVencimento() . 'T12:59:59.000-04:00';
        //"date_of_expiration": "2025-06-01T12:59:59.000-04:00",

        $nome = explode(" ", $contribuicaoLog->getSocio()->getNome());
        $primeiroNome = $nome[0];
        $ultimoNome = implode(" ", array_slice($nome, 1)) == "" ? " " : implode(" ", array_slice($nome, 1));

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $gatewayPagamento['endPoint'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "description": "Pagamento via Boleto",
            "transaction_amount": ' . $contribuicaoLog->getValor() . ',
            "payment_method_id": "bolbradesco",
            "date_of_expiration": "'. $dateOfExpiration .'",
            "payer": {
                "first_name": "'.$primeiroNome.'",
                "last_name": "'.$ultimoNome.'",
                "email": "'.$contribuicaoLog->getSocio()->getEmail().'",
                "identification": {
                    "type": "CPF",
                    "number": "'.$cpfSemMascara.'"
                },
                "address": {
                    "zip_code": "' .$contribuicaoLog->getSocio()->getCep(). '",
                    "city": "' .$contribuicaoLog->getSocio()->getCidade(). '",
                    "street_name": "' .$contribuicaoLog->getSocio()->getLogradouro(). '",
                    "street_number": "' .$contribuicaoLog->getSocio()->getNumeroEndereco(). '",
                    "neighborhood": "' .$contribuicaoLog->getSocio()->getBairro(). '",
                    "federal_unit": "' .$contribuicaoLog->getSocio()->getEstado(). '"
                }
            },
            "notification_url": "https://wegia.org",
            "external_reference": "'.$contribuicaoLog->getCodigo().'"
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $gatewayPagamento['token'],
            'X-Idempotency-Key: ' . $numeroDocumento
        ),
        ));

        $response = curl_exec($curl);

        // Verifica por erros no cURL
        if (curl_errno($curl)) {
            echo 'Erro na requisição: ' . curl_error($curl);
            curl_close($curl);
            return false;
        }

        // Obtém o código de status HTTP
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Fecha a conexão cURL
        curl_close($curl);

        // Verifica o código de status HTTP
        if ($httpCode === 200 || $httpCode === 201) {
            $responseData = json_decode($response, true);
            $pdf_link = $responseData['transaction_details']['external_resource_url'];

            //pegar o id do pedido na plataforma
            $idMercadoPago = $responseData['id'];

            //armazena copia para segunda via
            $contribuicaoLog->setCodigo($idMercadoPago);
            $this->guardarSegundaVia($pdf_link, $contribuicaoLog);

            //envia resposta para o front-end
            echo json_encode(['link' => $pdf_link]);
        } else {
            echo json_encode(['Erro' => 'A API retornou o código de status HTTP ' . $httpCode]);
            return false;
            // Verifica se há mensagens de erro na resposta JSON
            $responseData = json_decode($response, true);
        }

        return $idMercadoPago;
    }
    public function guardarSegundaVia($pdf_link, ContribuicaoLog $contribuicaoLog)
    {
        // Diretório onde os arquivos serão armazenados
        $saveDir = '../pdfs/';

        // Verifica se o diretório existe, se não, cria o diretório
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());//preg_replace('/\D/', '', $contribuicaoLog->getSocio()->getDocumento());

        //$numeroAleatorio = gerarCodigoAleatorio();
        $ultimaDataVencimento = $contribuicaoLog->getDataVencimento();
        $ultimaDataVencimento = str_replace('-', '', $ultimaDataVencimento);
        $codigo = str_replace('_', '-', $contribuicaoLog->getCodigo());
        $nomeArquivo = $saveDir . $codigo . '_' . $cpfSemMascara . '_' . $ultimaDataVencimento . '_' . $contribuicaoLog->getValor() . '.pdf';

        // Inicia uma sessão cURL
        $ch = curl_init($pdf_link);

        // Configurações da sessão cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // Executa a sessão cURL e obtém a resposta com cabeçalhos
        $response = curl_exec($ch);

        // Verifica se ocorreu algum erro durante a execução do cURL
        if (curl_errno($ch)) {
            echo json_encode('Erro ao baixar o arquivo.'); //. curl_error($ch) . PHP_EOL;
            exit();
        } else {
            // Verifica o código de resposta HTTP
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode == 200) {
                // Separa os cabeçalhos do corpo da resposta
                $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $headers = substr($response, 0, $headerSize);
                $fileContent = substr($response, $headerSize);
                // Verifica o tipo de conteúdo
                if (strpos($headers, 'content-type: application/pdf') !== false) {
                    // Salva o conteúdo do arquivo no diretório especificado
                    file_put_contents($nomeArquivo, $fileContent);
                    //$arquivos []= $savePath;
                } else {
                    //echo "Erro: O conteúdo da URL não é um PDF." . PHP_EOL;
                }
            } else {
                echo json_encode("Erro ao baixar o arquivo: HTTP $httpCode");
                exit();
            }
        }

        // Fecha a sessão cURL
        curl_close($ch);
    }
}
