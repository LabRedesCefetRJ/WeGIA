<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['id_pessoa'])) {
    http_response_code(401);
    die(json_encode(['erro' => 'Operação negada: Cliente não autorizado']));
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 63, 7);

require_once './MedicamentoControle.php';
header("Content-Type: application/json;charset=UTF-8");

$c = new MedicamentoControle();

$p = $c->listarMedicamento();

http_response_code(200);
die(json_encode($p));
