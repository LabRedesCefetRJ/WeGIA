<?php
    require("../../conexao.php");

    $resultado = false;
    $chave_correta = "h)8^#w4T<HaN-GSc&BM3[<?mvG[R?b";

    if(isset($_GET['chave'])){
        $chave = $_GET['chave'];

        $chave_hash = hash('sha256', $chave);
        $chave_correta_hash = hash('sha256', $chave_correta);

        if($chave_hash === $chave_correta_hash){
            $tabelas = array("endereco","pessoafisica","pessoajuridica","socio");
            $i = 0;
            mysqli_query($conexao,"SET FOREIGN_KEY_CHECKS=0");
            foreach ($tabelas as $n => $tabela) {
                $verif_tabela = mysqli_real_escape_string($conexao, $tabela);

                $r_query = mysqli_query($conexao, "TRUNCATE TABLE $verif_tabela");
                if(mysqli_affected_rows($conexao)) $i++;

                if (!$r_query) {
                    $resultado = false;
                    mysqli_query($conexao, "SET FOREIGN_KEY_CHECKS=1");
                    die(json_encode($resultado));
                }
            }
            $resultado = true;
            mysqli_query($conexao,"SET FOREIGN_KEY_CHECKS=1");
        }
    }
    
    echo json_encode($resultado);
?>