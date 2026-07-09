<?php
require_once "../../config.php";
require_once ROOT . "/controle/AtendidoControle.php";
require_once ROOT . "/controle/FuncionarioControle.php";
require_once ROOT . "/controle/VoluntarioControle.php";

header('Content-Type: application/json');

$tipo = $_GET['tipo'] ?? '';

switch ($tipo) {

    case 'atendido':
        $ctrl = new AtendidoControle();
        $ctrl->listarTodos2();
        $dados = $_SESSION['atendidos2'];
        break;

    case 'funcionario':
        $ctrl = new FuncionarioControle();
        $ctrl->listarTodos2();
        $dados = $_SESSION['funcionarios2'];
        break;

    case 'voluntario':
        $ctrl = new VoluntarioControle();
        $ctrl->listarTodos2();
        $dados = $_SESSION['voluntarios2'];
        break;

    default:
        $dados = [];
}

echo $dados;
exit;