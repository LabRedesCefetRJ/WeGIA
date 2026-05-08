<?php
//requisição do arquivo de configuração
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';

class Conexao
{
    /**
     * Estabelece uma conexão PDO com o banco de dados da aplicação baseada nos dados contidos no arquivo de config.php,
     * em caso de falha retorna false
     */
    public static function connect():PDO|false
    {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . '; dbname=' . DB_NAME . '; charset=utf8', DB_USER, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            //posteriormente fazer um sistema de armazenamento de log de erros
            return false;
        }
    }
}
