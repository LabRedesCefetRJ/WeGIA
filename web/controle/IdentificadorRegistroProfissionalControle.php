<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'IdentificadorRegistroProfissionalDAO.php';

class IdentificadorRegistroProfissionalControle
{
    private IdentificadorRegistroProfissionalDAO $dao;

    public function __construct()
    {
        $this->dao = new IdentificadorRegistroProfissionalDAO();
    }

    public function listar(): void
    {
        try {
            $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
            if (!$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('O id do funcionário informado não é válido.');
            }

            $resultado = $this->dao->listarPorIdFuncionario($idFuncionario);
            $this->responderJson($resultado, 200);
        } catch (InvalidArgumentException $e) {
            $this->responderJson(['erro' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            error_log("Erro no servidor: " . $e->getMessage());
            $this->responderJson(['erro' => 'Erro no banco/servidor: ' . $e->getMessage()], 500);
        }
    }

    public function adicionar(): void
    {
        try {
            $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
            $idTipo = filter_input(INPUT_POST, 'id_tipo_registro', FILTER_SANITIZE_NUMBER_INT);
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
        } catch (InvalidArgumentException $e) {
            $this->responderJson(['erro' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            error_log("Erro no servidor: " . $e->getMessage());
            $this->responderJson(['erro' => 'Erro no banco/servidor: ' . $e->getMessage()], 500);
        }
    }

    public function editar(): void
    {
        try {
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
        } catch (InvalidArgumentException $e) {
            $this->responderJson(['erro' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            error_log("Erro no servidor: " . $e->getMessage());
            $this->responderJson(['erro' => 'Erro no banco/servidor: ' . $e->getMessage()], 500);
        }
    }

    public function remover(): void
    {
        try {
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
        } catch (InvalidArgumentException $e) {
            $this->responderJson(['erro' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            error_log("Erro no servidor: " . $e->getMessage());
            $this->responderJson(['erro' => 'Erro no banco/servidor: ' . $e->getMessage()], 500);
        }
    }

    private function responderJson(mixed $dados, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($dados);
        exit();
    }
}
?>