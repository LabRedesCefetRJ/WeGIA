<?php

    function fecharConexao(mysqli_stmt $stmt, mysqli $conexao){
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
    }

    session_start();
    if(!isset($_SESSION['usuario'])) die("Você não está logado(a).");

    require_once '../../permissao/permissao.php';
    permissao($_SESSION['id_pessoa'], 4, 3);

    $config_path = "config.php";
	if(file_exists($config_path)){
		require_once($config_path);
	}else{
		while(true){
			$config_path = "../" . $config_path;
			if(file_exists($config_path)) break;
		}
		require_once($config_path);
	}
    extract($_REQUEST);

    $tag = filter_var($value, FILTER_SANITIZE_STRING);
    $id_tag = filter_var($id_tag, FILTER_SANITIZE_NUMBER_INT);

    if(!$tag){
        http_response_code(400);
        echo json_encode(['erro' => 'A descrição da tag informada não é válida']);
        exit();
    }

    if(!$id_tag || $id_tag < 1){
        http_response_code(400);
        echo json_encode(['erro' => 'O id da tag informado não é válido']);
        exit();
    }

    $conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "UPDATE socio_tag SET tag=? WHERE id_sociotag=?";

    $stmt = mysqli_prepare($conexao, $sql);

    mysqli_stmt_bind_param($stmt, 'si', $tag, $id_tag);

    mysqli_stmt_execute($stmt);

    if(mysqli_affected_rows($conexao)){
        $_SESSION['msg'] = "Tag salva com sucesso.";
        $_SESSION['link'] = "./socio/sistema/tags.php";
        $_SESSION['proxima'] = "Listar tags";

        fecharConexao($stmt, $conexao);
        header("Location: ../../sucesso.php");
    }else{
        fecharConexao($stmt, $conexao);
        header("Location: ./tags.php?msg_e=Erro ao modificar tag.");
    }
?>