<?php
require_once 'Conexao.php';

$descricao = trim(filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING));

if (!$descricao || empty($descricao)) {
	http_response_code(400);
	echo json_encode(['erro' => 'A descrição de um novo tipo de documentação não pode ser vazia.']);
	exit();
}

try {
	$sql = "INSERT INTO atendido_docs_atendidos(descricao) VALUES (:descricao)";
	$pdo = Conexao::connect();
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':descricao', $descricao);
	$stmt->execute();
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['erro' => 'Erro ao inserir um novo tipo de documentação no banco de dados: ' . $e->getMessage()]);
}
