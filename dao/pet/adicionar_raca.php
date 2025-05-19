<?php
require_once '../Conexao.php';
require_once '../../html/permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 6, 3);

$raca = filter_input(INPUT_POST, 'raca', FILTER_SANITIZE_STRING);
try {
    $pdo = Conexao::connect();
    $sql = "INSERT INTO pet_raca(descricao) values(:raca)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':raca', $raca);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor ao inserir a raça do pet.']);
    exit();
}
