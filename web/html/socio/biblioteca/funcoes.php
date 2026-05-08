<?php
    if (!function_exists('msg')) {
        function msg($tipo, $mensagem){
            $mensagem = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); //Previne XSS pois transforma em texto, impedindo scripts maliciosos
            switch($tipo){
                case "aviso": 
                    $_msg = "<div class='alert alert-warning' role='alert'>$mensagem</div>"; break;
                case "erro":
                    $_msg = "<div class='alert alert-danger' role='alert'>$mensagem</div>"; break;
                case "sucesso":
                    $_msg = "<div class='alert alert-success' role='alert'>$mensagem</div>"; break;
                default: $_msg = "<div class='alert alert-primary' role='alert'>$mensagem</div>"; break;
            }
            echo($_msg);
        }
    }
    if(!function_exists('redir')){
        function redir($local, $tempo){
            $tempo = filter_var($tempo, FILTER_VALIDATE_INT); //Filtra a variável para ser número inteiro
            if(empty($tempo)) $tempo = 0;
            $local = filter_var($local, FILTER_SANITIZE_URL); //Filtra a variável para receber apenas caracteres de URL
            if(filter_var($local, FILTER_VALIDATE_URL)){ //Valida o URL
                echo("<META HTTP-EQUIV='Refresh' CONTENT='$tempo;URL=$local'>");
            }
        }
    }
?>