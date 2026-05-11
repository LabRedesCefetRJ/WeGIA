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
    $descricao = "'$descricao'";
    $data_emissao = "'$data_emissao'";
    $data_pagamento = "'$data_pagamento'";
    $data_vencimento = "'$data_vencimento'";
    $link_cobranca= "'$link_cobranca'";
    $link_boleto = "'$link_boleto'";
    $linha_digitavel = "'$linha_digitavel'";
    $status = "'$status'";
    
    mysqli_query($conexao, "INSERT INTO cobrancas (`codigo`, `descricao`, `data_emissao`, `data_vencimento`, `data_pagamento`, `valor`, `valor_pago`, `status`, `link_cobranca`, `link_boleto`, `linha_digitavel`, `id_socio`) VALUES ($codigo,$descricao,$data_emissao, $data_vencimento,$data_pagamento,$valor,$valor_pago,$status,$link_cobranca,$link_boleto,$linha_digitavel,$id_socio)");
    
?>