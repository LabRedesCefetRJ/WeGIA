<?php
//refactore
    session_start();
    if(!isset($_SESSION['usuario'])) die("Você não está logado(a).");
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
    $conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //sql injection below
    $resultado = mysqli_query($conexao, "UPDATE `socio` SET `id_sociotag`=null WHERE id_sociotag=$id_tag");
    $resultado = mysqli_query($conexao, "DELETE FROM `socio_tag` WHERE id_sociotag=$id_tag");
    
    if(mysqli_affected_rows($conexao)){
        $_SESSION['msg'] = "Tag deletada com sucesso.";
        $_SESSION['link'] = "./socio/sistema/tags.php";
        $_SESSION['proxima'] = "Listar tags";
        header("Location: ../../sucesso.php");
    }else{
        header("Location: ./tags.php?msg_e=Erro ao deletar tag.");
    }
?>