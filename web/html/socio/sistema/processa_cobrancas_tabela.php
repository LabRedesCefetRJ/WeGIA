<?php
    require("../conexao.php");
    if(!isset($_POST) or empty($_POST)){
        $data = file_get_contents( "php://input" );
        $data = json_decode( $data, true );
        $_POST = $data;
    }else if(is_string($_POST)){
        $_POST = json_decode($_POST, true);
    }
    $conexao->set_charset("utf8");

    $query = mysqli_query($conexao, "SELECT c.codigo, p.nome, DATE_FORMAT(data_emissao, '%d/%m/%Y'), DATE_FORMAT(data_vencimento, '%d/%m/%Y'), DATE_FORMAT(data_pagamento, '%d/%m/%Y'), c.valor, c.valor_pago, c.status FROM cobrancas c JOIN socio s ON s.id_socio = c.id_socio JOIN pessoa p ON s.id_pessoa = p.id_pessoa");
    while($resultado = mysqli_fetch_assoc($query)){
        $dados['data'][] = array_values($resultado);
    }

    echo json_encode($dados);
?>