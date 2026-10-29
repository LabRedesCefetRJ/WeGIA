<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

include_once ROOT . '/classes/Origem.php';
include_once ROOT . '/dao/OrigemDAO.php';
require_once ROOT . '/classes/Util.php';
require_once ROOT . '/service/PermissaoFornecedorService.php';
require_once ROOT . '/classes/OrigemNavegacao.php';
require_once ROOT . '/classes/Csrf.php';

class OrigemControle
{
    private function exigirPermissao(int $acao): void
    {
        $servico = new PermissaoFornecedorService();
        if (!$servico->permite((int) ($_SESSION['id_pessoa'] ?? 0), $acao)) {
            header('Location: ' . WWW . 'html/home.php?msg_c=' . urlencode(
                'Você não tem permissão para realizar esta ação em fornecedores.'
            ));
            exit;
        }
    }

    /**
     * Valida e sanitiza os dados de entrada antes de criar o objeto Origem.
     */
    public function verificar()
    {
        $this->exigirPermissao(3);
        // Em vez de extract(), acessar diretamente e sanitizar
        $nome     = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
        $cpf      = isset($_POST['cpf']) ? trim($_POST['cpf']) : '';
        $cnpj     = isset($_POST['cnpj']) ? trim($_POST['cnpj']) : '';

        // Validação de campos obrigatórios
        if (empty($nome)) {
            throw new InvalidArgumentException('Nome da origem não informado. Por favor, informe um nome!');
        }

        if ($cpf !== '' && !Util::validarCPF($cpf)) {
            throw new InvalidArgumentException('CPF inválido!');
        }

        if ($cnpj !== '' && !Util::validaCnpj($cnpj)) {
            throw new InvalidArgumentException('CNPJ inválido!');
        }

        $cpf = $cpf !== '' ? $cpf : null;
        $cnpj = $cnpj !== '' ? $cnpj : null;
        $telefone = $telefone !== '' ? $telefone : null;

        return new Origem($nome, $cnpj, $cpf, $telefone);
    }

    private function responderJson(array $dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function validarCsrf(): void
    {
        if (!Csrf::validateToken($_POST['csrf_token'] ?? null)) {
            throw new InvalidArgumentException(
                'Token CSRF inválido ou ausente.',
                403
            );
        }
    }

    public function listarTodos()
    {
        $this->exigirPermissao(5);
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
        $this->exigirPermissao(3);

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
        $this->exigirPermissao(3);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            throw new InvalidArgumentException(
                'O cadastro exige uma requisição POST.',
                405
            );
        }

        $this->validarCsrf();

        try {
            $origem = $this->verificar();

            $almoxarifados = isset($_POST['almoxarifados']) && is_array($_POST['almoxarifados'])
            ? array_map('intval', $_POST['almoxarifados'])
            : array();

            $origemDAO = new OrigemDAO();

            $id_origem = $origemDAO->incluir($origem);

            $origemDAO->atualizarAlmoxarifados($id_origem, $almoxarifados);

            $_SESSION['msg'] = "Origem cadastrada com sucesso";
            $_SESSION['flag'] = 'success';
            unset($_SESSION['origem'], $_SESSION['proxima'], $_SESSION['link']);

            header('Location: ' . OrigemNavegacao::cadastro($_POST['origem_pagina'] ?? null));
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao incluir origem: " . $e->getMessage());
            echo "Erro ao cadastrar origem. Tente novamente mais tarde.";
        } catch (InvalidArgumentException $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['flag'] = 'error';

            header('Location: ' . OrigemNavegacao::cadastro($_POST['origem_pagina'] ?? null));
            exit;

        } catch (Exception $e) {
            error_log("Erro geral: " . $e->getMessage() . 'Line ' . $e->getLine() . 'File ' . $e->getFile());
            echo "Erro inesperado. Contate o administrador do sistema.";
        }
    }

    public function excluir()
    {
        $this->exigirPermissao(3);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            throw new InvalidArgumentException(
                'A exclusão exige uma requisição POST.',
                405
            );
        }

        $this->validarCsrf();

        $id_origem = isset($_POST['id_origem']) ? (int) $_POST['id_origem'] : 0;

        if ($id_origem <= 0) {
            throw new InvalidArgumentException(
                'ID de origem inválido.',
                400
            );
        }

        try {
            $origemDAO = new OrigemDAO();
            $origemDAO->excluir($id_origem);
        } catch (DomainException $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['flag'] = 'error';
        } catch (PDOException $e) {
            error_log("Erro ao excluir origem: " . $e->getMessage());
            $_SESSION['msg'] = 'Não foi possível excluir este fornecedor. Verifique se existem outros registros vinculados a ele e tente novamente.';
            $_SESSION['flag'] = 'error';
        }

        unset($_SESSION['origem']);
        header('Location: ' . WWW . 'html/matPat/listar_origem.php');
        exit;
    }

    public function listarPorAlmoxarifado()
    {
        $this->exigirPermissao(3);
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
        $this->exigirPermissao(3);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            throw new InvalidArgumentException(
                'A alteração exige uma requisição POST.',
                405
            );
        }

        $this->validarCsrf();

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

            if (($_POST['resposta'] ?? '') === 'json') {
                $this->responderJson(['destino' => WWW . 'html/matPat/listar_origem.php']);
            }

            $_SESSION['msg'] = "Origem alterada com sucesso.";
            header('Location: ' . WWW . 'html/matPat/listar_origem.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao alterar origem: " . $e->getMessage());
            if (($_POST['resposta'] ?? '') === 'json') {
                $this->responderJson(['mensagem' => 'Erro ao alterar origem. Tente novamente mais tarde.'], 500);
            }
            echo "Erro ao alterar origem. Tente novamente mais tarde.";
        } catch (Exception $e) {
            error_log("Erro ao alterar origem: " . $e->getMessage());
            if (($_POST['resposta'] ?? '') === 'json') {
                $this->responderJson(['mensagem' => $e->getMessage()], 400);
            }
            echo "Erro ao alterar origem.";
        }
    }
}
