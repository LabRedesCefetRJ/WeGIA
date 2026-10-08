<?php
if (session_status() === PHP_SESSION_NONE)
session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
include_once ROOT . "/dao/Conexao.php";
include_once ROOT . '/classes/Setor.php';
include_once ROOT . '/dao/SetorDAO.php';
require_once ROOT . '/classes/Util.php';

class SetorControle
{
    public function verificarSetor()
    {
        $descricaoBruta = filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW);
        $descricao = $descricaoBruta !== false && $descricaoBruta !== null ? trim(preg_replace('/\s+/u', ' ', $descricaoBruta)) : '';

        if ($descricao === '') {
            throw new InvalidArgumentException('O campo descrição é obrigatório.', 412);
        }

        if (mb_strlen($descricao) > Setor::TAMANHO_MAXIMO_DESCRICAO) {
            throw new InvalidArgumentException('A descrição do setor deve ter no máximo ' . Setor::TAMANHO_MAXIMO_DESCRICAO . ' caracteres.', 412);
        }

        return new Setor(null, $descricao);
    }

    public function incluir()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $setor = $this->verificarSetor();

            $setorDAO = new SetorDAO();
            $idSetor = $setorDAO->incluir($setor);

            if (!isset($idSetor))
                throw new PDOException('Erro ao cadastrar o setor.', 500);

            $_SESSION['msg_c'] = "Setor criado com sucesso. Agora você pode adicioná-lo como visitado.";

            header("Location: ../html/recepcao/cadastro_setor.php");
            exit();
        } catch (InvalidArgumentException $e) {
            if ($e->getCode() === 403) {
                Util::tratarException($e);
            }

            $_SESSION['msg_e'] = $e->getMessage();
            header("Location: ../html/recepcao/cadastro_setor.php");
            exit();
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}