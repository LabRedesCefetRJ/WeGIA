<?php
include_once ROOT . '/classes/Categoria.php';
include_once ROOT . '/dao/CategoriaDAO.php';
class CategoriaControle
{
    public function verificar(){
        $descricao_categoria = trim($_REQUEST['descricao_categoria']);
    
        try{
            $categoria = new Categoria($descricao_categoria);
            return $categoria;
        }catch(InvalidArgumentException $e){
            header('Location: '. WWW .'html/home.php?msg_c='.$e->getMessage());//Envio de temporariamente para a home, posteriormente criar um sistema de exibição de mensagens em html/adicionar_categoria.php
        }
    }
    public function listarTodos(){
        $nextPage = trim($_REQUEST['nextPage']);

        if(!filter_var($nextPage, FILTER_VALIDATE_URL)){
            http_response_code(400);
            exit('Erro, a URL informada para a próxima página não é válida.');
        }

        $categoriaDAO= new CategoriaDAO();
        $categorias = $categoriaDAO->listarTodos();
        session_start();
        $_SESSION['categoria']=$categorias;
        echo $_SESSION['categoria'];
        header('Location: '.$nextPage);
    }
    
    public function incluir(){
        $categoria = $this->verificar();
        $categoriaDAO = new CategoriaDAO();
        try{
            $categoriaDAO->incluir($categoria);
            header("Location: ". WWW ."html/matPat/adicionar_categoria.php");
        } catch (PDOException $e){
            echo "Não foi possível registrar a categoria";
        }
    }
    public function editar(){
        $id_categoria_produto = trim($_REQUEST['id_categoria_produto']);
        $descricao_categoria = trim($_REQUEST['descricao_categoria']);

        if(!$id_categoria_produto || !is_numeric($id_categoria_produto) || $id_categoria_produto < 1){
            http_response_code(400);
            exit('O id de uma categoria deve ser um inteiro maior ou igual a 1.');
        }

        if(!$descricao_categoria || empty($descricao_categoria)){
            http_response_code(400);
            exit('A descrição de uma categoria não pode ser vazia.');
        }

        $categoriaDAO = new CategoriaDAO();
        try{
            $categoriaDAO->editar($id_categoria_produto, $descricao_categoria);
            header("Location: ". WWW ."html/matPat/listar_categoria.php");
        } catch (PDOException $e){
            echo "Não foi possível editar a categoria";
        }
    }
    public function excluir(){
        $id_categoria_produto = trim($_REQUEST['id_categoria_produto']);

        if(!$id_categoria_produto || !is_numeric($id_categoria_produto) || $id_categoria_produto < 1){
            http_response_code(400);
            exit('O id de uma categoria deve ser um inteiro maior ou igual a 1.');
        }

        try {
            $categoriaDAO=new CategoriaDAO();
            $categoriaDAO->excluir($id_categoria_produto);
            header('Location: '. WWW .'html/matPat/listar_categoria.php');
        } catch (PDOException $e) {
            echo "Não foi possível excluir essa categoria, pois já deve existir um produto cadastrado com essa categoria";
        }
    }
}