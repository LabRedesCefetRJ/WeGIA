<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once ROOT . "/dao/projetos/ProjetoDAO.php";
require_once ROOT . "/classes/projetos/Projeto.php";
require_once ROOT . "/classes/Util.php";
require_once ROOT . "/classes/Csrf.php";

class ProjetoControle
{
    private $projetoDAO;
    private $projetoClasse;

    public function __construct()
    {
        $this->projetoDAO = new ProjetoDAO();
        $this->projetoClasse = new ProjetoClasse();
    }

    private function verificar()
    {
        $nome = filter_input(INPUT_POST, 'nome_projeto', FILTER_SANITIZE_SPECIAL_CHARS);
        $descricao = filter_input(INPUT_POST, 'descricao_projeto', FILTER_SANITIZE_SPECIAL_CHARS);
        $id_tipo = filter_input(INPUT_POST, 'tipo_projeto', FILTER_SANITIZE_NUMBER_INT);
        $id_local = filter_input(INPUT_POST, 'local_projeto', FILTER_SANITIZE_NUMBER_INT);
        $id_status = filter_input(INPUT_POST, 'status_projeto', FILTER_SANITIZE_NUMBER_INT);
        $data_inicio = filter_input(INPUT_POST, 'data_inicio', FILTER_SANITIZE_SPECIAL_CHARS);
        $data_fim = filter_input(INPUT_POST, 'data_fim', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!isset($nome) || strlen($nome) < 3) {
            header("Location: " . WWW . "html/projetos/cadastrar_projeto.php?msg=Nome não informado ou inválido!");
            exit();
        }

        if (!isset($id_tipo) || $id_tipo < 1) {
            header("Location: " . WWW . "html/projetos/cadastrar_projeto.php?msg=Tipo de projeto não informado ou inválido!");
            exit();
        }

        if (!isset($id_local) || $id_local < 1) {
            header("Location: " . WWW . "html/projetos/cadastrar_projeto.php?msg=Local não informado ou inválido!");
            exit();
        }

        if (!isset($id_status) || $id_status < 1) {
            header("Location: " . WWW . "html/projetos/cadastrar_projeto.php?msg=Status não informado ou inválido!");
            exit();
        }

        if (!isset($data_inicio) || empty($data_inicio)) {
            header("Location: " . WWW . "html/projetos/cadastrar_projeto.php?msg=Data de início não informada!");
            exit();
        }

        $dataInicio = DateTime::createFromFormat('Y-m-d', $data_inicio);
        if (!$dataInicio) {
            header("Location: " . WWW . "html/projetos/cadastrar_projeto.php?msg=Data de início inválida!");
            exit();
        }

        if (!empty($data_fim)) {
            $dataFim = DateTime::createFromFormat('Y-m-d', $data_fim);
            if (!$dataFim) {
                header("Location: " . WWW . "html/projetos/cadastrar_projeto.php?msg=Data de término inválida!");
                exit();
            }

            if ($dataFim < $dataInicio) {
                header("Location: " . WWW . "html/projetos/cadastrar_projeto.php?msg=Data de fim não pode ser anterior à data de início!");
                exit();
            }
        } else {
            $data_fim = null;
        }

        if (!isset($descricao)) {
            $descricao = '';
        }

        $this->projetoClasse->setNome($nome);
        $this->projetoClasse->setDescricao($descricao);
        $this->projetoClasse->setIdTipo($id_tipo);
        $this->projetoClasse->setIdLocal($id_local);
        $this->projetoClasse->setIdStatus($id_status);
        $this->projetoClasse->setDataInicio($data_inicio);
        $this->projetoClasse->setDataFim($data_fim);
    }

    public function incluir()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $this->verificar();
            
            $this->projetoDAO->adicionarProjeto(
                $this->projetoClasse->getNome(),
                $this->projetoClasse->getDescricao(),
                $this->projetoClasse->getIdTipo(),
                $this->projetoClasse->getIdLocal(),
                $this->projetoClasse->getIdStatus(),
                $this->projetoClasse->getDataInicio(),
                $this->projetoClasse->getDataFim()
            );

            header('Location: ' . WWW . 'html/projetos/informacao_projeto.php');
            exit();
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarTodos()
    {
        try {
            $projetoDAO = new ProjetoDAO();
            $projetos = $projetoDAO->listarTodos();
            $_SESSION['projetos'] = json_encode($projetos);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarUm()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $host = parse_url(WWW, PHP_URL_HOST);
        $nextPageHost = parse_url($nextPage, PHP_URL_HOST);

        if ($nextPageHost !== null && $nextPageHost !== $host) {
            throw new InvalidArgumentException('Redirecionamento externo não permitido.', 400);
        }
        
        $idProjeto = filter_input(INPUT_GET, 'id_projeto', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!$idProjeto || $idProjeto < 1)
                throw new InvalidArgumentException('O id do projeto fornecido é inválido.', 422);

            $projetoDAO = new ProjetoDAO();
            $projeto = $projetoDAO->listarUm($idProjeto);
            
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
            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $id_projeto = filter_input(INPUT_POST, 'id_projeto', FILTER_SANITIZE_NUMBER_INT);
            
            if (!$id_projeto || $id_projeto < 1) {
                header("Location: " . WWW . "html/projetos/informacao_projeto.php?msg=ID do projeto inválido!");
                exit();
            }

            $this->verificar();
            
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

            header('Location: ' . WWW . 'html/projetos/informacao_projeto.php?msg=Projeto alterado com sucesso!');
            exit();
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
?>