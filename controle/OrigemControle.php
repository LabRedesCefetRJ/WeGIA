<?php
include_once ROOT . '/classes/Origem.php';
include_once ROOT . '/dao/OrigemDAO.php';
require_once ROOT . '/html/contribuicao/helper/Util.php';

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

        if (empty($telefone)) {
            $msg = urlencode("Telefone da origem não informado. Por favor, informe um telefone!");
            header('Location: ../html/origem.html?msg=' . $msg);
            exit;
        }

        // Validação de CPF e CNPJ
        if (strlen($cpf) > 0 && !Util::validarCPF($cpf)) {
            $msg = urlencode("CPF inválido!");
            header('Location: ../html/origem.html?msg=' . $msg);
            exit;
        }

        if (strlen($cnpj) > 0 && (!Util::validaCnpj($cnpj))) {
            $msg = urlencode("CNPJ inválido!");
            header('Location: ../html/origem.html?msg=' . $msg);
            exit;
        }

        // Criação do objeto de forma segura
        $cpf = strlen($cpf) > 0 ? $cpf : null;
        $origem = new Origem($nome, $cnpj, $cpf, $telefone);

        return $origem;
    }

    public function listarTodos()
    {
        $nextPage = isset($_REQUEST['nextPage']) ? $_REQUEST['nextPage'] : '../html/origem.html';
        $origemDAO = new OrigemDAO();

        try {
            $origens = $origemDAO->listarTodos();
            session_start();
            $_SESSION['origem'] = $origens;

            // Escapa a URL antes de redirecionar
            header('Location: ' . htmlspecialchars($nextPage, ENT_QUOTES, 'UTF-8'));
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao listar origens: " . $e->getMessage());
            echo "Erro ao listar origens. Tente novamente mais tarde.";
        }
    }

    public function listarId_Nome()
    {
        $nextPage = isset($_REQUEST['nextPage']) ? $_REQUEST['nextPage'] : '../html/origem.html';
        $origemDAO = new OrigemDAO();

        try {
            $origens = $origemDAO->listarId_Nome();
            session_start();
            $_SESSION['origem'] = $origens;
            header('Location: ' . htmlspecialchars($nextPage, ENT_QUOTES, 'UTF-8'));
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
            $origemDAO = new OrigemDAO();
            $origemDAO->incluir($origem);

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
}
