<?php
if (session_status() === PHP_SESSION_NONE)
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

        $camposObrigatorios = ['nome', 'sobrenome', 'gender', 'nascimento', 'cpf'];

        foreach ($camposObrigatorios as $campo) {
            if (!isset($$campo) || empty($$campo)) {
                http_response_code(412);
                header('Location: ../html/recepcao/cadastro_visitante.php?msg=O campo ' . $campo . ' é obrigatório.');
                exit();
            }
        }

        if (!Util::validarCPF($cpf)) {
            http_response_code(412);
            header('Location: ../html/recepcao/cadastro_visitante.php?msg=O CPF informado é inválido.');
            exit();
        }

        $senha = '';
        $visitante = new Visitante($cpf, $nome, $sobrenome, $gender, $nascimento, null, null, null, $nome_mae ?? '', $nome_pai ?? '', $sangue ?? '', $senha, $telefone ?? null, $imgperfil ?? '', $cep ?? '', $uf ?? '', $cidade ?? '', $bairro ?? '', $rua ?? '', $numero_residencia ?? '', $complemento ?? '', $ibge ?? '');

        return $visitante;
    }
    
    public function selecionarCadastro()
    {
        try {
            $cpf = filter_input(INPUT_GET, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!Util::validarCPF($cpf))
                throw new InvalidArgumentException("O CPF informado não é válido.", 412);

            $visitanteDAO = new VisitanteDAO();
            $resultado = $visitanteDAO->selecionarCadastro($cpf);
            
            if ($resultado === 'PESSOA_EXISTENTE') {
                header('Location: ../html/recepcao/cadastro_visitante_pessoa_existente.php?cpf=' . htmlspecialchars($cpf));
                exit;
            } else if ($resultado === 'NOVO_CADASTRO') {
                header('Location: ../html/recepcao/cadastro_visitante.php?cpf=' . htmlspecialchars($cpf));
                exit;
            } else if ($resultado === 'VISITANTE_EXISTENTE') {
                header('Location: ../html/recepcao/visitante_escolhido.php?cpf=' . htmlspecialchars($cpf));
                exit;
            }
        }
        catch (Exception $e) {
            if ($e->getMessage() === 'Erro, Visitante já cadastrado no sistema.') {
                header("Location: ../html/recepcao/pre_registro_entrada.php?msg_e=" . urlencode($e->getMessage()));
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

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $visitanteDAO = new VisitanteDAO();
            $idVisitante = $visitanteDAO->incluir($visitante, $cpf);

            if (!isset($idVisitante))
                throw new PDOException('Erro ao cadastrar o visitante.', 500);

            $_SESSION['msg'] = "Visitante cadastrado com sucesso";
            $_SESSION['tipo'] = "success";

            header("Location: ../html/recepcao/pre_registro_entrada.php");
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluir_existente()
    {
        try {
            $visitante = $this->verificarVisitante();
            $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $visitanteDAO = new VisitanteDAO();
            $idVisitante = $visitanteDAO->incluir_existente($visitante, $cpf);

            if (!isset($idVisitante))
                throw new PDOException('Erro ao cadastrar o visitante.', 500);

            $_SESSION['msg'] = "Visitante cadastrado com sucesso";
            $_SESSION['tipo'] = "success";

            header("Location: ../html/recepcao/registro_entrada.php?idVisitante=" . $idVisitante);
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}