<?php
include_once ROOT . '/classes/TipoSaida.php';
include_once ROOT . '/dao/TipoSaidaDAO.php';
class TipoSaidaControle
{
    public function verificar(){
        extract($_REQUEST);
        
        if((!isset($descricao)) || (empty($descricao))){
            $msg .= "Descrição do tipo de saida não informada. Por favor, informe uma descrição!";
            header('Location: ../html/tiposaida.html?msg='.$msg);
        }else{
        	$tiposaida = new TipoSaida($descricao);
        }
        return $tiposaida;
    }
    
    public function listarTodos(){
        extract($_REQUEST);
        $tiposaidaDAO= new TipoSaidaDAO();
        $tiposaida = $tiposaidaDAO->listarTodos();
        session_start();
        $_SESSION['tipo_saida']=$tiposaida;
        header('Location: ' .$nextPage);
    }
    
    public function incluir(){
        $tiposaida = $this->verificar();
        $tiposaidaDAO = new TipoSaidaDAO();
        try{
            $tiposaidaDAO->incluir($tiposaida);
            session_start();
            $_SESSION['msg']="Tipo de Saida cadastrado com sucesso";
            $_SESSION['proxima']="Cadastrar outro TipoSaida";
            $_SESSION['link']= WWW . "html/matPat/adicionar_tipoSaida.php";
            header("Location: ". WWW ."html/matPat/cadastro_saida.php");
        } catch (PDOException $e){
            $msg= "Não foi possível registrar o tipo"."<br>".$e->getMessage();
            echo $msg;
        }
    }
    
    public function excluir(){
        extract($_REQUEST);
        try {
            $tiposaidaDAO=new TipoSaidaDAO();
            $tiposaidaDAO->excluir($id_tipo);
            header('Location: '. WWW .'html/matPat/listar_tipoSaida.php');
        } catch (PDOException $e) {
            echo "ERROR: ".$e->getMessage();
        }
    }
}