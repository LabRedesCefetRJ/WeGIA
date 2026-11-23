<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
include_once ROOT . "/dao/Conexao.php";
include_once ROOT . '/classes/Visitado.php';
include_once ROOT . '/dao/VisitadoDAO.php';
require_once ROOT . '/classes/Util.php';
require_once ROOT . '/html/permissao/permissao.php';
require_once ROOT . '/classes/FichaRecepcao.php';
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

    /**
     * Ficha do visitado (JSON) exibida no histórico de visitas. Setor e instituição não têm ficha.
     */
    public function detalhar()
    {
        try {
            permissao($_SESSION['id_pessoa'], 20, 5);

            $idVisitado = filter_input(INPUT_GET, 'id_visitado', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$idVisitado)
                throw new InvalidArgumentException('O visitado informado é inválido.', 412);

            $detalhes = (new VisitadoDAO())->buscarDetalhes($idVisitado);
            if ($detalhes === null)
                throw new InvalidArgumentException('Visitado não encontrado.', 404);

            switch ($detalhes['categoria']) {
                case 'atendido':
                    $ficha = FichaRecepcao::atendido($detalhes['dados']);
                    break;
                case 'funcionario':
                    $ficha = FichaRecepcao::colaborador($detalhes['dados'], 'Funcionário');
                    break;
                case 'voluntario':
                    $ficha = FichaRecepcao::colaborador($detalhes['dados'], 'Voluntário');
                    break;
                case 'pessoa':
                    $ficha = FichaRecepcao::pessoa($detalhes['dados']);
                    break;
                case 'pet':
                    $ficha = FichaRecepcao::pet($detalhes['dados']);
                    break;
                default:
                    throw new InvalidArgumentException('Este visitado não possui ficha.', 404);
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($ficha, JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
