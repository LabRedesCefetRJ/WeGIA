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

    $codigo = "$codigo";
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