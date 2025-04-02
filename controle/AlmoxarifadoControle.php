<?php
include_once '../classes/Almoxarifado.php';
include_once '../dao/AlmoxarifadoDAO.php';
class AlmoxarifadoControle
{
    public function verificar()
    {
        $descricao_almoxarifado= trim($_POST['descricao_almoxarifado']);
        try {
            $almoxarifado = new Almoxarifado($descricao_almoxarifado);
            return $almoxarifado;
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            exit('Erro ao verificar almoxarifado: ' . $e->getMessage());
        }
    }
    public function listarTodos()
    {
        $nextPage = trim($_GET['nextPage']);

        if(!filter_var($nextPage, FILTER_VALIDATE_URL)){
            http_response_code(400);
            exit('Erro, a URL informada para a próxima página não é válida.');
        }

        $almoxarifadoDAO = new AlmoxarifadoDAO();
        $almoxarifados = $almoxarifadoDAO->listarTodos();
        session_start();
        $_SESSION['almoxarifado'] = $almoxarifados;
        header('Location: ' . $nextPage);
    }
    public function incluir()
    {
        $almoxarifado = $this->verificar();
        $almoxarifadoDAO = new AlmoxarifadoDAO();
        try {
            $almoxarifadoDAO->incluir($almoxarifado);
            session_start();
            $_SESSION['msg'] = "Almoxarifado cadastrado com sucesso";
            $_SESSION['proxima'] = "Cadastrar outro almoxarifado";
            $_SESSION['link'] = WWW ."html/matPat/adicionar_almoxarifado.php";
            header("Location: " . WWW ."html/matPat/adicionar_almoxarifado.php");
        } catch (PDOException $e) {
            echo "Não foi possível registrar o almoxarifado";
        }
    }
    public function excluir()
    {
        $id_almoxarifado = trim($_GET['id_almoxarifado']);

        if(!$id_almoxarifado || !is_numeric($id_almoxarifado) || $id_almoxarifado < 1){
            http_response_code(400);
            exit("O id de um almoxarifado deve ser um inteiro maior ou igual a 1");
        }

        try {
            $almoxarifadoDAO = new AlmoxarifadoDAO();
            $almoxarifadoDAO->excluir($id_almoxarifado);
            header('Location: '. WWW .'html/matPat/listar_almox.php');
        } catch (PDOException $e) {
            echo "Não foi possível excluir o almoxarifado";
        }
    }
}
