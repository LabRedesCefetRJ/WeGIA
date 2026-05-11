<?php
require_once dirname(__DIR__, 3).'/config.php';
class ConexaoDAO{
    public static function conectar(){
        $pdo = new PDO('mysql:host='.DB_HOST.'; dbname='.DB_NAME.'; charset=utf8',DB_USER,DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}