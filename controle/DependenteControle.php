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
            $id_dependente = filter_input(INPUT_POST, 'id_dependente', FILTER_SANITIZE_NUMBER_INT);
            $rg = filter_input(INPUT_POST, 'rg', FILTER_SANITIZE_SPECIAL_CHARS);
            $orgao_emissor = filter_input(INPUT_POST, 'orgao_emissor', FILTER_SANITIZE_SPECIAL_CHARS);
            $data_expedicao = filter_input(INPUT_POST, 'data_expedicao', FILTER_UNSAFE_RAW);
            $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

            if ($id_dependente < 1)
                throw new InvalidArgumentException('ID do dependente inválido.', 412);
        
            if (!Util::validarCPF($cpf))
                throw new InvalidArgumentException('CPF inválido.', 412);

            $dao = new DependenteDAO();
            $sucesso = $dao->alterarDocumentacao($id_dependente, $rg, $orgao_emissor, $data_expedicao, $cpf);

            if ($sucesso) {
                $_SESSION['msg'] = 'Documentação atualizada!';
                $_SESSION['tipo'] = 'success';
            } else {
                throw new Exception('Falha ao atualizar documentação.', 500);
            }

            header("Location: ../html/funcionario/profile_dependente.php?id_dependente=" . $id_dependente . "#documentacao");
            exit;
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = 'Erro ao editar a documentação de um dependente';
            Util::tratarException($e);
            header("Location: ../html/funcionario/profile_dependente.php?id_dependente=" . $id_dependente . "#documentacao");
            exit;
        }
    }
}
