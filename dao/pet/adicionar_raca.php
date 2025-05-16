<?php
require_once '../Conexao.php';
require_once '../../html/permissao/permissao.php';
session_start();
permissao($_SESSION['id_pessoa'], 6, 3);

$raca = filter_input(INPUT_POST, 'raca', FILTER_SANITIZE_STRING);
$raca = trim($raca); // Remove espaços extras
$raca = htmlspecialchars($raca, ENT_QUOTES, 'UTF-8'); // Protege contra XSS

try {
    $pdo = Conexao::connect();
    $sql = "INSERT INTO pet_raca(descricao) VALUES (:raca)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':raca', $raca, PDO::PARAM_STR);
    $stmt->execute(); // Executa a inserção
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor ao inserir a raça do pet.']);
    exit();
}