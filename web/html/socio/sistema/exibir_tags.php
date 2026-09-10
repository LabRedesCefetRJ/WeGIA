<?php
    if (session_status() === PHP_SESSION_NONE)
        session_start();

    if (!isset($_SESSION['usuario'])) {
        http_response_code(401);
        exit('Não autenticado.');
    }

    require_once('../conexao.php');
    $dados = [];
    $query = mysqli_query($conexao, "SELECT * FROM socio_tag");
    while($resultado = mysqli_fetch_assoc($query)){
        $dados[] = $resultado;
    }
	echo json_encode($dados);
?>