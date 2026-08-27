<?php
    if (session_status() === PHP_SESSION_NONE)
        session_start();

    if (!isset($_SESSION['usuario'])) {
        http_response_code(401);
        exit('Não autenticado.');
    }

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
        $data_doacao = null;
    }

    if(!isset($valor) or ($valor == null) or ($valor == "") or empty($valor) or ($valor == "imp") or !is_numeric($valor)){
        $valor = 0;
    }

    $socio_id = filter_var($socio_id, FILTER_SANITIZE_NUMBER_INT);
    $descricao = "PAGO EM $local_recepcao, $forma_doacao, RECEBIDO POR $receptor";
    $codigo = rand() * -1;

    $stmt = mysqli_prepare($conexao, "INSERT INTO `cobrancas`(`codigo`, `descricao`, `data_pagamento`, `valor`, `valor_pago`, `status`, `linha_digitavel`, `link_cobranca`, `link_boleto`, `id_socio`) VALUES (?, ?, ?, ?, ?, 'PAGO', ?, '#', '#', ?)");
    mysqli_stmt_bind_param($stmt, "sssddsi", $codigo, $descricao, $data_doacao, $valor, $valor, $descricao, $socio_id);

    if($stmt && mysqli_stmt_execute($stmt)){
        if(mysqli_affected_rows($conexao)){
            $cadastrado = true;
        }
    }

    echo json_encode($cadastrado);
?>