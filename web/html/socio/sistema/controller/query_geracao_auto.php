<?php
/*
    require("../../conexao.php");
    if(!isset($_POST) or empty($_POST)){
        $data = file_get_contents( "php://input" );
        $data = json_decode( $data, true );
        $_POST = $data;
    }else if(is_string($_POST)){
        $_POST = json_decode($_POST, true);
    }
    $conexao->set_charset("utf8");
    extract($_REQUEST);
    $query = mysqli_query($conexao, $query);
    while($resultado = mysqli_fetch_assoc($query)){
        $dados[] = $resultado;
    }

    // array_walk_recursive($dados, function (&$val) {
    //     if (is_string($val)) {
    //         $val = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
    //     }
    // });
    if(isset($dados)){
        echo json_encode($dados);
    }else */

// Obtendo informações sobre a requisição
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Referer não disponível';

// Mensagem de erro
$mensagem = [
    "erro" => true,
    "mensagem" => "query_geracao_auto.php foi descontinuada. A execução de queries diretamente como parâmetro de requisição não é mais permitida por razões de segurança.",
    "solucao" => "Atualize sua aplicação para utilizar métodos seguros de acesso ao banco de dados.",
    "referer" => $referer
];

// Retorno da mensagem
header('Content-Type: application/json');
echo json_encode($mensagem);
exit;
