<?php
    // Importando constantes do config.php
    $config_path = "config.php";
	if(file_exists($config_path)){
		require_once($config_path);
	}else{
		while(true){
			$config_path = "../" . $config_path;
			if(file_exists($config_path)) break;
		}
		require_once($config_path);
	}
    require_once ROOT . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
    // Setando a conexão com o BD
    $conexao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die("Erro ao conectar-se ao banco de dados.");
    // Verifica se a api está nos enviando um chargeReference válido e não nulo
    if(isset($_POST['chargeReference']) and $_POST['chargeReference'] != ''){
        Util::definirFusoHorario();
        // Extraindo variáveis retornadas pela API (paymentToken, chargeReference e chargeCode)
        extract($_POST);

        if($chargeReference == '' || $chargeCode == ''){
            exit;
        }else{
            $url = "https://sandbox.boletobancario.com/boletofacil/integration/api/v1/fetch-payment-details";
            $datas = array('paymentToken'=>''.$paymentToken.'');
            $data = http_build_query($datas);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURLOPT_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $xmlres = curl_exec($curl);
            $retorno = json_decode($xmlres, true);
            $codePag = $retorno['success'];
            echo($retorno);
            if($codePag == 'true'){
                // precisa adicionar o campo referencia no bd e o campo status
                $referencia = $retorno['data']['payment']['charge']['reference'];
                $status = $retorno['data']['payment']['status'];
                $stmtCheck = mysqli_prepare($conexao, "SELECT * FROM `log_contribuicao` WHERE referencia = ?");
                mysqli_stmt_bind_param($stmtCheck, "s", $referencia);
                mysqli_stmt_execute($stmtCheck);
                mysqli_stmt_store_result($stmtCheck);
                if(mysqli_stmt_num_rows($stmtCheck)){
                    $stmtUpdate = mysqli_prepare($conexao, "UPDATE `log_contribuicao` SET `status`= ? WHERE referencia = ?");
                    mysqli_stmt_bind_param($stmtUpdate, "ss", $status, $referencia);
                    $resultado = mysqli_stmt_execute($stmtUpdate) or die(mysqli_connect_error());
                }else{
                    // Ainda não sei o que fazer caso entre o pagamento de uma pessoa não cadastrada
                }
            }

        }
    }
?>
