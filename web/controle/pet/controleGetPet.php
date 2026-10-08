<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['id_pessoa'])) {
    http_response_code(401);
    die(json_encode(['erro' => 'Operação negada: Cliente não autorizado']));
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 63, 7);

require_once "./controleSaudePet.php";

$post = json_decode(file_get_contents("php://input"));
$dado = [];

foreach ($post as $valor) {
    $dado[] = $valor;
}

$c = new controleSaudePet();
$metodo = $dado[1] ?? null;

// Mesma restrição de ControleHistorico.php (refs #510): o nome do método
// vem direto do corpo da requisição.
$metodosPermitidos = ['getFichaMedicaPet'];
if (!in_array($metodo, $metodosPermitidos, true) || !method_exists('controleSaudePet', $metodo)) {
    http_response_code(400);
    die(json_encode(['erro' => 'Método inválido.']));
}

$dado = $c->$metodo($dado[0] ?? null);

echo json_encode($dado);
