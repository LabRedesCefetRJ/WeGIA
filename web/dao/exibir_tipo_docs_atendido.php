<?php
session_start();
require_once 'Conexao.php';

//verificar permissão
require_once '../html/permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 12, 3);

try {
	$pdo = Conexao::connect();

	$sql = 'select * from atendido_docs_atendidos ORDER BY descricao ASC;';
	$stmt = $pdo->query($sql);
	$resultado = array();
	while ($row = $stmt->fetch()) {
		$resultado[] = array('idatendido_docs_atendidos' => $row['idatendido_docs_atendidos'], 'descricao' => htmlspecialchars($row['descricao']));
	}
	echo json_encode($resultado);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['erro' => 'Problema no servidor ao buscar tipos de documento']);
}
