<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'sessaoCookie.php';
	session_start();
	require_once 'Conexao.php';

	//verificar permissão
	require_once '../html/permissao/permissao.php';
	permissao($_SESSION['id_pessoa'], 12, 3);
	
	$status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));

	if(!$status || empty($status)){
		http_response_code(400);
		exit('Erro, a descrição de um novo tipo de status não pode ser vazia.');
	}

	try{
		$pdo = Conexao::connect();
		$sql = "INSERT into atendido_status(status) values(:status)";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':status', $status);
		$stmt->execute();
	}catch(PDOException $e){
		echo 'Erro ao inserir novo tipo de status no banco de dados: '.$e->getMessage();
	}