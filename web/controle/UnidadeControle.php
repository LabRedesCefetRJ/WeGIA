<?php
include_once ROOT . '/classes/Unidade.php';
include_once ROOT .'/dao/UnidadeDAO.php';
class UnidadeControle
{
    public function verificar(){
        extract($_REQUEST);
        
        if((!isset($descricao_unidade)) || (empty($descricao_unidade))){
            $msg .= "Descrição da Unidade não informada. Por favor, informe uma descrição!";
            header('Location: ../html/unidade.html?msg='.$msg);
        }else{
        	$unidade = new Unidade($descricao_unidade);
        }
        return $unidade;
    }
    public function listarTodos(){
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));

        $regex = '#^(\.\./html/matPat/(alterar_produto|cadastro_produto|listar_unidade)\.php(\?id_produto=\d+)?)$#';

        $unidadeDAO = new UnidadeDAO();
        $unidades = $unidadeDAO->listarTodos();
        session_start();
        $_SESSION['unidade']=$unidades;

        if(preg_match($regex, $nextPage)){
            header('Location:' . htmlspecialchars($nextPage));
        }else{
            header('Location:' . '../html/home.php');
        }
    }
    
    public function incluir(){
        $unidade = $this->verificar();
        $unidadeDAO = new UnidadeDAO();
        try{
            $unidadeDAO->incluir($unidade);
            session_start();
            $_SESSION['msg']="Unidade cadastrado com sucesso";
            $_SESSION['proxima']="Cadastrar outra unidade";
            $_SESSION['link']= WWW ."html/matPat/adicionar_unidade.php";
            header("Location: ". WWW ."html/matPat/cadastro_produto.php");
        } catch (PDOException $e){
            $msg= "Não foi possível registrar o funcionário"."<br>".$e->getMessage();
            echo $msg;
        }
    }
    public function excluir(){
        extract($_REQUEST);
        try{
            $unidadeDAO = new UnidadeDAO();
            $unidadeDAO->excluir($id_unidade);
            header('Location: '. WWW .'html/matPat/listar_unidade.php');
        }catch (PDOException $e) {
            echo "ERROR: ".$e->getMessage();
        }
    }
}