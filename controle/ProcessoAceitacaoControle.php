<?php

require_once ROOT . '/classes/Pessoa.php';
require_once ROOT . '/dao/PessoaDAO.php';
require_once ROOT . '/dao/ProcessoAceitacaoDAO.php';
require_once ROOT . '/classes/Util.php';
require_once ROOT . '/dao/Conexao.php';

class ProcessoAceitacaoControle
{
    
    public function atualizarStatus()
{
    session_start();
    require_once ROOT . '/dao/Conexao.php';
    require_once ROOT . '/dao/ProcessoAceitacaoDAO.php';

    $idProcesso = (int)($_POST['id_processo'] ?? 0);
    $idStatus   = (int)($_POST['id_status'] ?? 0);

    if ($idProcesso <= 0 || $idStatus <= 0) {
        $_SESSION['mensagem_erro'] = 'Processo ou status inválido.';
        header("Location: ../html/atendido/processo_aceitacao.php");
        exit();
    }

    $pdo = Conexao::connect();
    $dao = new ProcessoAceitacaoDAO($pdo);
    $dao->atualizarStatus($idProcesso, $idStatus);

    $_SESSION['msg'] = 'Status do processo atualizado com sucesso.';
    header("Location: ../html/atendido/etapa_processo.php?id=".$idProcesso);
    exit();
}
    
    
    
    
    public function incluir()
    {
        try {
            $nome = trim($_POST['nome']);
            $sobrenome = trim($_POST['sobrenome']);
            $cpf = trim($_POST['cpf']);

            $pdo = Conexao::connect();

            // Verifica se CPF já existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE cpf = ?");
            $stmt->execute([$cpf]);
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                throw new InvalidArgumentException('Erro: CPF já cadastrado no sistema.', 400);
            }

            // Valida CPF
            if (!Util::validarCPF($cpf)) {
                throw new InvalidArgumentException('Erro: o CPF informado não é válido.', 400);
            }

            // Valida campos obrigatórios
            if (empty($nome) || empty($sobrenome)) {
                throw new InvalidArgumentException('Erro: Nome e Sobrenome são obrigatórios.', 400);
            }

            // Usa DAO para inserir pessoa
            $pessoaDAO = new PessoaDAO($pdo);
            $id_pessoa = $pessoaDAO->inserirPessoa($cpf, $nome, $sobrenome);

            // Cria processo aceitação para a pessoa criada
            $processoDAO = new ProcessoAceitacaoDAO($pdo);
            $processoDAO->criarProcessoInicial($id_pessoa);

            $_SESSION['msg'] = "Processo cadastrado com sucesso!";
            // VOLTA para a própria página do processo de aceitação
            header("Location: ../html/atendido/processo_aceitacao.php");
            exit();

        } catch (InvalidArgumentException $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
            header("Location: ../html/atendido/processo_aceitacao.php");
            exit();
        } catch (PDOException $e) {
            Util::tratarException($e);
            exit();
        }
    }
}

?>
