<?php
require_once "../../config.php";
require_once ROOT . "/controle/VisitaControle.php";

header('Content-Type: application/json');

$ctrl = new VisitaControle();
$ctrl->listarTodos();
$dados = $_SESSION['visitas'] ?? [];

echo $dados;
exit;