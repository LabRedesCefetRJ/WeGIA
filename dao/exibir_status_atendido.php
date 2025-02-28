<?php
	require_once'Conexao.php';
	$pdo = Conexao::connect();

	$sql = 'select * from atendido_status';
	$stmt = $pdo->query($sql);
	$resultado = array();
	while ($row = $stmt->fetch()) {
    	$resultado[] = array('idatendido_status'=>$row['idatendido_status'],'status'=>htmlspecialchars($row['status']));
	}
	echo json_encode($resultado);
?>