<?php
require_once '../Conexao.php';
require_once '../../html/permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 6, 3);

$especie = filter_input(INPUT_POST, 'especie', FILTER_SANITIZE_STRING);
try {
    $pdo = Conexao::connect();
    $sql = "INSERT INTO pet_especie(descricao) values(:especie)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':especie', $especie);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor ao inserir a espécie do pet.']);
    exit();
}
