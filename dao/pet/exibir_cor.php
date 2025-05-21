<?php
	require_once '../Conexao.php';
	$pdo = Conexao::connect();
	$sql = 'select * from pet_cor';
	$stmt = $pdo->query($sql);
	$resultado = array();
	while ($row = $stmt->fetch()) {
    	$resultado[] = array('id_cor'=>$row['id_pet_cor'],'cor'=>$row['descricao']);
	}
	echo json_encode($resultado);
?>