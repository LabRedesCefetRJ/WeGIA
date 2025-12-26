<?php
include_once '../classes/Situacao.php';
include_once '../dao/SituacaoDAO.php';
include_once '../classes/util.php';

class SituacaoControle
{
    public function verificar(){
        //extract($_REQUEST);
        $situacoes = trim($_REQUEST['situacoes']);

        if((!isset($situacoes)) || (empty($situacoes))){
            $msg = "Descricao da Situacão não informada. Por favor, informe uma descrição!";
            header('Location: ../html/situacao.html?msg='.$msg);
        }else{
            $situacao = new Situacao($situacoes);
        }
        return $situacao;
    }
    public function listarTodos(){
        $situacoes = trim($_REQUEST['situacoes']);
        $nextPage = trim($_REQUEST['nextPage']);

        if(!$situacoes || empty($situacoes)){
            http_response_code(400);
            exit('A descrição da situação não pode ser vazia.');
        }

        if(!filter_var($nextPage, FILTER_VALIDATE_URL)){
            http_response_code(400);
            exit('Erro, a URL informada para a próxima página não é válida.');
        }
        try{
            $situacaoDAO= new SituacaoDAO();
            $situacaos = $situacaoDAO->listarTodos();
            session_start();
            $_SESSION['situacao']=$situacaos;
            header('Location: '.$nextPage);
        }catch(PDOException $e){
            Util::tratarException($e);
        }

    }
    public function incluir(){
        $situacao = $this->verificar();
        $situacaoDAO = new SituacaoDAO();
        try{
            $situacaoDAO->incluir($situacao);
            session_start();
            $_SESSION['msg']="Almoxarifado cadastrado com sucesso";
            $_SESSION['proxima']="Cadastrar outro almoxarifado";
            $_SESSION['link']="../html/adicionar_situacao.php";
            header("Location: ../html/adicionar_situacao.php");
        } catch (PDOException $e){
            $msg= "Não foi possível registrar a situacao"."<br>" . Util::tratarException($e);
            echo $msg;
        }
    }
    public function excluir(){
        $id_situacao = trim($_REQUEST['id_situacao']);

        if(!$id_situacao || !is_numeric($id_situacao) || $id_situacao < 1){
            http_response_code(400);
            exit('O id de uma situação deve ser um número inteiro maior ou igual a 1.');
        }

        try {
            $situacaoDAO=new SituacaoDAO();
            $situacaoDAO->excluir($id_situacao);
            header('Location:../html/listar_calca.php');
        } catch (PDOException $e) {
            Util::tratarException($e);
        }
    }
    
}
    