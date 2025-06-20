<?php
	require_once '../../dao/Conexao.php';

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	$enfermidadeCid = $_POST["cid"];
	$enfermidadeNome = $_POST["nome"];

	$enfermidadeCid = trim($enfermidadeCid);
	$enfermidadeNome = trim($enfermidadeNome);

	if(!$enfermidadeCid || !$enfermidadeNome){
		echo 'As informações referentes ao CID e ao nome da enfermidade devem ser preenchidas para que o cadastro ocorra';
		exit(400);
	}
	
	try{
		$pdo = Conexao::connect();
		$sql = "INSERT into saude_tabelacid(CID, descricao) values(:enfermidadeCid, :enfermidadeNome)";

		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':enfermidadeCid', $enfermidadeCid);
		$stmt->bindParam('enfermidadeNome', $enfermidadeNome);
		$stmt->execute();
	}catch(PDOException $e){
		echo 'Erro ao cadastrar enfermidade: '.$e->getMessage();
	}
	
?>