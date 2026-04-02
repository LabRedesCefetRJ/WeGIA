<?php
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
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
    }else $data_pagamento = mysqli_real_escape_string($conexao, $data_pagamento);

    if(!isset($valor_pago) or ($valor_pago == null) or ($valor_pago == "") or empty($valor_pago) or ($valor_pago == "imp")){
        $valor_pago = 0;
    }else $valor_pago = mysqli_real_escape_string($conexao, $valor_pago);

    if(!isset($contribuinte)){
        $contribuinte = null;
    }
    // Lidando com aspas simples e duplas
    $socio_nome = addslashes($socio_nome);
    $descricao = addslashes($descricao);
    
    $data_emissao = implode('-', array_reverse(explode('/', $data_emissao)));
    $data_vencimento = implode('-', array_reverse(explode('/', $data_vencimento)));
    $data_pagamento = implode('-', array_reverse(explode('/', $data_pagamento)));
    // si = sem informação
    $resultado = mysqli_query($conexao, "UPDATE `cobrancas` SET `status` = '$status', `valor_pago` = $valor_pago, `linha_digitavel`='$linha_digitavel' WHERE codigo=$codigo");
    if(mysqli_affected_rows($conexao)){
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
            }  break;

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
            }  break;
        }
        if($resultado = mysqli_query($conexao, "INSERT INTO `socio`(`id_pessoa`, `id_sociostatus`, `id_sociotipo`, `email`) VALUES ($id_pessoa, 4, $id_sociotipo, '$email')")){
            $id_socio = mysqli_insert_id($conexao);
            if($resultado = mysqli_query($conexao, "INSERT INTO `cobrancas`(`codigo`, `descricao`, `data_emissao`, `data_vencimento`, `data_pagamento`, `valor`, `valor_pago`, `status`, `link_cobranca`, `link_boleto`, `linha_digitavel`, `id_socio`) VALUES ($codigo, '$descricao', '$data_emissao', '$data_vencimento', '$data_pagamento', $valor, $valor_pago, '$status', '$link_cobranca', '$link_boleto', '$linha_digitavel', $id_socio)")){
                if(mysqli_affected_rows($conexao)){
                    $cadastrado = true;
                }
            }
        }
        }
    }else if(mysqli_num_rows($resultado = mysqli_query($conexao, "SELECT * FROM `pessoa` WHERE cpf='$cpf_cnpj'"))){
        $id_pessoa = mysqli_fetch_assoc($resultado)['id_pessoa'];
        if(mysqli_num_rows($resultado = mysqli_query($conexao, "SELECT * FROM `socio` WHERE id_pessoa=$id_pessoa"))){
            $id_socio = mysqli_fetch_assoc($resultado)['id_socio'];
            if($resultado = mysqli_query($conexao, "INSERT INTO `cobrancas`(`codigo`, `descricao`, `data_emissao`, `data_vencimento`, `data_pagamento`, `valor`, `valor_pago`, `status`, `link_cobranca`, `link_boleto`, `linha_digitavel`, `id_socio`) VALUES ($codigo, '$descricao', '$data_emissao', '$data_vencimento', '$data_pagamento', $valor, $valor_pago, '$status', '$link_cobranca', '$link_boleto', '$linha_digitavel', $id_socio)")){
                if(mysqli_affected_rows($conexao)){
                    $cadastrado = true;
                }
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
                }  break;
    
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
                }  break;
            }
            if($resultado = mysqli_query($conexao, "INSERT INTO `socio`(`id_pessoa`, `id_sociostatus`, `id_sociotipo`, `email`) VALUES ($id_pessoa, 4, $id_sociotipo, '$email')")){
                $id_socio = mysqli_insert_id($conexao);
                if($resultado = mysqli_query($conexao, "INSERT INTO `cobrancas`(`codigo`, `descricao`, `data_emissao`, `data_vencimento`, `data_pagamento`, `valor`, `valor_pago`, `status`, `link_cobranca`, `link_boleto`, `linha_digitavel`, `id_socio`) VALUES ($codigo, '$descricao', '$data_emissao', '$data_vencimento', '$data_pagamento', $valor, $valor_pago, '$status', '$link_cobranca', '$link_boleto', '$linha_digitavel', $id_socio)")){
                    if(mysqli_affected_rows($conexao)){
                        $cadastrado = true;
                    }
                }
            }
        }
    }
    // var_dump($_REQUEST);
    echo json_encode($cadastrado);
?>