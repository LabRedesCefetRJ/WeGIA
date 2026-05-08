<?php
//Requisições necessárias
require_once 'Conexao.php';
require_once '../html/permissao/permissao.php';

//Verifica se um usuário está logado e possui as permissões necessárias
session_start();
permissao($_SESSION['id_pessoa'], 11, 3);

$pdo = Conexao::connect();

try {
	$sql = 'SELECT * FROM situacao';
	$stmt = $pdo->query($sql);
	$resultado = array();
	while ($row = $stmt->fetch()) {
		$resultado[] = array('id_situacao' => $row['id_situacao'], 'situacoes' => htmlspecialchars($row['situacoes']));
	}
	echo json_encode($resultado);
} catch (PDOException $e) {
	http_response_code(500);
	echo $e->getMessage();
}
