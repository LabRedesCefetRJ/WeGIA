<?php
session_start();
require_once 'Conexao.php';

//verificar permissão
require_once '../html/permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 12, 3);

$descricao = trim(filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING));

if(!$descricao || empty($descricao)){
	http_response_code(400);
	echo json_encode(['erro' => 'Erro, a descrição de um novo tipo não poder ser vazia.']);
	exit();
}

try{
	$sql = "INSERT into atendido_tipo(descricao) values(:descricao)";
	$pdo = Conexao::connect();
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':descricao', $descricao);
	$stmt->execute();
}catch(PDOException $e){
	echo json_encode(['erro' => 'Erro ao inserir a descrição do novo tipo no banco de dados: '.$e->getMessage()]);
}