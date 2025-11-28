<?php

require_once ROOT . '/dao/PaStatusDAO.php';
require_once ROOT . '/classes/Util.php';

class PaStatusControle
{
    public function incluir()
    {
        try {
            $descricao = trim($_POST['descricao'] ?? '');
            if (empty($descricao)) {
                throw new InvalidArgumentException('Descrição é obrigatória.');
            }

            $dao = new PaStatusDAO();
            $dao->inserir($descricao);

            $_SESSION['msg'] = 'Status cadastrado com sucesso!';
            header('Location: ../html/lista_pa_status.php');
            exit();
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
            header('Location: ../html/cadastro_pa_status.php');
            exit();
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
            $_SESSION['mensagem_erro'] = $e->getMessage();
            header("Location: ../html/editar_pa_status.php?id=$id");
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
            $_SESSION['mensagem_erro'] = $e->getMessage();
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
