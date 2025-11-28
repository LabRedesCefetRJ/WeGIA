<?php
include_once ROOT . '/classes/Destino.php';
include_once ROOT . '/dao/DestinoDAO.php';
class DestinoControle
{
    public function verificar(){
        extract($_REQUEST);
        if((!isset($nome)) || (empty($nome))){
            $msg = "Nome do destino nÃ£o informado. Por favor, informe um nome!";
            header('Location: '. WWW .'html/destino.html?msg='.$msg);
        }
        if((!isset($cnpj)) || (empty($cnpj))){
            $cnpj='';
        }
        if((!isset($cpf)) || (empty($cpf))){
            $cpf='';
        }
        if((!isset($telefone)) || (empty($telefone))){
            $msg .= "Telefone do destino não informado. Por favor, informe um telefone!";
            header('Location: ../html/destino.html?msg='.$msg);
        }
   
        $destino = new Destino($nome,$cnpj,$cpf,$telefone);
        $destino->setNome($nome);
        $destino->setCnpj($cnpj);
        $destino->setCpf($cpf);
        $destino->setTelefone($telefone);
        
        return $destino;
    }
    
    public function listarTodos(){
        extract($_REQUEST);
        $destinoDAO= new DestinoDAO();
        $destinos = $destinoDAO->listarTodos();
        session_start();
        $_SESSION['destino']=$destinos;
        header('Location: '.$nextPage);
    }
    
    public function incluir(){
        $destino = $this->verificar();
        $destinoDAO = new DestinoDAO();
        try{
            $destinoDAO->incluir($destino);
            session_start();
            $_SESSION['msg']="Destino cadastrado com sucesso";
            $_SESSION['proxima']="Cadastrar outro Destino";
            $_SESSION['link']= WWW . "html/matPat/cadastro_destino.php";
            header("Location: ". WWW ."html/matPat/cadastro_destino.php");
        } catch (PDOException $e){
            $msg= "Não foi possível registrar o tipo"."<br>".$e->getMessage();
            echo $msg;
        }
    }
    public function excluir(){
        extract($_REQUEST);
        try {
            $destinoDAO=new DestinoDAO();
            $destinoDAO->excluir($id_destino);
            header('Location: '. WWW .'html/matPat/listar_destino.php');
        } catch (PDOException $e) {
            echo "ERROR: ".$e->getMessage();
        }
    }
}