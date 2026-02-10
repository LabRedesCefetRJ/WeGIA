<?php

require_once ROOT . '/dao/PaStatusDAO.php';
require_once ROOT . '/classes/Util.php';

class PaStatusControle
{
    public function incluir()
    {
        try {
            // Lê o corpo da requisição (JSON)
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);

            if (!is_array($data)) {
                throw new InvalidArgumentException('JSON inválido.', 400);
            }

            $descricao = $data['descricao'] ?? null;
            $descricao = trim($descricao);

            if (!$descricao || empty($descricao) || strlen($descricao) > 512)
                throw new InvalidArgumentException('Informe uma descrição válida.' . $descricao, 400);

            $dao = new PaStatusDAO();

            if (!$dao->inserir($descricao))
                throw new Exception('Erro ao inserir nova descrição', 500);

            // Retorna a lista completa
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($dao->listarTodos());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function atualizar()
    {
        try {
            $id = intval($_POST['id'] ?? 0);
            $descricao = trim($_POST['descricao'] ?? '');
            if ($id <= 0 || empty($descricao)) {
                throw new InvalidArgumentException('ID e descrição são obrigatórios.');
            }

            $dao = new PaStatusDAO();
            $dao->atualizar($id, $descricao);

            $_SESSION['msg'] = 'Status atualizado com sucesso!';
            header('Location: ../html/lista_pa_status.php');
            exit();
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = 'Erro ao atualizar status.';
            header('Location: ../html/lista_pa_status.php');
            exit();
        }
    }

    public function excluir()
    {
        try {
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new InvalidArgumentException('ID inválido.');
            }

            $dao = new PaStatusDAO();
            $dao->excluir($id);

            $_SESSION['msg'] = 'Status excluído com sucesso!';
            header('Location: ../html/lista_pa_status.php');
            exit();
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = 'Erro ao excluir status.';
            header('Location: ../html/lista_pa_status.php');
            exit();
        }
    }

    public function listar(): array
    {
        $dao = new PaStatusDAO();
        return $dao->listarTodos();
    }

    public function buscarPorId(int $id): ?array
    {
        $dao = new PaStatusDAO();
        return $dao->buscarPorId($id);
    }
}
