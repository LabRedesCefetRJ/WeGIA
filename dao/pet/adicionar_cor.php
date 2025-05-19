<?php
require_once '../Conexao.php';
require_once '../../html/permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 6, 3);

$cor = filter_input(INPUT_POST, 'cor', FILTER_SANITIZE_STRING);
try {
    $pdo = Conexao::connect();
    $sql = "INSERT INTO pet_cor(descricao) values(:cor)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cor', $cor);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor ao inserir a cor do pet.']);
    exit();
}
