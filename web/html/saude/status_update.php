<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 5, 5);

require_once "../../dao/Conexao.php";

$id_status = filter_input(INPUT_POST, 'id_status', FILTER_VALIDATE_INT);
$id_medicacao = filter_input(INPUT_POST, 'id_medicacao', FILTER_VALIDATE_INT);
$id_fichamedica = filter_input(INPUT_POST, 'id_fichamedica', FILTER_VALIDATE_INT);

if (!$id_status || $id_status < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id do status informado não é válido.']);
    exit();
}

if (!$id_medicacao || $id_medicacao < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id da medicação informado não é válido.']);
    exit();
}

try {
    $pdo = Conexao::connect();
    $prep = $pdo->prepare("UPDATE saude_medicacao SET saude_medicacao_status_idsaude_medicacao_status = :status WHERE id_medicacao = :id_medicacao");
    $prep->bindParam(':status', $id_status, PDO::PARAM_INT);
    $prep->bindParam(':id_medicacao', $id_medicacao, PDO::PARAM_INT);
    $prep->execute();

    $destino = $id_fichamedica ? "profile_paciente.php?id_fichamedica=" . $id_fichamedica : "profile_paciente.php";
    header("Location: " . $destino);
    exit();
} catch (PDOException $e) {
    error_log("[ERRO] " . $e->getMessage());
    http_response_code(500);
    echo "Houve um erro ao atualizar o status da medicação.";
    exit();
}
