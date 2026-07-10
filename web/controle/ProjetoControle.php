<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once ROOT . "/dao/ProjetoDAO.php";
require_once ROOT . "/classes/projetos/Projeto.php";
require_once ROOT . "/classes/Util.php";
require_once ROOT . "/classes/Csrf.php";
require_once ROOT . "/controle/FuncionarioControle.php";

class ProjetoControle
{
    private $projetoDAO;
    private $projetoClasse;

    public function __construct()
    {
        $this->projetoDAO    = new ProjetoDAO();
        $this->projetoClasse = new ProjetoClasse();
    }

    public function obterTipos(): array
    {
        return $this->projetoDAO->listarTiposProjeto();
    }

    public function obterLocais(): array
    {
        return $this->projetoDAO->listarLocaisProjeto();
    }

    public function obterStatus(): array
    {
        return $this->projetoDAO->listarStatusProjeto();
    }

    public function adicionarTipo()
    {
        $data = $this->lerJSON();
        $tipo = trim(filter_var($data['tipo'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));

        if (empty($tipo)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Tipo não informado.']);
            exit();
        }

        $this->projetoDAO->adicionarTipoProjeto($tipo);
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => true, 'tipos' => $this->projetoDAO->listarTiposProjeto()]);
        exit();
    }

    public function adicionarLocal()
    {
        $data  = $this->lerJSON();
        $local = trim(filter_var($data['local'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));

        if (empty($local)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Local não informado.']);
            exit();
        }

        $this->projetoDAO->adicionarLocalProjeto($local);
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => true, 'locais' => $this->projetoDAO->listarLocaisProjeto()]);
        exit();
    }

    public function adicionarStatus()
    {
        $data   = $this->lerJSON();
        $status = trim(filter_var($data['status'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));

        if (empty($status)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Status não informado.']);
            exit();
        }

        $this->projetoDAO->adicionarStatusProjeto($status);
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => true, 'status' => $this->projetoDAO->listarStatusProjeto()]);
        exit();
    }

    private function obterDadosRequisicao(): array
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        $isJson      = strpos($contentType, 'application/json') !== false;

        if ($isJson) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (!is_array($data)) {
                throw new InvalidArgumentException('Dados inválidos.', 400);
            }

            return $data;
        }

        return $_POST;
    }

    private function validarDadosProjeto(array $dados): void
    {
        $nome        = trim(filter_var($dados['nome_projeto'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));
        $descricao   = trim(filter_var($dados['descricao_projeto'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));
        $id_tipo     = filter_var($dados['tipo_projeto'] ?? null, FILTER_SANITIZE_NUMBER_INT);
        $id_local    = filter_var($dados['local_projeto'] ?? null, FILTER_SANITIZE_NUMBER_INT);
        $id_status   = filter_var($dados['status_projeto'] ?? null, FILTER_SANITIZE_NUMBER_INT);
        $data_inicio = filter_var($dados['data_inicio'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $data_fim    = filter_var($dados['data_fim'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($nome) || strlen($nome) < 3)
            throw new InvalidArgumentException('Nome não informado ou inválido.', 422);

        if (!$id_tipo || $id_tipo < 1)
            throw new InvalidArgumentException('Tipo de projeto não informado ou inválido.', 422);

        if (!$id_local || $id_local < 1)
            throw new InvalidArgumentException('Local não informado ou inválido.', 422);

        if (!$id_status || $id_status < 1)
            throw new InvalidArgumentException('Status não informado ou inválido.', 422);

        if (empty($data_inicio))
            throw new InvalidArgumentException('Data de início não informada.', 422);

        $dataInicio = DateTime::createFromFormat('Y-m-d', $data_inicio);
        if (!$dataInicio)
            throw new InvalidArgumentException('Data de início inválida.', 422);

        if (!empty($data_fim)) {
            $dataFim = DateTime::createFromFormat('Y-m-d', $data_fim);
            if (!$dataFim)
                throw new InvalidArgumentException('Data de término inválida.', 422);
            if ($dataFim < $dataInicio)
                throw new InvalidArgumentException('Data de fim não pode ser anterior à data de início.', 422);
        } else {
            $data_fim = null;
        }

        $this->projetoClasse->setNome($nome);
        $this->projetoClasse->setDescricao($descricao ?? '');
        $this->projetoClasse->setIdTipo($id_tipo);
        $this->projetoClasse->setIdLocal($id_local);
        $this->projetoClasse->setIdStatus($id_status);
        $this->projetoClasse->setDataInicio($data_inicio);
        $this->projetoClasse->setDataFim($data_fim);
    }

    public function incluir()
    {
        try {
            $dados = $this->obterDadosRequisicao();

            $csrf_token = $dados['csrf_token'] ?? null;

            if (!Csrf::validateToken($csrf_token))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $this->validarDadosProjeto($dados);

            $this->projetoDAO->adicionarProjeto(
                $this->projetoClasse->getNome(),
                $this->projetoClasse->getDescricao(),
                $this->projetoClasse->getIdTipo(),
                $this->projetoClasse->getIdLocal(),
                $this->projetoClasse->getIdStatus(),
                $this->projetoClasse->getDataInicio(),
                $this->projetoClasse->getDataFim()
            );

            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'mensagem' => 'Projeto cadastrado com sucesso!']);
            exit();
        } catch (Exception $e) {
            http_response_code($e->getCode() >= 400 ? $e->getCode() : 500);
            header('Content-Type: application/json');
            echo json_encode(['erro' => $e->getMessage()]);
            exit();
        }
    }

    public function listarTodos()
    {
        try {
            $projetos             = $this->projetoDAO->listarTodos();
            $_SESSION['projetos'] = json_encode($projetos);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarUm()
    {
        $nextPage     = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $host         = strtolower(parse_url(WWW, PHP_URL_HOST));
        $nextPageHost = strtolower(parse_url($nextPage, PHP_URL_HOST));

        if ($nextPageHost !== null && $nextPageHost !== '' && $nextPageHost !== $host)
            throw new InvalidArgumentException('Redirecionamento externo não permitido.', 400);

        $idProjeto = filter_input(INPUT_GET, 'id_projeto', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!$idProjeto || $idProjeto < 1)
                throw new InvalidArgumentException('O id do projeto fornecido é inválido.', 422);

            $projeto = $this->projetoDAO->listarUm($idProjeto);

            if ($projeto) {
                $_SESSION['projeto'] = $projeto;
                header('Location: ' . $nextPage);
                exit();
            } else {
                header('Location: ' . WWW . 'html/projetos/informacao_projeto.php?msg=Projeto não encontrado!');
                exit();
            }
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function alterarProjeto()
    {
        try {
            $dados = $this->obterDadosRequisicao();

            $csrf_token = $dados['csrf_token'] ?? null;

            if (!Csrf::validateToken($csrf_token))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $id_projeto = filter_var($dados['id_projeto'] ?? null, FILTER_SANITIZE_NUMBER_INT);

            if (!$id_projeto || $id_projeto < 1)
                throw new InvalidArgumentException('ID do projeto inválido.', 422);

            $this->validarDadosProjeto($dados);

            $this->projetoDAO->alterarProjeto(
                $id_projeto,
                $this->projetoClasse->getNome(),
                $this->projetoClasse->getDescricao(),
                $this->projetoClasse->getIdTipo(),
                $this->projetoClasse->getIdLocal(),
                $this->projetoClasse->getIdStatus(),
                $this->projetoClasse->getDataInicio(),
                $this->projetoClasse->getDataFim()
            );

            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'mensagem' => 'Projeto alterado com sucesso!']);
            exit();
        } catch (Exception $e) {
            http_response_code($e->getCode() >= 400 ? $e->getCode() : 500);
            header('Content-Type: application/json');
            echo json_encode(['erro' => $e->getMessage()]);
            exit();
        }
    }

    private function lerJSON(): array
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['erro' => 'JSON inválido.']);
            exit();
        }
        return $data;
    }

    // ================== FUNÇÕES PARA EQUIPE ==================

    public function adicionarMembroEquipe()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $projeto_id = filter_input(INPUT_POST, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);
            $id_pessoa  = filter_input(INPUT_POST, 'funcionario_id', FILTER_SANITIZE_NUMBER_INT);
            $id_funcao  = filter_input(INPUT_POST, 'funcao_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$projeto_id || $projeto_id < 1) {
                echo json_encode(['success' => false, 'message' => 'Projeto inválido']);
                return;
            }

            if (!$id_pessoa || $id_pessoa < 1) {
                echo json_encode(['success' => false, 'message' => 'Pessoa inválida']);
                return;
            }

            if (!$id_funcao || $id_funcao < 1) {
                echo json_encode(['success' => false, 'message' => 'Função inválida']);
                return;
            }

            $resultado = $this->projetoDAO->adicionarMembroEquipe($projeto_id, $id_pessoa, $id_funcao);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function removerMembroEquipe()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $id         = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $projeto_id = filter_input(INPUT_POST, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }

            $resultado = $this->projetoDAO->removerMembroEquipe($id, $projeto_id);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function listarEquipeAjax()
    {
        try {
            header('Content-Type: application/json');

            $projeto_id = filter_input(INPUT_GET, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);
            $turma_ids  = isset($_GET['turma_ids']) && is_array($_GET['turma_ids'])
                ? array_filter(array_map('intval', $_GET['turma_ids']), fn($v) => $v > 0)
                : [];

            if (!$projeto_id || $projeto_id < 1) {
                echo json_encode(['success' => false, 'message' => 'Projeto inválido']);
                return;
            }

            if (!empty($turma_ids)) {
                $equipe = $this->projetoDAO->listarEquipeProjetoPorTurmas($projeto_id, array_values($turma_ids));
            } else {
                $equipe = $this->projetoDAO->listarEquipeProjeto($projeto_id);
            }

            echo json_encode(['success' => true, 'data' => $equipe]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function adicionarFuncaoProjeto()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $descricao = trim(filter_var($_POST['descricao'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));

            if (empty($descricao)) {
                echo json_encode(['success' => false, 'message' => 'Descrição da função não informada']);
                return;
            }

            $resultado = $this->projetoDAO->adicionarFuncaoProjeto($descricao);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function listarFuncoesAjax()
    {
        try {
            header('Content-Type: application/json');

            $funcoes = $this->projetoDAO->listarFuncoesProjeto();

            echo json_encode(['success' => true, 'data' => $funcoes]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ================== FUNÇÕES PARA ATENDIDOS ==================

    public function listarAtendidosAjax()
    {
        try {
            header('Content-Type: application/json');
            $atendidos = $this->projetoDAO->listarTodosAtendidos();
            echo json_encode(['success' => true, 'data' => $atendidos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function listarAtendidosProjetoAjax()
    {
        try {
            header('Content-Type: application/json');

            $projeto_id = filter_input(INPUT_GET, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);
            $turma_ids  = isset($_GET['turma_ids']) && is_array($_GET['turma_ids'])
                ? array_filter(array_map('intval', $_GET['turma_ids']), fn($v) => $v > 0)
                : [];

            if (!$projeto_id || $projeto_id < 1) {
                echo json_encode(['success' => false, 'message' => 'Projeto inválido']);
                return;
            }

            if (!empty($turma_ids)) {
                $atendidos = $this->projetoDAO->listarAtendidosProjetoPorTurmas($projeto_id, array_values($turma_ids));
            } else {
                $atendidos = $this->projetoDAO->listarAtendidosProjeto($projeto_id);
            }

            echo json_encode(['success' => true, 'data' => $atendidos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function listarStatusAtendidosAjax()
    {
        try {
            header('Content-Type: application/json');

            $status = $this->projetoDAO->listarStatusAtendidoProjeto();

            echo json_encode(['success' => true, 'data' => $status]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function adicionarAtendidoProjeto()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $projeto_id  = filter_input(INPUT_POST, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);
            $id_atendido = filter_input(INPUT_POST, 'atendido_id', FILTER_SANITIZE_NUMBER_INT);
            $id_status   = filter_input(INPUT_POST, 'status_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$projeto_id || $projeto_id < 1) {
                echo json_encode(['success' => false, 'message' => 'Projeto inválido']);
                return;
            }

            if (!$id_atendido || $id_atendido < 1) {
                echo json_encode(['success' => false, 'message' => 'Atendido inválido']);
                return;
            }

            if (!$id_status || $id_status < 1) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }

            $resultado = $this->projetoDAO->adicionarAtendidoProjeto($projeto_id, $id_atendido, $id_status);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function removerAtendidoProjeto()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $id         = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $projeto_id = filter_input(INPUT_POST, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }

            $resultado = $this->projetoDAO->removerAtendidoProjeto($id, $projeto_id);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function atualizarStatusAtendidoProjeto()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $id        = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $id_status = filter_input(INPUT_POST, 'id_status', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }

            if (!$id_status || $id_status < 1) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }

            $resultado = $this->projetoDAO->atualizarStatusAtendidoProjeto($id, $id_status);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function adicionarStatusAtendidoProjeto()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $descricao = trim(filter_var($_POST['descricao'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));

            if (empty($descricao)) {
                echo json_encode(['success' => false, 'message' => 'Descrição do status não informada']);
                return;
            }

            $resultado = $this->projetoDAO->adicionarStatusAtendidoProjeto($descricao);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ================== FUNÇÕES PARA TURMAS ==================

    public function listarExecutantesForaDaTurmaAjax()
    {
        try {
            header('Content-Type: application/json');

            $projeto_id = filter_input(INPUT_GET, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);
            $turma_id   = filter_input(INPUT_GET, 'turma_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$projeto_id || $projeto_id < 1 || !$turma_id || $turma_id < 1) {
                echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
                return;
            }

            $data = $this->projetoDAO->listarExecutantesForaDaTurma($projeto_id, $turma_id);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function listarAtendidosForaDaTurmaAjax()
    {
        try {
            header('Content-Type: application/json');

            $projeto_id = filter_input(INPUT_GET, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);
            $turma_id   = filter_input(INPUT_GET, 'turma_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$projeto_id || $projeto_id < 1 || !$turma_id || $turma_id < 1) {
                echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
                return;
            }

            $data = $this->projetoDAO->listarAtendidosForaDaTurma($projeto_id, $turma_id);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function listarTurmasAjax()
    {
        try {
            header('Content-Type: application/json');

            $projeto_id = filter_input(INPUT_GET, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$projeto_id || $projeto_id < 1) {
                echo json_encode(['success' => false, 'message' => 'Projeto inválido']);
                return;
            }

            $turmas = $this->projetoDAO->listarTurmasProjeto($projeto_id);

            echo json_encode(['success' => true, 'data' => $turmas]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function adicionarTurma()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $projeto_id = filter_input(INPUT_POST, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);
            $nome       = trim(filter_var($_POST['nome'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));

            if (!$projeto_id || $projeto_id < 1) {
                echo json_encode(['success' => false, 'message' => 'Projeto inválido']);
                return;
            }

            if (empty($nome)) {
                echo json_encode(['success' => false, 'message' => 'Nome da turma não informado']);
                return;
            }

            $id_turma = $this->projetoDAO->adicionarTurma($projeto_id, $nome);

            echo json_encode(['success' => true, 'id_turma' => $id_turma]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function removerTurma()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $id_turma   = filter_input(INPUT_POST, 'id_turma', FILTER_SANITIZE_NUMBER_INT);
            $projeto_id = filter_input(INPUT_POST, 'projeto_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_turma || $id_turma < 1) {
                echo json_encode(['success' => false, 'message' => 'Turma inválida']);
                return;
            }

            $resultado = $this->projetoDAO->removerTurma($id_turma, $projeto_id);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function adicionarExecutanteTurma()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $id_turma  = filter_input(INPUT_POST, 'id_turma', FILTER_SANITIZE_NUMBER_INT);
            $id_pessoa = filter_input(INPUT_POST, 'id_pessoa', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_turma || $id_turma < 1) {
                echo json_encode(['success' => false, 'message' => 'Turma inválida']);
                return;
            }

            if (!$id_pessoa || $id_pessoa < 1) {
                echo json_encode(['success' => false, 'message' => 'Executante inválido']);
                return;
            }

            $resultado = $this->projetoDAO->adicionarExecutanteTurma($id_turma, $id_pessoa);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function removerExecutanteTurma()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $id_turma  = filter_input(INPUT_POST, 'id_turma', FILTER_SANITIZE_NUMBER_INT);
            $id_pessoa = filter_input(INPUT_POST, 'id_pessoa', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_turma || $id_turma < 1) {
                echo json_encode(['success' => false, 'message' => 'Turma inválida']);
                return;
            }

            $resultado = $this->projetoDAO->removerExecutanteTurma($id_turma, $id_pessoa);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function adicionarAtendidoTurma()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $id_turma    = filter_input(INPUT_POST, 'id_turma', FILTER_SANITIZE_NUMBER_INT);
            $id_atendido = filter_input(INPUT_POST, 'id_atendido', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_turma || $id_turma < 1) {
                echo json_encode(['success' => false, 'message' => 'Turma inválida']);
                return;
            }

            if (!$id_atendido || $id_atendido < 1) {
                echo json_encode(['success' => false, 'message' => 'Atendido inválido']);
                return;
            }

            $resultado = $this->projetoDAO->adicionarAtendidoTurma($id_turma, $id_atendido);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function removerAtendidoTurma()
    {
        try {
            header('Content-Type: application/json');

            $csrf_token = $_POST['csrf_token'] ?? null;
            if (!Csrf::validateToken($csrf_token)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            $id_turma    = filter_input(INPUT_POST, 'id_turma', FILTER_SANITIZE_NUMBER_INT);
            $id_atendido = filter_input(INPUT_POST, 'id_atendido', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_turma || $id_turma < 1) {
                echo json_encode(['success' => false, 'message' => 'Turma inválida']);
                return;
            }

            $resultado = $this->projetoDAO->removerAtendidoTurma($id_turma, $id_atendido);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>