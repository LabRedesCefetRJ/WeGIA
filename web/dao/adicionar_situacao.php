<?php
//Requisições necessárias
require_once 'Conexao.php';
require_once '../html/permissao/permissao.php';

//Verifica se um usuário está logado e possui as permissões necessárias
session_start();
permissao($_SESSION['id_pessoa'], 11, 3);

//Sanitiza a entrada.
$situacao = trim(filter_input(INPUT_POST, 'situacao', FILTER_SANITIZE_STRING));

if(!$situacao || empty($situacao)){
	http_response_code(400);
	exit('Erro, a descrição de uma nova situação não pode ser vazia.');
}

//Executa a consulta no banco de dados da aplicação
try {
	$sql = "INSERT INTO situacao(situacoes) VALUES (:situacao)";
	$pdo = Conexao::connect();
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':situacao', $situacao);
	$stmt->execute();
} catch (PDOException $e) {
	http_response_code(500);
	echo 'Erro ao inserir uma nova situação no banco de dados: '.$e->getMessage();
}