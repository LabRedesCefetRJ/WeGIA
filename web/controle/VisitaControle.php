<?php
if (session_status() === PHP_SESSION_NONE)
session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
include_once ROOT . "/dao/Conexao.php";
include_once ROOT . '/classes/Visita.php';
include_once ROOT . '/dao/VisitaDAO.php';
include_once ROOT . '/dao/VisitadoDAO.php';
require_once ROOT . '/classes/Util.php';

class VisitaControle
{
    public function verificarVisita()
    {
        $idsVisitanteBrutos = $_POST['idVisitante'] ?? null;

        if ($idsVisitanteBrutos === null || $idsVisitanteBrutos === '' || $idsVisitanteBrutos === []) {
            http_response_code(412);
            header('Location: ../html/recepcao/pre_registro_entrada.php?msg_e=' . urlencode('Selecione ao menos um visitante.'));
            exit();
        }

        $idsVisitanteBrutos = is_array($idsVisitanteBrutos) ? $idsVisitanteBrutos : [$idsVisitanteBrutos];

        $idsVisitante = [];
        foreach ($idsVisitanteBrutos as $idVisitante) {
            if (!filter_var($idVisitante, FILTER_VALIDATE_INT) || $idVisitante <= 0) {
                http_response_code(412);
                header('Location: ../html/recepcao/registro_entrada.php?msg=Visitante inválido.');
                exit();
            }
            $idsVisitante[] = (int) $idVisitante;
        }

        $idsVisitante = array_values(array_unique($idsVisitante));

        $idsVisitadoBrutos = $_POST['idVisitado'] ?? [];

        if ($idsVisitadoBrutos === null || $idsVisitadoBrutos === '') {
            $idsVisitadoBrutos = [];
        }

        $idsVisitadoBrutos = is_array($idsVisitadoBrutos) ? $idsVisitadoBrutos : [$idsVisitadoBrutos];

        $idsVisitado = [];
        foreach ($idsVisitadoBrutos as $idVisitado) {
            if (!filter_var($idVisitado, FILTER_VALIDATE_INT) || $idVisitado <= 0) {
                http_response_code(412);
                header('Location: ../html/recepcao/registro_entrada.php?msg=Visitado inválido.');
                exit();
            }
            $idsVisitado[] = (int) $idVisitado;
        }

        $idsVisitado = array_values(array_unique($idsVisitado));

        $descricaoBruta = filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW);
        $descricao = $descricaoBruta !== false && $descricaoBruta !== null ? trim($descricaoBruta) : '';

        if ($descricao !== '' && mb_strlen($descricao) > 255) {
            http_response_code(412);
            header('Location: ../html/recepcao/registro_entrada.php?msg=A descrição deve ter no máximo 255 caracteres.');
            exit();
        }

        return [
            'idsVisitante' => $idsVisitante,
            'idsVisitado' => $idsVisitado,
            'descricao' => $descricao,
        ];
    }

    public function verificarIdsVisita()
    {
        $idsBrutos = $_POST['idVisita'] ?? null;

        if ($idsBrutos === null || $idsBrutos === '' || $idsBrutos === []) {
            http_response_code(412);
            header('Location: ../html/recepcao/registro_saida.php?msg=O campo idVisita é obrigatório.');
            exit();
        }

        $idsBrutos = is_array($idsBrutos) ? $idsBrutos : [$idsBrutos];

        $ids = [];
        foreach ($idsBrutos as $idVisita) {
            if (!filter_var($idVisita, FILTER_VALIDATE_INT) || $idVisita <= 0) {
                http_response_code(412);
                header('Location: ../html/recepcao/registro_saida.php?msg=Visita inválida.');
                exit();
            }
            $ids[] = (int) $idVisita;
        }

        return array_values(array_unique($ids));
    }

    public function incluir()
    {
        try {
            $dados = $this->verificarVisita();

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            if (empty($dados['idsVisitado'])) {
                $dados['idsVisitado'] = [(new VisitadoDAO())->obterIdInstituicao()];
            }

            $visitaDAO = new VisitaDAO();
            $idsVisita = $visitaDAO->incluirMultiplos($dados['idsVisitante'], $dados['idsVisitado'], $dados['descricao']);

            if (empty($idsVisita))
                throw new PDOException('Erro ao registrar a(s) visita(s).', 500);

            unset($_SESSION['visitantes_entrada']);

            $_SESSION['msg_c'] = "Entrada registrada com sucesso";

            header("Location: ../html/recepcao/registro_saida.php");
        }
        catch (InvalidArgumentException $e) {
            if ($e->getCode() !== 412) {
                Util::tratarException($e);
            }

            header('Location: ../html/recepcao/registro_entrada.php?msg=' . urlencode($e->getMessage()));
            exit();
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function encerrar()
    {
        try {
            $idsVisita = $this->verificarIdsVisita();

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $visitaDAO = new VisitaDAO();
            $linhasAfetadas = $visitaDAO->encerrarMultiplos($idsVisita);

            if (!$linhasAfetadas)
                throw new PDOException('Erro ao registrar a saída da(s) visita(s).', 500);

            $_SESSION['msg_c'] = "Saída registrada com sucesso";
            $_SESSION['tipo'] = "success";

            header("Location: ../html/recepcao/registro_saida.php");
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    } 

    public function listarTodos()
    {
        try {
            $VisitaDAO = new VisitaDAO();
            $visitas = $VisitaDAO->listarTodosAgrupados();

            $_SESSION['visitas'] = json_encode($visitas);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}