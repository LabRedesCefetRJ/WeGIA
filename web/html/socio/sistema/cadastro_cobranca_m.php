<?php
    require("../conexao.php");
    if(!isset($_POST) or empty($_POST)){
        $data = file_get_contents( "php://input" );
        $data = json_decode( $data, true );
        $_POST = $data;
    }else if(is_string($_POST)){
        $_POST = json_decode($_POST, true);
    }
    $cadastrado =  false;
    extract($_REQUEST);
    if(!isset($data_doacao) or ($data_doacao == null) or ($data_doacao == "") or empty($data_doacao) or ($data_doacao == "imp")){
        $data_doacao = "null";
    }else $data_doacao = "'$data_doacao'";

    if(!isset($valor) or ($valor == null) or ($valor == "") or empty($valor) or ($valor == "imp")){
        $valor = 0;
    }else $valor = $valor;

    // Lidando com aspas simples e duplas
    $socio_nome = addslashes($socio_nome);
    $local_recepcao = addslashes($local_recepcao);
    $codigo = rand() * -1;

    $stmt = mysqli_prepare($conexao, "INSERT INTO `cobrancas`(`codigo`, `descricao`, `data_pagamento`, `valor`, `valor_pago`, `status`, `linha_digitavel`, `link_cobranca`, `link_boleto`, `id_socio`) VALUES ($codigo, 'PAGO EM $local_recepcao, $forma_doacao, RECEBIDO POR $receptor', $data_doacao, $valor, $valor, 'PAGO', 'PAGO EM $local_recepcao, $forma_doacao, RECEBIDO POR $receptor', '#', '#', $socio_id)");
    
    if($stmt && mysqli_stmt_execute($stmt)){
        if(mysqli_affected_rows($conexao)){
            $cadastrado = true;
        }
    }
    
    echo json_encode($cadastrado);
?>