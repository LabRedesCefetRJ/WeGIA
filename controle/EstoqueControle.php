<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

include_once '../classes/Estoque.php';
include_once '../dao/EstoqueDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';

class EstoqueControle
{
    public function listarTodos()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(estoque)\.php)$#';

        try {
            $estoqueDAO = new EstoqueDAO();
            $estoques = $estoqueDAO->listarTodos();
            $_SESSION['estoque'] = $estoques;

            preg_match($regex, $nextPage) ? header('Location: ' . htmlspecialchars($nextPage, ENT_QUOTES, 'UTF-8')) : header('Location: ' . WWW . 'html/home.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao listar estoques: " . $e->getMessage());
            echo "Erro ao acessar os dados de estoque. Tente novamente mais tarde.";
        }
    }
}
