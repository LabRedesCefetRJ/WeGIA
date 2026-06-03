<?php
if(session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
include_once ROOT . "/dao/Conexao.php";
include_once ROOT . '/classes/Visitante.php';
include_once ROOT . '/dao/VisitanteDAO.php';
require_once ROOT . '/classes/Util.php';

class VisitanteControle
{
    public function verificarVisitante()
    {
        extract($_REQUEST);

        $camposObrigatorios = ['nome', 'sobrenome', 'gender', 'nascimento', 'cpf', 'situacao'];

        foreach($camposObrigatorios as $campo) {
            if(!isset($campo) || empty($campo)) {
                http_response_code(412);
                header('Location: ../html/visitante/cadastro_visitante.php?msg=O campo ' . $campo . ' é obrigatório.');
                exit();
            }
        }

        if(!Util::validarCPF($cpf)) {
            http_response_code(412);
            header('Location: ../html/visitante/cadastro_visitante.php?msg=O CPF informado é inválido.');
            exit();
        }

        $senha = '';
        $visitante = new Visitante($cpf, $nome, $sobrenome, $gender, $nascimento, null, null, null, $nome_mae ?? '', $nome_pai ?? '', $sangue ?? '', $senha, $telefone ?? null, $imgperfil ?? '', $cep ?? '', $uf ?? '', $cidade ?? '', $bairro ?? '', $rua ?? '', $numero_residencia ?? '', $complemento ?? '', $ibge ?? '');
        $visitante->setIdSituacao($situacao);

        return $visitante;
    }

    public function selecionarCadastro()
    {
        try {
            $cpf = filter_input(INPUT_GET, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

            if(!Util::validarCPF($cpf))
                throw new InvalidArgumentException("O CPF informado não é válido.", 412);

            $visitanteDAO = new VisitanteDao();
            $resultado = visitanteDAO->selecionarCadastro($cpf);

            if($resultado === 'PESSOA_EXISTENTE') {
                header('Location: ../html/visitante/cadastro_visitante_pessoa_existente.php?cpf=' . htmlspecialchars($cpf));
                exit;
            } else if($resultado === 'NOVO_CADASTRO') {
                header('Location: ../html/visitante/cadastro_visitante.php?cpf=' . htmlspecialchars($cpf));
                exit;
            }
        }
        catch(Exception $e) {
            if($e->getMessage() === 'Erro, Visitante já cadastrado no sistema.') {
                header("Location: ../html/visitante/pre_cadastro_visitante.php?msg_e=" . urlencode($e->getMessage()));
                exit;
            }
            Util::tratarException($e);
        }
    }

    public function incluir()
    {
        try {
            $visitante = $this->verificarVisitante();
            $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

            if(!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $visitanteDAO = new VisitanteDAO();
            $idVisitante = $visitanteDAO->incluir($visitante, $cpf);

            if(!isset($idVisitante))
                throw new PDOException('Erro ao cadastrar o visitante.', 500);

            $_SESSION['msg'] = "Visitante cadastrado com sucesso";
            $_SESSION['tipo'] = "success";

            header("Location: ../controle/control.php?metodo=listarTodos&nomeClasse=VisitanteControle&nextPage=../html/visitante/informacao_visitante.php");
        }
        catch(Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluirExistente()
    {
        try {
            $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
            $situacao = filter_input(INPUT_POST, 'situacao', FILTER_SANITIZE_NUMBER_INT);

            if(!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $visitanteDAO = new VisitanteDAO();
            $idVisitante = $visitanteDAO->incluirExistente($cpf, $situacao);

            if(!isset($idVisitante))
                throw new PDOException('Erro ao cadastrar o visitante existente.', 500);
            
            $_SESSION['msg'] = "Visitante cadastrado com sucesso";
            $_SESSION['tipo'] = "success";

            header("Location: ../controle/control.php?metodo=listarTodos&nomeClasse=VisitanteControle&nextPage=../html/visitante/informacao_visitante.php");
        }
        catch(Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarTodos()
    {
        try {
            extract($_REQUEST);

            $situacao_selecionada = isset($_GET['select_situacao']) ? $_GET['select_situacao'] : 1;

            $visitantesDAO = new VisitanteDAO();
            $visitantes = $visitantesDAO->listarTodos($situacao_selecionada);

            $_SESSION['visitantes'] = json_encode($visitantes);
            
            $nextPage = isset($nextPage) ? $nextPage : WWW . 'html/home.php';
            // Validar o Open Redirect: não permitir rotas externas (que comecem com http:// ou https://) a menos que sejam do próprio domínio
            if (preg_match('/^https?:\/\//i', $nextPage) && strpos($nextPage, WWW) !== 0) {
                $nextPage = WWW . 'html/home.php'; // Força rota segura caso tentem injetar URL externa
            }
            header('Location: ' . $nextPage);
            exit();
        }
        catch(Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarUm()
    {
        try {
            $idVisitante = filter_input(INPUT_GET, 'id_visitante', FILTER_SANITIZE_NUMBER_INT);

            if(!$idVisitante || $idVisitante <= 0)
                throw new InvalidArgumentException("ID inválido.", 412);

            $visitanteDAO = new VisitanteDAO();
            $resultado = $visitanteDAO->listarUm($idVisitante);

            $_SESSION['visitante'] = json_encode([$resultado]);

            header('Location: ../html/visitante/profile_visitante.php?id_visitante=' . urlencode($id));
            exit();
        }
        catch(Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarCpf()
    {
        try {
            $visitanteDAO = new VisitanteDAO();
            $cpfs = $visitanteDAO->listarCPF();
            header('Content-Type: application/json');
            echo json_encode($cpfs ?: []);
        }
        catch(Exception $e) {
            Util::tratarException($e);
        }
    }
}

// Funções de UPDATE a serem criadas posteriormente...