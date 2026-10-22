<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
include_once ROOT . "/dao/Conexao.php";
include_once ROOT . '/classes/Visitado.php';
include_once ROOT . '/dao/VisitadoDAO.php';
require_once ROOT . '/classes/Util.php';
require_once ROOT . '/controle/VisitanteControle.php';

class VisitadoControle
{
    public function adicionar()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $tipo = filter_input(INPUT_POST, 'tipo', FILTER_UNSAFE_RAW);
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

            if (!VisitadoDAO::tipoValido($tipo))
                throw new InvalidArgumentException('Tipo de visitado inválido.', 412);

            if (!$id || $id <= 0)
                throw new InvalidArgumentException('O registro informado é inválido.', 412);

            $visitadoDAO = new VisitadoDAO();
            $idVisitado = $visitadoDAO->incluir($tipo, $id);

            if (!isset($idVisitado))
                throw new PDOException('Erro ao cadastrar o visitado.', 500);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => true,
                'id_visitado' => (int) $idVisitado,
                'mensagem' => 'Visitado cadastrado com sucesso',
            ]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    private function obterTipoRequisicao(): string
    {
        $tipo = filter_input(INPUT_GET, 'tipo', FILTER_UNSAFE_RAW);

        return is_string($tipo) ? $tipo : '';
    }

    public function listarTodos()
    {
        try {
            $visitadoDAO = new VisitadoDAO();
            $visitados = $visitadoDAO->listarTodos($this->obterTipoRequisicao(), VisitanteControle::obterIdsSelecionados());

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($visitados, JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarCandidatos()
    {
        try {
            $visitadoDAO = new VisitadoDAO();
            $candidatos = $visitadoDAO->listarCandidatos($this->obterTipoRequisicao());

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($candidatos, JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}