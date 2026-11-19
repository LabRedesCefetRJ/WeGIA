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
require_once ROOT . '/html/permissao/permissao.php';

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
            $visitaDAO = new VisitaDAO();
            $visitas = $visitaDAO->listarTodosAgrupados();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($visitas, JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Histórico de entradas e saídas (JSON) para a página historico_visitas.php.
     */
    public function listarHistorico()
    {
        try {
            permissao($_SESSION['id_pessoa'], 20, 5);

            $filtros = self::lerFiltrosHistorico($_GET);
            $grupos = (new VisitaDAO())->listarHistorico($filtros);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($grupos, JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Valida os filtros do histórico de visitas (usados pela listagem e pelo relatório em PDF).
     *
     * @throws InvalidArgumentException (412) quando algum filtro é inválido.
     */
    public static function lerFiltrosHistorico(array $fonte): array
    {
        $texto = function (string $campo) use ($fonte): string {
            return is_string($fonte[$campo] ?? null) ? trim($fonte[$campo]) : '';
        };

        $filtros = [];

        foreach (['data_inicio' => 'inicial', 'data_fim' => 'final'] as $campo => $rotulo) {
            $valor = $texto($campo);
            if ($valor === '') {
                continue;
            }

            $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
            if (!$data || $data->format('Y-m-d') !== $valor) {
                throw new InvalidArgumentException("A data {$rotulo} informada é inválida.", 412);
            }

            $filtros[$campo] = $valor;
        }

        if (isset($filtros['data_inicio'], $filtros['data_fim']) && $filtros['data_inicio'] > $filtros['data_fim']) {
            throw new InvalidArgumentException('A data inicial não pode ser posterior à data final.', 412);
        }

        $idVisitado = $texto('id_visitado');
        if ($idVisitado !== '') {
            $id = filter_var($idVisitado, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                throw new InvalidArgumentException('O visitado informado é inválido.', 412);
            }
            $filtros['id_visitado'] = $id;
        }

        $categoria = $texto('categoria');
        if ($categoria !== '') {
            if (!in_array($categoria, VisitaDAO::CATEGORIAS_VISITADO, true)) {
                throw new InvalidArgumentException('O tipo de visitado informado é inválido.', 412);
            }
            $filtros['categoria'] = $categoria;
        }

        $situacao = $texto('situacao');
        if ($situacao !== '') {
            if (!in_array($situacao, [VisitaDAO::SITUACAO_EM_ANDAMENTO, VisitaDAO::SITUACAO_ENCERRADA], true)) {
                throw new InvalidArgumentException('A situação informada é inválida.', 412);
            }
            $filtros['situacao'] = $situacao;
        }

        return $filtros;
    }
}
