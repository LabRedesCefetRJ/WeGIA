<?php
include_once ROOT . '/classes/Origem.php';
include_once ROOT . '/dao/OrigemDAO.php';
class OrigemControle
{
    public function verificar(){
        extract($_REQUEST);
        if((!isset($nome)) || (empty($nome))){
            $msg = "Nome do origem não informado. Por favor, informe um nome!";
            header('Location: ../html/origem.html?msg='.$msg);
        }
        if((!isset($cnpj)) || (empty($cnpj))){
            $calcado='null';
        }
        if((!isset($cpf)) || (empty($cpf))){
            $calca='null';
        }
        if((!isset($telefone)) || (empty($telefone))){
            $msg .= "Telefone do origem não informado. Por favor, informe um telefone!";
            header('Location: ../html/origem.html?msg='.$msg);
        }
        $cpf=str_replace(".", '', $cpf);
        $cpf=str_replace("-", "", $cpf);
        $origem = new Origem($nome,$cnpj,$cpf,$telefone);
        $origem->setNome($nome);
        $origem->setCnpj($cnpj);
        $origem->setCpf($cpf);
        $origem->setTelefone($telefone);
        
        return $origem;
    }
    
    public function listarTodos(){
        extract($_REQUEST);
        $origemDAO= new OrigemDAO();
        $origens = $origemDAO->listarTodos();
        session_start();
        $_SESSION['origem']=$origens;
        header('Location:' .$nextPage);
    }

    public function listarId_Nome(){
        extract($_REQUEST);
        $origemDAO= new OrigemDAO();
        $origens = $origemDAO->listarId_Nome();
        session_start();
        $_SESSION['origem']=$origens;
        header('Location: ' . $nextPage);
    }
    
    public function incluir(){
        $origem = $this->verificar();
        $origemDAO = new OrigemDAO();
        try{
            $origemDAO->incluir($origem);
            session_start();
            $_SESSION['msg']="origem cadastrado com sucesso";
            $_SESSION['proxima']="Cadastrar outra Origem";
            $_SESSION['link']= WWW ."html/matPat/cadastro_doador.php";
            header("Location: ". WWW ."html/matPat/cadastro_doador.php");
        } catch (PDOException $e){
            $msg= "Não foi possível registrar o tipo"."<br>".$e->getMessage();
            echo $msg;
        }
    }
    public function excluir(){
        extract($_REQUEST);
        try {
            $origemDAO=new OrigemDAO();
            $origemDAO->excluir($id_origem);
            header('Location:' . WWW . 'html/matPat/listar_origem.php');
        } catch (PDOException $e) {
            echo "ERROR: ".$e->getMessage();
        }
    }
}