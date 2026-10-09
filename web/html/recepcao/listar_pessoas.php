<?php
require_once "../../config.php";
require_once ROOT . "/controle/VisitadoControle.php";
require_once ROOT . "/controle/VisitanteControle.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_pessoa'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Operação negada: Cliente não autorizado']);
    exit;
}

$tipo = filter_input(INPUT_GET, 'tipo', FILTER_UNSAFE_RAW) ?? '';

$ctrl = new VisitadoControle();
$visitados = $ctrl->listarTodos($tipo, VisitanteControle::obterIdsSelecionados());

echo json_encode($visitados, JSON_INVALID_UTF8_SUBSTITUTE);
exit;