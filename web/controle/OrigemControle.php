<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

include_once ROOT . '/classes/Origem.php';
include_once ROOT . '/dao/OrigemDAO.php';
require_once ROOT . '/classes/Util.php';

class OrigemControle
{
    /**
     * Valida e sanitiza os dados de entrada antes de criar o objeto Origem.
     */
    public function verificar()
    {
        // Em vez de extract(), acessar diretamente e sanitizar
        $nome     = isset($_REQUEST['nome']) ? trim($_REQUEST['nome']) : '';
        $telefone = isset($_REQUEST['telefone']) ? trim($_REQUEST['telefone']) : '';
        $cpf      = isset($_REQUEST['cpf']) ? trim($_REQUEST['cpf']) : '';
        $cnpj     = isset($_REQUEST['cnpj']) ? trim($_REQUEST['cnpj']) : '';

        // Validação de campos obrigatórios
        if (empty($nome)) {
            $msg = urlencode("Nome da origem não informado. Por favor, informe um nome!");
            header('Location: ../html/origem.html?msg=' . $msg);
            exit;
        }

        if ($cpf !== '' && !Util::validarCPF($cpf)) {
            $_SESSION['msg'] = "CPF inválido!";
            header('Location: ' . WWW . 'html/matPat/cadastro_doador.php');
            exit;
        }

        if ($cnpj !== '' && !Util::validaCnpj($cnpj)) {
            $_SESSION['msg'] = "CNPJ inválido!";
            header('Location: ' . WWW . 'html/matPat/cadastro_doador.php');
            exit;
        }

        $cpf = $cpf !== '' ? $cpf : null;
        $cnpj = $cnpj !== '' ? $cnpj : null;
        $telefone = $telefone !== '' ? $telefone : null;

        return new Origem($nome, $cnpj, $cpf, $telefone);
    }

    public function listarTodos()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(listar_origem)\.php)$#';

        try {
            $origemDAO = new OrigemDAO();
            $origens = $origemDAO->listarTodos();

            $_SESSION['origem'] = $origens;

            preg_match($regex, $nextPage) ? header('Location: ' . htmlspecialchars($nextPage, ENT_QUOTES, 'UTF-8')) : header('Location: ' . WWW . 'html/home.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao listar origens: " . $e->getMessage());
            echo "Erro ao listar origens. Tente novamente mais tarde.";
        }
    }

    public function listarId_Nome()
    {

        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(cadastro_entrada)\.php)$#';

        try {
            $origemDAO = new OrigemDAO();
            $origens = $origemDAO->listarId_Nome();

            $_SESSION['origem'] = $origens;

            preg_match($regex, $nextPage) ? header('Location: ' . htmlspecialchars($nextPage, ENT_QUOTES, 'UTF-8')) : header('Location: ' . WWW . 'html/home.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao listar ID/Nome: " . $e->getMessage());
            echo "Erro ao listar origens. Tente novamente mais tarde.";
        }
    }

    public function incluir()
    {
        try {
            $origem = $this->verificar();

            $almoxarifados = isset($_POST['almoxarifados']) && is_array($_POST['almoxarifados'])
            ? array_map('intval', $_POST['almoxarifados'])
            : array();


            $origemDAO = new OrigemDAO();

            $id_origem = $origemDAO->incluir($origem);

            $origemDAO->atualizarAlmoxarifados($id_origem, $almoxarifados);

            session_start();
            $_SESSION['msg'] = "Origem cadastrada com sucesso";
            $_SESSION['proxima'] = "Cadastrar outra Origem";
            $_SESSION['link'] = WWW . "html/matPat/cadastro_doador.php";

            header("Location: " . WWW . "html/matPat/cadastro_doador.php");
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao incluir origem: " . $e->getMessage());
            echo "Erro ao cadastrar origem. Tente novamente mais tarde.";
        } catch (Exception $e) {
            error_log("Erro geral: " . $e->getMessage() . 'Line ' . $e->getLine() . 'File ' . $e->getFile());
            echo "Erro inesperado. Contate o administrador do sistema.";
        }
    }

    public function excluir()
    {
        $id_origem = isset($_REQUEST['id_origem']) ? (int) $_REQUEST['id_origem'] : 0;

        if ($id_origem <= 0) {
            echo "ID de origem inválido.";
            return;
        }

        try {
            $origemDAO = new OrigemDAO();
            $origemDAO->excluir($id_origem);
            header('Location:' . WWW . 'html/matPat/listar_origem.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao excluir origem: " . $e->getMessage());
            echo "Erro ao excluir origem. Tente novamente mais tarde.";
        }
    }

    public function listarPorAlmoxarifado()
    {
        $id_almoxarifado = isset($_GET['id_almoxarifado'])
            ? (int) $_GET['id_almoxarifado']
            : 0;

        header('Content-Type: application/json; charset=utf-8');

        if ($id_almoxarifado <= 0) {
            echo json_encode(array());
            exit;
        }

        $origemDAO = new OrigemDAO();
        echo $origemDAO->listarPorAlmoxarifado($id_almoxarifado);
        exit;
    }

    public function alterar()
    {
        try {
            $id_origem = isset($_POST['id_origem']) ? (int) $_POST['id_origem'] : 0;
            $almoxarifados = isset($_POST['almoxarifados']) && is_array($_POST['almoxarifados'])
                ? $_POST['almoxarifados']
                : array();

            if ($id_origem <= 0) {
                throw new Exception("Origem inválida.");
            }

            $origem = $this->verificar();
            $origem->setId_origem($id_origem);

            $origemDAO = new OrigemDAO();
            $origemDAO->alterar($origem);
            $origemDAO->atualizarAlmoxarifados($id_origem, $almoxarifados);

            $_SESSION['msg'] = "Origem alterada com sucesso.";
            header('Location: ' . WWW . 'html/matPat/listar_origem.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao alterar origem: " . $e->getMessage());
            echo "Erro ao alterar origem. Tente novamente mais tarde.";
        } catch (Exception $e) {
            error_log("Erro ao alterar origem: " . $e->getMessage());
            echo "Erro ao alterar origem.";
        }   
    }
}
