<?php
include_once '../classes/Estoque.php';
include_once '../dao/EstoqueDAO.php';
session_start();

class EstoqueControle
{
    public function listarTodos() {
        // Sanitização de entrada
        $nextPage = isset($_REQUEST['nextPage']) 
            ? filter_var($_REQUEST['nextPage'], FILTER_SANITIZE_URL) 
            : '../html/estoque.html';
            
        try {
            $estoqueDAO = new EstoqueDAO();
            $estoques = $estoqueDAO->listarTodos();
            $_SESSION['estoque'] = $estoques;

            // Redireciona com URL sanitizada
            header('Location: ' . htmlspecialchars($nextPage, ENT_QUOTES, 'UTF-8'));
            exit;

        } catch (PDOException $e) {
            error_log("Erro ao listar estoques: " . $e->getMessage());
            echo "Erro ao acessar os dados de estoque. Tente novamente mais tarde.";
        }
    }
}
/*
class EstoqueControle
{
    public function listarTodos(){
        extract($_REQUEST);
        $estoqueDAO= new EstoqueDAO();
        $estoques = $estoqueDAO->listarTodos();
        $_SESSION['estoque']=$estoques;
        header('Location: '.$nextPage);
    }    
}*/
?>