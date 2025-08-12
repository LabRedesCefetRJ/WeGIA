<?php
require_once 'ApiPixServiceInterface.php';
require_once '../helper/Util.php';
class MercadoPagoPixService implements ApiPixServiceInterface
{
    public function gerarQrCode(ContribuicaoLog $contribuicaoLog)
    {
        //Validar regras

        //Buscar Url da API e token no BD
        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamento = $gatewayPagamentoDao->buscarPorId(3); //Pegar valor do id dinamicamente
        } catch (PDOException $e) {
            //Implementar tratamento de erro
            echo 'Erro: ' . $e->getMessage();
            return false;
        }

        // Configuração dos dados para a API
        $numeroDocumento = Util::gerarCodigoAleatorio();
        $description = $contribuicaoLog->getAgradecimento();
        $expires_in = 3600;

        //Configura os dados a serem enviados

        //gerar um número aleatório para o parâmetro code
        $code = $contribuicaoLog->getCodigo();
        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());
        $telefone = Util::limpaTelefone($contribuicaoLog->getSocio()->getTelefone());
        $nome = explode(" ", $contribuicaoLog->getSocio()->getNome());
        $primeiroNome = $nome[0];
        $ultimoNome = implode(" ", array_slice($nome, 1)) == "" ? " " : implode(" ", array_slice($nome, 1));

        /*
            'mobile_phone' => [
                'country_code' => '55',
                'area_code' => substr($telefone, 0, 2),
                'number' => substr($telefone, 2)
            ],
            'pix' => [
                'expires_in' => $expires_in
            ]
        ];
        */
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
            "description": "Pagamento via Pix",
            "transaction_amount": ' . $contribuicaoLog->getValor() . ',
            "payment_method_id": "pix",
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
            "external_reference": "'.$code.'"
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
        } else {
            echo json_encode(['erro' => $response]);
            return false;
            // Verifica se há mensagens de erro na resposta JSON
            $responseData = json_decode($response, true);
            if (isset($responseData['errors'])) {
                //echo 'Detalhes do erro:';
                foreach ($responseData['errors'] as $error) {
                    //echo '<br>- ' . htmlspecialchars($error['message']);
                }
            }
        }
        //Mexer nessa parte com base na resposta
        //Verifica se o status é 'pending'
        if ($responseData['status'] === 'pending') {
            // Gera um qr_code
            $qr_code_url = $responseData['point_of_interaction']['transaction_data']['qr_code_base64'];

            $qr_code = $responseData['point_of_interaction']['transaction_data']['qr_code'];
            $idPedido = $responseData['id'];
            //envia o link da url
            echo json_encode(['qrcode' => $qr_code_url, 'copiaCola' => $qr_code]); //enviar posteriormente a cópia do QR para área de transferência junto
            return $idPedido;
        } else {
            echo json_encode(["erro" => "Houve um erro ao gerar o QR CODE de pagamento. Verifique se as informações fornecidas são válidas."]);
            return false;
        }

        return true;
    }
}
