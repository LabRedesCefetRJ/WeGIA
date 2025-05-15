<?php
$config_path = "config.php";
$loopLimit = 2000;
if(file_exists($config_path)){
    require_once($config_path);
}else{
    while(true){
        $loopLimit--;
        $config_path = "../" . $config_path;
        if(file_exists($config_path)) break;
        if($loopLimit < 0) { 
            // Caso config.php não seja encontrado
            header("Location: ". WWW ."instalador/index.php");
            break;
        }
    }
    require_once($config_path);
}

class  Conexao
{
    public static function connect()
    {
        $pdo = new PDO('mysql:host='.DB_HOST.'; dbname='.DB_NAME.'; charset=utf8',DB_USER,DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}


