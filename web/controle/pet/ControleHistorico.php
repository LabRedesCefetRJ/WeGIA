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

$dados = json_decode(file_get_contents("php://input"));
foreach($dados as $valor){
    $a[] = $valor;
}

$metodo = $a[0] ?? null;

// O nome do método vem direto do corpo da requisição -- restringe aos
// métodos que este endpoint realmente expõe, em vez de permitir chamar
// qualquer método público de controleSaudePet (refs #509).
$metodosPermitidos = ['getAtendimentoPet', 'dataAplicacao', 'getHistoricoPet'];
if (!in_array($metodo, $metodosPermitidos, true) || !method_exists('controleSaudePet', $metodo)) {
    http_response_code(400);
    die(json_encode(['erro' => 'Método inválido.']));
}

$c = new controleSaudePet();
echo json_encode($c->$metodo($a[1] ?? null));