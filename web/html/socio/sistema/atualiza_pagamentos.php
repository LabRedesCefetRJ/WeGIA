<?php
    require("../conexao.php");
    if(!isset($_POST) or empty($_POST)){
        $data = file_get_contents( "php://input" );
        $data = json_decode( $data, true );
        $_POST = $data;
    }else if(is_string($_POST)){
        $_POST = json_decode($_POST, true);
    }

    extract($_REQUEST);
    $cadastrado = false;

    $stmt = mysqli_prepare($conexao, "UPDATE cobrancas SET status='Pago', data_pagamento=?, valor_pago=? WHERE codigo=?");

    mysqli_stmt_bind_param($stmt, "sdi", $data, $valor, $codigo);

    mysqli_stmt_execute($stmt);

    if(mysqli_affected_rows($conexao)) $cadastrado = true;

    echo(json_encode($cadastrado));
?>