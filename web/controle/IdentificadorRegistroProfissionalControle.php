<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'IdentificadorRegistroProfissionalDAO.php';

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

class IdentificadorRegistroProfissionalControle
{
    private IdentificadorRegistroProfissionalDAO $dao;

    public function __construct()
    {
        $this->iniciarSessao();
        $this->validarAutenticacao();
        // $this->validarPermissao(); // Descomente caso as permissões (11, 7) estejam configuradas na tabela
        $this->dao = new IdentificadorRegistroProfissionalDAO();
    }

    private function iniciarSessao(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function validarAutenticacao(): void
    {
        if (!isset($_SESSION['usuario'])) {
            $this->responderJson(['erro' => 'Sessão expirada. Faça login novamente.'], 401);
        }
    }

    private function validarPermissao(): void
    {
        if (function_exists('permissao')) {
            $temPermissao = permissao($_SESSION['id_pessoa'], 11, 7);
            if ($temPermissao === false) {
                $this->responderJson(['erro' => 'Acesso negado. Permissão insuficiente.'], 403);
            }
        }
    }

    public function processarRequisicao(): void
    {
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            switch ($action) {
                case 'listar':
                    $this->listar();
                    break;

                case 'adicionar':
                    $this->adicionar();
                    break;

                case 'editar':
                    $this->editar();
                    break;

                case 'remover':
                    $this->remover();
                    break;

                default:
                    $this->responderJson(['erro' => 'Ação não reconhecida ou ausente: ' . $action], 400);
                    break;
            }
        } catch (InvalidArgumentException $e) {
            $this->responderJson(['erro' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            error_log("Erro no servidor: " . $e->getMessage());
            $this->responderJson(['erro' => 'Erro no banco/servidor: ' . $e->getMessage()], 500);
        }
    }

    private function listar(): void
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
        if (!$idFuncionario || $idFuncionario < 1) {
            throw new InvalidArgumentException('O id do funcionário informado não é válido.');
        }

        $resultado = $this->dao->listarPorIdFuncionario($idFuncionario);
        $this->responderJson($resultado, 200);
    }

    private function adicionar(): void
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
        $idTipo = filter_input(INPUT_POST, 'id_tipo', FILTER_SANITIZE_NUMBER_INT);
        $numeroRegistro = trim((string) filter_input(INPUT_POST, 'numero_registro', FILTER_SANITIZE_SPECIAL_CHARS));
        $uf = filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_SPECIAL_CHARS);
        $uf = ($uf === '' || $uf === null) ? null : $uf;

        if (!$idFuncionario || $idFuncionario < 1) {
            throw new InvalidArgumentException('O id do funcionário informado não é válido.');
        }
        if (!$idTipo || $idTipo < 1) {
            throw new InvalidArgumentException('Selecione um tipo de registro profissional válido.');
        }
        if ($numeroRegistro === '') {
            throw new InvalidArgumentException('Informe o número do registro.');
        }

        $this->dao->salvarRegistroProfissional($idFuncionario, $idTipo, $numeroRegistro, $uf);
        $resultado = $this->dao->listarPorIdFuncionario($idFuncionario);

