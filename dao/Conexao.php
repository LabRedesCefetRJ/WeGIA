<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';

class  Conexao
{
    public static function connect()
    {
        $pdo = new PDO('mysql:host='.DB_HOST.'; dbname='.DB_NAME.'; charset=utf8',DB_USER,DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}


