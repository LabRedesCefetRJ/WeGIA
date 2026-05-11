<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'AlmoxarifeDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';

class AlmoxarifeControle
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if(is_null($pdo)){
            $this->pdo = Conexao::connect();
        }else{
            $this->pdo = $pdo;
        }
    }

    public function listarTodos()
    {
        try {
            $almoxarifeDAO = new almoxarifeDAO($this->pdo);
            $almoxarifes = $almoxarifeDAO->listarTodos();
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['almoxarife'] = $almoxarifes;
            header('Location: ' . WWW . 'html/matPat/listar_almoxarife.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function excluir()
    {
        $id_almoxarife = filter_var($_REQUEST['id_almoxarife'], FILTER_SANITIZE_NUMBER_INT);

        try {
            if(!Csrf::validateToken($_POST['csrf_token'])){
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);
            }

            if (!$id_almoxarife || !is_numeric($id_almoxarife) || $id_almoxarife < 1) {
                throw new InvalidArgumentException('O id de um almoxarife deve ser um inteiro maior ou igual a 1.', 400);
            }

            $almoxarifeDAO = new almoxarifeDAO($this->pdo);
            $almoxarifeDAO->excluir($id_almoxarife);
            header('Location: ' . WWW . 'html/matPat/listar_almoxarife.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
