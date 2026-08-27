<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['id_pessoa'])) {
    http_response_code(401);
    die(json_encode(['erro' => 'Operação negada: Cliente não autorizado']));
}

require_once "./controleSaudePet.php";

$dados = json_decode(file_get_contents("php://input"));
foreach($dados as $valor){
    $a[] = $valor;
}

$metodo = $a[0];

$c = new controleSaudePet();
echo json_encode($c->$metodo($a[1]));