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
    private function extrairImagemEnviada()
    {
        if (isset($_FILES['imgperfil']) && $_FILES['imgperfil']['error'] === UPLOAD_ERR_OK && !empty($_FILES['imgperfil']['tmp_name'])) {
            return file_get_contents($_FILES['imgperfil']['tmp_name']);
        }

        return '';
    }
    
    private const SESSAO_VISITANTES = 'visitantes_entrada';

    public static function obterIdsSelecionados(): array
    {
        $ids = $_SESSION[self::SESSAO_VISITANTES] ?? [];

        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids), fn($id) => $id > 0)));
    }

    private function adicionarASelecao(int $idVisitante): void
    {
        $ids = self::obterIdsSelecionados();

        if (!in_array($idVisitante, $ids, true)) {
            $ids[] = $idVisitante;
        }

        $_SESSION[self::SESSAO_VISITANTES] = $ids;
    }

    public function adicionarVisita()
    {
        try {
            $idVisitante = filter_input(INPUT_POST, 'idVisitante', FILTER_VALIDATE_INT);

            if (!$idVisitante || $idVisitante <= 0)
                throw new InvalidArgumentException('O visitante informado é inválido.', 412);

            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $visitanteDAO = new VisitanteDAO();

            if (empty($visitanteDAO->buscarResumoPorIds([$idVisitante])))
                throw new InvalidArgumentException('Visitante não encontrado.', 404);

            $this->adicionarASelecao($idVisitante);

            header("Location: ../html/recepcao/pre_registro_entrada.php?msg_c=" . urlencode("Visitante adicionado à visita"));
            exit();
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function removerVisita()
    {
        try {
            $idVisitante = filter_input(INPUT_POST, 'idVisitante', FILTER_VALIDATE_INT);

            if (!$idVisitante || $idVisitante <= 0)
                throw new InvalidArgumentException('O visitante informado é inválido.', 412);

            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $ids = array_values(array_filter(self::obterIdsSelecionados(), fn($id) => $id !== $idVisitante));
            $_SESSION[self::SESSAO_VISITANTES] = $ids;

            $destino = filter_input(INPUT_POST, 'retorno', FILTER_UNSAFE_RAW) === 'registro_entrada'
                ? 'registro_entrada.php'
                : 'pre_registro_entrada.php';

            if ($destino === 'registro_entrada.php' && empty($ids)) {
                $destino = 'pre_registro_entrada.php';
            }

            header("Location: ../html/recepcao/" . $destino);
            exit();
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }

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
            header('Location: ../html/recepcao/cadastro_visitante.php?msg=O CPF informado não é válido.');
            exit();
        }

        $senha = '';
        $imgperfil = $this->extrairImagemEnviada();
        $visitante = new Visitante($cpf, $nome, $sobrenome, $gender, $nascimento, null, null, null, $nome_mae ?? '', $nome_pai ?? '', $sangue ?? '', $senha, $telefone ?? null, $imgperfil, $cep ?? '', $uf ?? '', $cidade ?? '', $bairro ?? '', $rua ?? '', $numero_residencia ?? '', $complemento ?? '', $ibge ?? '');

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

            $this->adicionarASelecao((int) $idVisitante);

            header("Location: ../html/recepcao/pre_registro_entrada.php?msg_c=" . urlencode("Visitante cadastrado e adicionado à visita"));
            exit();
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

            $this->adicionarASelecao((int) $idVisitante);

            header("Location: ../html/recepcao/pre_registro_entrada.php?msg_c=" . urlencode("Visitante cadastrado e adicionado à visita"));
            exit();
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}