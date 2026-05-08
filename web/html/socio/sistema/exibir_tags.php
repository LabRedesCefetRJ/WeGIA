<?php
    require_once('../conexao.php');
    $dados = [];
    $query = mysqli_query($conexao, "SELECT * FROM socio_tag");
    while($resultado = mysqli_fetch_assoc($query)){
        $dados[] = $resultado;
    }
	echo json_encode($dados);
?>