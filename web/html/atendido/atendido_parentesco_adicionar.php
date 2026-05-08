<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 12, 7);
require_once '../../dao/Conexao.php';

$descricao = trim(filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS));

if (!$descricao || empty($descricao)) {
    http_response_code(400);
    echo json_encode(['erro' => 'A descrição de um novo tipo de parentesco não pode ser vazia']);
    exit();
}

try {
    $pdo = Conexao::connect();
    $sql = "INSERT INTO atendido_parentesco (parentesco) VALUES (:descricao)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->execute();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Ocorreu um erro ao tentar adicionar o parentesco: ' . $e->getMessage()]);
    exit();
}
