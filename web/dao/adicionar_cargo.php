<?php
require_once 'Conexao.php';
require_once '../html/permissao/permissao.php';

//Verifica se um usuário está logado e possui as permissões necessárias
session_start();
permissao($_SESSION['id_pessoa'], 11, 3);

// A exibição do cargo já escapa com htmlspecialchars ao renderizar o
// <select>, então aqui só precisamos validar e normalizar espaços.
$cargo = trim((string) filter_input(INPUT_POST, 'cargo', FILTER_UNSAFE_RAW));

if(!$cargo || empty($cargo)){
	http_response_code(400);
	exit('Erro, a descrição fornecida para o cargo não pode ser vazia.');
}

try {
	$sql = "INSERT INTO cargo(cargo) VALUES (:cargo)";
	$pdo = Conexao::connect();
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':cargo', $cargo);
	$stmt->execute();
} catch (PDOException $e) {
	http_response_code(500);
	echo 'Erro ao adicionar novo cargo: '.$e->getMessage();
}