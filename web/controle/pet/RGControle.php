<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['id_pessoa'])) {
    http_response_code(401);
    die(json_encode(['erro' => 'Operação negada: Cliente não autorizado']));
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 63, 7);

require_once 'AdocaoControle.php';

$post = file_get_contents( 'php://input');

$dado = json_decode($post);

foreach( $dado as $value){
    $rgAdotante = $value;
}

$dado = $a->nomeAdotante($rgAdotante);

echo json_encode($dado);
//echo $dado;