<?php
    session_start();
    if (!isset($_SESSION['usuario'])) die("Você não está logado(a).");

    require_once '../../permissao/permissao.php';
    permissao($_SESSION['id_pessoa'], 4, 7);

    require("../conexao.php");
    if(!isset($_POST) or empty($_POST)){
        $data = file_get_contents( "php://input" );
        $data = json_decode( $data, true );
        $_POST = $data;
    }else if(is_string($_POST)){
        $_POST = json_decode($_POST, true);
    }

    $codigo = isset($_POST['codigo']) ? (string) $_POST['codigo'] : '';
    $data_emissao = isset($_POST['data_emissao']) ? (string) $_POST['data_emissao'] : '';
    $data_vencimento_inicial = isset($_POST['data_vencimento_inicial']) ? (string) $_POST['data_vencimento_inicial'] : '';
    $data_vencimento_final = isset($_POST['data_vencimento_final']) ? (string) $_POST['data_vencimento_final'] : '';
    $tipo_carne = isset($_POST['tipo_carne']) ? (int) $_POST['tipo_carne'] : 0;
    $quantidade_boletos = isset($_POST['quantidade_boletos']) ? (int) $_POST['quantidade_boletos'] : 0;
    $valor = isset($_POST['valor']) ? (float) $_POST['valor'] : 0.0;
    $nosso_num_seq = isset($_POST['nosso_num_seq']) ? (int) $_POST['nosso_num_seq'] : 0;
    $id_socio = isset($_POST['id_socio']) ? (int) $_POST['id_socio'] : 0;

    // Use prepared statements
    $stmt = mysqli_prepare($conexao, "INSERT INTO `remessa` (`codigo`, `data_emissao`, `data_vencimento_inicial`, `data_vencimento_final`, `tipo_carne`, `quantidade_boletos`, `valor_unitario`, `nosso_num_seq`, `id_socio`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Verifique se a preparação foi bem-sucedida
    if ($stmt) {
        // Vincule os parâmetros
        mysqli_stmt_bind_param($stmt, "ssssiidii", $codigo, $data_emissao, $data_vencimento_inicial, $data_vencimento_final, $tipo_carne, $quantidade_boletos, $valor, $nosso_num_seq, $id_socio); // s d i são as representações dos tipos de cada campo. s: string, d: float, i: inteiro

        // Execute a consulta
        if (mysqli_stmt_execute($stmt)) {
            echo "Inserção bem-sucedida!";
        } else {
            // echo "Erro na execução da consulta: " . mysqli_error($conexao);
            echo $codigo . ', ' . $data_emissao . ', ' . $data_vencimento_inicial . ', ' . $data_vencimento_final . ', ' . $tipo_carne . ', ' . $quantidade_boletos . ', ' . $valor . ', ' . $id_socio;
        }

        // Feche o statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Erro na preparação da consulta: " . mysqli_error($conexao);
        echo $codigo . ', ' . $data_emissao . ', ' . $data_vencimento_inicial . ', ' . $data_vencimento_final . ', ' . $tipo_carne . ', ' . $quantidade_boletos . ', ' . $valor . ', ' . $id_socio;
    }
    
?>