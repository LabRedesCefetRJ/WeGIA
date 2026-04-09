<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
include_once ROOT . "/dao/Conexao.php";
include_once ROOT . '/classes/Voluntario.php';
include_once ROOT . '/dao/VoluntarioDAO.php';
require_once ROOT . '/classes/Util.php';

class VoluntarioControle
{
    /** Extrai os dados de uma requisição e retorna um objeto do tipo Voluntario */
    public function verificarVoluntario()
    {
        extract($_REQUEST);

        $camposObrigatorios = ['nome', 'sobrenome', 'gender', 'nascimento', 'rg', 'orgao_emissor', 'data_expedicao', 'cpf', 'data_admissao', 'situacao'];

        foreach ($camposObrigatorios as $campo) {
            if (!isset($$campo) || empty($$campo)) {
                http_response_code(412);
                header('Location: ../html/voluntario/cadastro_voluntario.php?msg=O campo ' . $campo . ' é obrigatório.');
                exit();
            }
        }

        if (!Util::validarCPF($cpf)) {
            http_response_code(412);
            header('Location: ../html/voluntario/cadastro_voluntario.php?msg=O CPF informado é inválido.');
            exit();
        }

        $senha = '';
        $voluntario = new Voluntario($cpf, $nome, $sobrenome, $gender, $nascimento, $rg, $orgao_emissor, $data_expedicao, $nome_mae ?? '', $nome_pai ?? '', $sangue ?? '', $senha, $telefone ?? 'null', $imgperfil ?? '', $cep ?? '', $uf ?? '', $cidade ?? '', $bairro ?? '', $rua ?? '', $numero_residencia ?? '', $complemento ?? '', $ibge ?? '');
        $voluntario->setData_admissao($data_admissao);
        $voluntario->setId_situacao($situacao);

        return $voluntario;
    }

    public function selecionarCadastro()
    {
        try {
            $cpf = filter_input(INPUT_GET, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!Util::validarCPF($cpf))
                throw new InvalidArgumentException("O CPF informado não é válido.", 412);

            $voluntarioDAO = new VoluntarioDAO();
            $resultado = $voluntarioDAO->selecionarCadastro($cpf);
            
            if ($resultado === 'PESSOA_EXISTENTE') {
                header('Location: ../html/voluntario/cadastro_voluntario_pessoa_existente.php?cpf=' . htmlspecialchars($cpf));
                exit;
            } else if ($resultado === 'NOVO_CADASTRO') {
                header('Location: ../html/voluntario/cadastro_voluntario.php?cpf=' . htmlspecialchars($cpf));
                exit;
            }
        }
        catch (Exception $e) {
            if ($e->getMessage() === 'Erro, Voluntário já cadastrado no sistema.') {
                header("Location: ../html/voluntario/pre_cadastro_voluntario.php?msg_e=" . urlencode($e->getMessage()));
                exit;
            }
            Util::tratarException($e);
        }
    }

    public function incluir()
    {
        try {
            $voluntario = $this->verificarVoluntario();
            $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $voluntarioDAO = new VoluntarioDAO();
            $idVoluntario = $voluntarioDAO->incluir($voluntario, $cpf);

            if (!isset($idVoluntario))
                throw new PDOException('Erro ao cadastrar o voluntário.', 500);

            $_SESSION['msg'] = "Voluntário cadastrado com sucesso";
            $_SESSION['tipo'] = "success";

            header("Location: ../controle/control.php?metodo=listarTodos&nomeClasse=VoluntarioControle&nextPage=../html/voluntario/informacao_voluntario.php");
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarTodos()
    {
        try {
            extract($_REQUEST);

            $situacao_selecionada = isset($_GET['select_situacao']) ? $_GET['select_situacao'] : 1;

            $voluntariosDAO = new VoluntarioDAO();
            $voluntarios = $voluntariosDAO->listarTodos($situacao_selecionada);

            $_SESSION['voluntarios'] = json_encode($voluntarios);

            $nextPage = isset($nextPage) ? $nextPage : WWW . 'html/home.php';
            // Validar o Open Redirect: não permitir rotas externas (que comecem com http:// ou https://) a menos que sejam do próprio domínio
            if (preg_match('/^https?:\/\//i', $nextPage) && strpos($nextPage, WWW) !== 0) {
                $nextPage = WWW . 'html/home.php'; // Força rota segura caso tentem injetar URL externa
            }
            header('Location: ' . $nextPage);
            exit();
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarCpf()
    {
        try {
            $voluntarioDAO = new VoluntarioDAO();
            $cpfs = $voluntarioDAO->listarCPF();
            header('Content-Type: application/json');
            echo json_encode($cpfs ?: []);
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}