        $this->responderJson($resultado, 201);
    }

    private function editar(): void
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
        $idRegistro = filter_input(INPUT_POST, 'id_registro', FILTER_SANITIZE_NUMBER_INT);
        $numeroRegistro = trim((string) filter_input(INPUT_POST, 'numero_registro', FILTER_SANITIZE_SPECIAL_CHARS));
        $uf = filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_SPECIAL_CHARS);
        $uf = ($uf === '' || $uf === null) ? null : $uf;

        if (!$idFuncionario || $idFuncionario < 1) {
            throw new InvalidArgumentException('O id do funcionário informado não é válido.');
        }
        if (!$idRegistro || $idRegistro < 1) {
            throw new InvalidArgumentException('O id do registro informado não é válido.');
        }
        if ($numeroRegistro === '') {
            throw new InvalidArgumentException('Informe o número do registro.');
        }

        $this->dao->alterarRegistroPorId($idRegistro, $idFuncionario, $numeroRegistro, $uf);
        $resultado = $this->dao->listarPorIdFuncionario($idFuncionario);

        $this->responderJson($resultado, 200);
    }

    private function remover(): void
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
        $idRegistro = filter_input(INPUT_POST, 'id_registro', FILTER_SANITIZE_NUMBER_INT);

        if (!$idFuncionario || $idFuncionario < 1) {
            throw new InvalidArgumentException('O id do funcionário informado não é válido.');
        }
        if (!$idRegistro || $idRegistro < 1) {
            throw new InvalidArgumentException('O id do registro informado não é válido.');
        }

        $this->dao->remover($idRegistro, $idFuncionario);
        $resultado = $this->dao->listarPorIdFuncionario($idFuncionario);

        $this->responderJson($resultado, 200);
    }

    private function responderJson(mixed $dados, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($dados);
        exit();
    }
}
/*
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'IdentificadorRegistroProfissionalDAO.php';

require_once "../permissao/permissao.php";

class IdentificadorRegistroProfissionalControle
{
    private IdentificadorRegistroProfissionalDAO $dao;

    public function __construct()
    {
        $this->iniciarSessao();
        $this->validarAutenticacao();
        //$this->validarPermissao();
        $this->dao = new IdentificadorRegistroProfissionalDAO();
    }

    private function iniciarSessao(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function validarAutenticacao(): void
    {
        if (!isset($_SESSION['usuario'])) {
            $this->responderJson(['erro' => 'Sessão expirada. Faça login novamente.'], 401);
        }
    }

    private function validarPermissao(): void
    {
        if (function_exists('permissao')) {
            $temPermissao = permissao($_SESSION['id_pessoa'], 11, 7);
            if ($temPermissao === false) {
                $this->responderJson(['erro' => 'Acesso negado. Permissão insuficiente.'], 403);
            }
        }
    }

    public function processarRequisicao(): void
    {
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            switch ($action) {
                case 'listar':
                    $this->listar();
                    break;

                case 'adicionar':
                    $this->adicionar();
                    break;

                case 'editar':
                    $this->editar();
                    break;

                case 'remover':
                    $this->remover();
                    break;

                default:
                    $this->responderJson(['erro' => 'Ação não reconhecida ou ausente.'], 400);
                    break;
            }
        } catch (InvalidArgumentException $e) {
            error_log("erro " . $e->getMessage());
            $this->responderJson(['erro' => $e->getMessage()], 400);
        } catch (Exception $e) {
            error_log("Erro interno no servidor " . $e->getMessage());
            $this->responderJson(['erro' => 'Erro interno no servidor: '. $e->getMessage()], 500);
        }
    }

    private function listar(): void
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
        if (!$idFuncionario || $idFuncionario < 1) {
            throw new InvalidArgumentException('O id do funcionário informado não é válido.');
        }

        $resultado = $this->dao->listarPorIdFuncionario($idFuncionario);
        $this->responderJson($resultado, 200);
    }

    private function adicionar(): void
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
        $idTipo = filter_input(INPUT_POST, 'id_tipo', FILTER_SANITIZE_NUMBER_INT);
        $numeroRegistro = trim((string) filter_input(INPUT_POST, 'numero_registro', FILTER_SANITIZE_SPECIAL_CHARS));
        $uf = filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_SPECIAL_CHARS);
        $uf = ($uf === '' || $uf === null) ? null : $uf;

        if (!$idFuncionario || $idFuncionario < 1) {
            throw new InvalidArgumentException('O id do funcionário informado não é válido.');
        }
        if (!$idTipo || $idTipo < 1) {
            throw new InvalidArgumentException('Selecione um tipo de registro profissional válido.');
        }
        if ($numeroRegistro === '') {
            throw new InvalidArgumentException('Informe o número do registro.');
        }

        $this->dao->salvarRegistroProfissional($idFuncionario, $idTipo, $numeroRegistro, $uf);
        $resultado = $this->dao->listarPorIdFuncionario($idFuncionario);

        $this->responderJson($resultado, 201);
    }

    private function editar(): void
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
        $idRegistro = filter_input(INPUT_POST, 'id_registro', FILTER_SANITIZE_NUMBER_INT);
        $numeroRegistro = trim((string) filter_input(INPUT_POST, 'numero_registro', FILTER_SANITIZE_SPECIAL_CHARS));
        $uf = filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_SPECIAL_CHARS);
        $uf = ($uf === '' || $uf === null) ? null : $uf;

        if (!$idFuncionario || $idFuncionario < 1) {
            throw new InvalidArgumentException('O id do funcionário informado não é válido.');
        }
        if (!$idRegistro || $idRegistro < 1) {
            throw new InvalidArgumentException('O id do registro informado não é válido.');
        }
        if ($numeroRegistro === '') {
            throw new InvalidArgumentException('Informe o número do registro.');
        }

        $this->dao->alterarRegistroPorId($idRegistro, $idFuncionario, $numeroRegistro, $uf);
        $resultado = $this->dao->listarPorIdFuncionario($idFuncionario);

        $this->responderJson($resultado, 200);
    }

    private function remover(): void
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
        $idRegistro = filter_input(INPUT_POST, 'id_registro', FILTER_SANITIZE_NUMBER_INT);

        if (!$idFuncionario || $idFuncionario < 1) {
            throw new InvalidArgumentException('O id do funcionário informado não é válido.');
        }
        if (!$idRegistro || $idRegistro < 1) {
            throw new InvalidArgumentException('O id do registro informado não é válido.');
        }

        $this->dao->remover($idRegistro, $idFuncionario);
        $resultado = $this->dao->listarPorIdFuncionario($idFuncionario);

        $this->responderJson($resultado, 200);
    }

    private function responderJson(mixed $dados, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($dados);
        exit();
    }
*/
?>