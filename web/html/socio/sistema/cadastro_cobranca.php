<?php
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
require("../conexao.php");

if(!isset($_POST) or empty($_POST)){
    $data = file_get_contents("php://input");
    $data = json_decode($data, true);
    $_POST = is_array($data) ? $data : [];
}else if(is_string($_POST)){
    $_POST = json_decode($_POST, true);
}

$cadastrado = false;

$codigo = isset($_POST['codigo']) ? filter_var($_POST['codigo'], FILTER_VALIDATE_INT) : null;
$valor = isset($_POST['valor']) ? filter_var($_POST['valor'], FILTER_VALIDATE_FLOAT) : null;
$valor_pago = isset($_POST['valor_pago']) ? filter_var($_POST['valor_pago'], FILTER_VALIDATE_FLOAT) : 0;

$status = $_POST['status'] ?? '';
$linha_digitavel = $_POST['linha_digitavel'] ?? '';
$cpf_cnpj = $_POST['cpf_cnpj'] ?? '';
$socio_nome = $_POST['socio_nome'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$email = $_POST['email'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$link_cobranca = $_POST['link_cobranca'] ?? '';
$link_boleto = $_POST['link_boleto'] ?? '';
$data_nasc = $_POST['data_nasc'] ?? null;
$data_pagamento = $_POST['data_pagamento'] ?? null;
$data_emissao = $_POST['data_emissao'] ?? '';
$data_vencimento = $_POST['data_vencimento'] ?? '';
$pessoa = $_POST['pessoa'] ?? '';
$contribuinte = $_POST['contribuinte'] ?? null;

if ($codigo === false || $codigo === null || $valor === false || $valor === null) {
    http_response_code(400);
    echo json_encode(false);
    exit;
}

if ($valor_pago === false || $valor_pago === null || $valor_pago === '') {
    $valor_pago = 0;
}

$status = mysqli_real_escape_string($conexao, $status);
$linha_digitavel = mysqli_real_escape_string($conexao, $linha_digitavel);
$cpf_cnpj = mysqli_real_escape_string($conexao, $cpf_cnpj);
$socio_nome = mysqli_real_escape_string($conexao, $socio_nome);
$telefone = mysqli_real_escape_string($conexao, $telefone);
$email = mysqli_real_escape_string($conexao, $email);
$descricao = mysqli_real_escape_string($conexao, $descricao);
$link_cobranca = mysqli_real_escape_string($conexao, $link_cobranca);
$link_boleto = mysqli_real_escape_string($conexao, $link_boleto);

if(!isset($data_nasc) or ($data_nasc == null) or ($data_nasc == "") or empty($data_nasc) or ($data_nasc == "imp")){
    $data_nasc = "null";
}else{
    $data_nasc = mysqli_real_escape_string($conexao, $data_nasc);

    if (DateTime::createFromFormat('Y-m-d', $data_nasc) !== false) {
        $data_nasc = "'$data_nasc'";
    } else {
        $data_nasc = "null"; 
    }
}

if(!isset($data_pagamento) or ($data_pagamento == null) or ($data_pagamento == "") or empty($data_pagamento) or ($data_pagamento == "imp")){
    $data_pagamento = "0000-00-00";
}else {
    $data_pagamento = mysqli_real_escape_string($conexao, $data_pagamento);
}

if(!isset($contribuinte)){
    $contribuinte = null;
}

$socio_nome = addslashes($socio_nome);
$descricao = addslashes($descricao);

$data_emissao = implode('-', array_reverse(explode('/', $data_emissao)));
$data_vencimento = implode('-', array_reverse(explode('/', $data_vencimento)));
$data_pagamento = implode('-', array_reverse(explode('/', $data_pagamento)));

$stmt = mysqli_prepare($conexao, "UPDATE `cobrancas` SET `status` = ?, `valor_pago` = ?, `linha_digitavel` = ? WHERE codigo = ?");
mysqli_stmt_bind_param($stmt, "sdsi", $status, $valor_pago, $linha_digitavel, $codigo);
mysqli_stmt_execute($stmt);

if(mysqli_stmt_affected_rows($stmt)){
    $cadastrado = true;
}else if(!mysqli_num_rows($resultado = mysqli_query($conexao, "SELECT * FROM `pessoa` WHERE cpf='$cpf_cnpj'"))){
    if($resultado = mysqli_query($conexao, "INSERT INTO `pessoa`(`cpf`, `nome`, `telefone`) VALUES ('$cpf_cnpj', '$socio_nome',  '$telefone')")){
        $id_pessoa = mysqli_insert_id($conexao);

        switch($pessoa){
            case "juridica": 
                if($contribuinte == "mensal"){
                    $id_sociotipo = 3;
                }else if($contribuinte == "casual"){
                    $id_sociotipo = 1;
                }else if($contribuinte == "bimestral"){
                    $id_sociotipo = 7;
                }else if($contribuinte == "trimestral"){
                    $id_sociotipo = 9;
                }else if($contribuinte == "semestral"){
                    $id_sociotipo = 11;
                }

                if($contribuinte == null || $contribuinte == "si" || $contribuinte == ""){
                    $id_sociotipo = 5;
                }
                break;

            case "fisica": 
                if($contribuinte == "mensal"){
                    $id_sociotipo = 2;
                }else if($contribuinte == "casual"){
                    $id_sociotipo = 0;
                }else if($contribuinte == "bimestral"){
                    $id_sociotipo = 6;
                }else if($contribuinte == "trimestral"){
                    $id_sociotipo = 8;
                }else if($contribuinte == "semestral"){
                    $id_sociotipo = 10;
                }

                if($contribuinte == null || $contribuinte == "si" || $contribuinte == ""){
                    $id_sociotipo = 4;
                }
                break;
        }

        $stmt = mysqli_prepare($conexao, "INSERT INTO `socio`(`id_pessoa`, `id_sociostatus`, `id_sociotipo`, `email`) VALUES (?, 4, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $id_pessoa, $id_sociotipo, $email);
        $resultado = mysqli_stmt_execute($stmt);

        if($resultado){
            $id_socio = mysqli_insert_id($conexao);

            $stmt = mysqli_prepare($conexao, "INSERT INTO `cobrancas`(`codigo`, `descricao`, `data_emissao`, `data_vencimento`, `data_pagamento`, `valor`, `valor_pago`, `status`, `link_cobranca`, `link_boleto`, `linha_digitavel`, `id_socio`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issssddssssi", $codigo, $descricao, $data_emissao, $data_vencimento, $data_pagamento, $valor, $valor_pago, $status, $link_cobranca, $link_boleto, $linha_digitavel, $id_socio);
            $resultado = mysqli_stmt_execute($stmt);

            if($resultado && mysqli_stmt_affected_rows($stmt)){
                $cadastrado = true;
            }
        }
    }
}else if(mysqli_num_rows($resultado = mysqli_query($conexao, "SELECT * FROM `pessoa` WHERE cpf='$cpf_cnpj'"))){
    $id_pessoa = mysqli_fetch_assoc($resultado)['id_pessoa'];

    $stmt = mysqli_prepare($conexao, "SELECT * FROM `socio` WHERE id_pessoa = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_pessoa);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($resultado)){
        $id_socio = mysqli_fetch_assoc($resultado)['id_socio'];

        $stmt = mysqli_prepare($conexao, "INSERT INTO `cobrancas`(`codigo`, `descricao`, `data_emissao`, `data_vencimento`, `data_pagamento`, `valor`, `valor_pago`, `status`, `link_cobranca`, `link_boleto`, `linha_digitavel`, `id_socio`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issssddssssi", $codigo, $descricao, $data_emissao, $data_vencimento, $data_pagamento, $valor, $valor_pago, $status, $link_cobranca, $link_boleto, $linha_digitavel, $id_socio);
        $resultado = mysqli_stmt_execute($stmt);

        if($resultado && mysqli_stmt_affected_rows($stmt)){
            $cadastrado = true;
        }
    }else{
        switch($pessoa){
            case "juridica": 
                if($contribuinte == "mensal"){
                    $id_sociotipo = 3;
                }else if($contribuinte == "casual"){
                    $id_sociotipo = 1;
                }else if($contribuinte == "bimestral"){
                    $id_sociotipo = 7;
                }else if($contribuinte == "trimestral"){
                    $id_sociotipo = 9;
                }else if($contribuinte == "semestral"){
                    $id_sociotipo = 11;
                }

                if($contribuinte == null || $contribuinte == "si" || $contribuinte == ""){
                    $id_sociotipo = 5;
                }
                break;

            case "fisica": 
                if($contribuinte == "mensal"){
                    $id_sociotipo = 2;
                }else if($contribuinte == "casual"){
                    $id_sociotipo = 0;
                }else if($contribuinte == "bimestral"){
                    $id_sociotipo = 6;
                }else if($contribuinte == "trimestral"){
                    $id_sociotipo = 8;
                }else if($contribuinte == "semestral"){
                    $id_sociotipo = 10;
                }

                if($contribuinte == null || $contribuinte == "si" || $contribuinte == ""){
                    $id_sociotipo = 4;
                }
                break;
        }

        $stmt = mysqli_prepare($conexao, "INSERT INTO `socio`(`id_pessoa`, `id_sociostatus`, `id_sociotipo`, `email`) VALUES (?, 4, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $id_pessoa, $id_sociotipo, $email);
        $resultado = mysqli_stmt_execute($stmt);

        if($resultado){
            $id_socio = mysqli_insert_id($conexao);

            $stmt = mysqli_prepare($conexao, "INSERT INTO `cobrancas`(`codigo`, `descricao`, `data_emissao`, `data_vencimento`, `data_pagamento`, `valor`, `valor_pago`, `status`, `link_cobranca`, `link_boleto`, `linha_digitavel`, `id_socio`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issssddssssi", $codigo, $descricao, $data_emissao, $data_vencimento, $data_pagamento, $valor, $valor_pago, $status, $link_cobranca, $link_boleto, $linha_digitavel, $id_socio);
            $resultado = mysqli_stmt_execute($stmt);

            if($resultado && mysqli_stmt_affected_rows($stmt)){
                $cadastrado = true;
            }
        }
    }
}

echo json_encode($cadastrado);
?>