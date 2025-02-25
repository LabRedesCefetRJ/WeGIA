<?php
	require_once 'Conexao.php';
	$pdo = Conexao::connect();

	$sql = 'select * from atendido_tipo';
	$stmt = $pdo->query($sql);
	$resultado = array();
	while ($row = $stmt->fetch()) {
    	$resultado[] = array('idatendido_tipo'=>$row['idatendido_tipo'],'descricao'=>htmlspecialchars($row['descricao']));
	}
	echo json_encode($resultado);
?>