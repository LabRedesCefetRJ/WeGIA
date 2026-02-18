<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Dependente.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'DependenteDTO.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/dao/DependenteDAO.php';
require_once ROOT . '/classes/Util.php';

class DependenteControle
{


    public function editarInfoPessoal()
    {
        try {
            $dependente = new Dependente(new DependenteDTO($_POST));

            $dao = new DependenteDAO();
            $sucesso = $dao->alterarInfoPessoal($dependente);

            if ($sucesso) {
                $_SESSION['msg'] = 'Informações pessoais atualizadas!';
                $_SESSION['tipo'] = 'success';
            } else {
                throw new Exception('Falha ao atualizar dados (nenhuma linha alterada).');
            }

            header("Location: ../html/funcionario/profile_dependente.php?id_dependente=" . $dependente->getId() . "#overview");
            exit;
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = 'Erro ao editar as informações pessoais do dependente';
            Util::tratarException($e);
            header("Location: ../html/funcionario/profile_dependente.php?id_dependente=" . $dependente->getId());
            exit;
        }
    }

    public function listarUm()
    {
        try {
            $id_dependente = (int)($_REQUEST['id_dependente'] ?? $_REQUEST['iddependente'] ?? 0);


            if ($id_dependente < 1) {
                http_response_code(400);
                echo json_encode(['erro' => 'ID inválido']);
                exit;
            }


            $dao = new DependenteDAO();
            $dependente = $dao->buscarPorId($id_dependente);


            header('Content-Type: application/json');
            echo json_encode($dependente ?: []);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function editarDocumentacao()
    {
        try {
            $id_dependente = (int)($_POST['id_dependente'] ?? 0);
            $id_pessoa = (int)($_POST['id_pessoa'] ?? 0);
            $rg = trim($_POST['rg'] ?? '');
            $orgao_emissor = trim($_POST['orgao_emissor'] ?? '');
            $data_expedicao = $_POST['data_expedicao'] ?? null;
            $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');

            error_log("DependenteControle::editarDocumentacao - id_dependente=$id_dependente, id_pessoa=$id_pessoa");

            if ($id_dependente < 1) {
                throw new InvalidArgumentException('ID do dependente inválido.');
            }
            if ($cpf && strlen($cpf) !== 11) {
                throw new InvalidArgumentException('CPF inválido.');
            }

            $dao = new DependenteDAO();
            $sucesso = $dao->alterarDocumentacao($id_pessoa, $rg, $orgao_emissor, $data_expedicao, $cpf);

            if ($sucesso) {
                $_SESSION['msg'] = 'Documentação atualizada!';
                $_SESSION['tipo'] = 'success';
            } else {
                throw new Exception('Falha ao atualizar documentação.');
            }

            header("Location: ../html/funcionario/profile_dependente.php?id_dependente=" . $id_dependente . "#documentacao");
            exit;
        } catch (Exception $e) {
            error_log("ERRO editarDocumentacao: " . $e->getMessage());
            $_SESSION['mensagem_erro'] = $e->getMessage();
            $redirect_id = $_POST['id_dependente'] ?? 0;
            header("Location: ../html/funcionario/profile_dependente.php?id_dependente=" . $redirect_id . "#documentacao");
            exit;
        }
    }
}
