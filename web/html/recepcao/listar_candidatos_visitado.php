<?php
require_once "../../config.php";
require_once ROOT . "/controle/VisitadoControle.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_pessoa'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Operação negada: Cliente não autorizado']);
    exit;
}

$tipo = filter_input(INPUT_GET, 'tipo', FILTER_UNSAFE_RAW) ?? '';

$ctrl = new VisitadoControle();
$candidatos = $ctrl->listarCandidatos($tipo);

echo json_encode($candidatos, JSON_INVALID_UTF8_SUBSTITUTE);
exit;