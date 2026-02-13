<?php


require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/dao/DependenteDAO.php';
require_once ROOT . '/classes/Util.php';




class DependenteControle
{


    public function editarInfoPessoal()
    {
        try {
            $id_dependente = (int)($_POST['id_dependente'] ?? $_POST['iddependente'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $sobrenome = trim($_POST['sobrenome'] ?? '');
            $sexo = $_POST['sexo'] ?? $_POST['gender'] ?? '';
            $nascimento = $_POST['nascimento'] ?? null;
            $telefone = trim($_POST['telefone'] ?? '');
            $nome_pai = trim($_POST['nome_pai'] ?? '');
            $nome_mae = trim($_POST['nome_mae'] ?? '');



            error_log("DependenteControle::editarInfoPessoal - id=$id_dependente");


            if ($id_dependente < 1) {
                throw new InvalidArgumentException('ID do dependente inválido.');
            }
            if (strlen($nome) < 2) {
                throw new InvalidArgumentException('Nome deve ter pelo menos 2 caracteres.');
            }
            if (strlen($sobrenome) < 2) {
                throw new InvalidArgumentException('Sobrenome deve ter pelo menos 2 caracteres.');
            }


            $dao = new DependenteDAO();
            $sucesso = $dao->alterarInfoPessoal(
                $id_dependente,
                $nome,
                $sobrenome,
                $sexo,
                $nascimento,
                $telefone,
                $nome_pai,
                $nome_mae
            );


            if ($sucesso) {
                $_SESSION['msg'] = 'Informações pessoais atualizadas!';
                $_SESSION['tipo'] = 'success';
            } else {
                throw new Exception('Falha ao atualizar dados (nenhuma linha alterada).');
            }


            header("Location: ../html/funcionario/profile_dependente.php?id_dependente=" . $id_dependente . "#overview");
            exit;
        } catch (Exception $e) {
            error_log("ERRO editarInfoPessoal: " . $e->getMessage());
            $_SESSION['mensagem_erro'] = $e->getMessage();
            $redirect_id = $_POST['id_dependente'] ?? $id_dependente ?? 0;
            header("Location: ../html/funcionario/profile_dependente.php?id_dependente=" . $redirect_id);
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
