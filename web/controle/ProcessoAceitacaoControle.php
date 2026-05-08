<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once ROOT . '/classes/Pessoa.php';
require_once ROOT . '/dao/PessoaDAO.php';
require_once ROOT . '/dao/ProcessoAceitacaoDAO.php';
require_once ROOT . '/classes/Util.php';
require_once ROOT . '/dao/Conexao.php';

class ProcessoAceitacaoControle
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }
    public function atualizarStatus()
    {
        $idProcesso = (int)($_POST['id_processo'] ?? 0);
        $idStatus   = (int)($_POST['id_status'] ?? 0);
        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);

        if ($idProcesso <= 0 || $idStatus <= 0) {
            $_SESSION['mensagem_erro'] = 'Processo ou status inválido.';
            header("Location: ../html/atendido/processo_aceitacao.php");
            exit();
        }

        try {
            $dao = new ProcessoAceitacaoDAO($this->pdo);
            $dao->alterar($idProcesso, $idStatus, $descricao);

            $_SESSION['msg'] = 'Status do processo atualizado com sucesso.';
            header("Location: ../html/atendido/processo_aceitacao.php?status-processo=" . htmlspecialchars($idStatus));
            exit();
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluir()
    {
        try {
            $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
            $sobrenome = filter_input(INPUT_POST, 'sobrenome', FILTER_SANITIZE_SPECIAL_CHARS);
            $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
            $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);

            if (empty($nome) || empty($sobrenome))
                throw new InvalidArgumentException('Erro: Nome e Sobrenome são obrigatórios.', 400);

            if (strlen($cpf) != 0 && !Util::validarCPF($cpf))
                throw new InvalidArgumentException('Erro: o CPF informado não é válido.', 400);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE cpf = ?"); //Isso deve virar responsabilidade da classe de Pessoa
            $stmt->execute([$cpf]);

            //futuramente caso um CPF já esteja cadastrado, o sistema deve pegar os dados da pessoa existente no sistema e usar para criar o processo
            if ((int)$stmt->fetchColumn() > 0)
                throw new InvalidArgumentException('Erro: CPF já cadastrado no sistema.', 400);

            $pessoaDAO = new PessoaDAO($this->pdo);
            $processoDAO = new ProcessoAceitacaoDAO($this->pdo);

            $this->pdo->beginTransaction();

            $id_pessoa = isset($cpf) && !empty($cpf) ? $pessoaDAO->inserirPessoa($cpf, $nome, $sobrenome): $pessoaDAO->inserirPessoa(null, $nome, $sobrenome);

            $resultado = $processoDAO->criarProcessoInicial($id_pessoa, 1, $descricao); 
            if(!$resultado || $resultado <= 0)
                throw new Exception('Erro ao cadastrar processo de aceitação no servidor.', 500);

            $this->pdo->commit();

            $_SESSION['msg'] = "Processo cadastrado com sucesso!";
            header("Location: ../html/atendido/processo_aceitacao.php");
        } catch (Exception $e) {
            if($this->pdo->inTransaction())
                $this->pdo->rollBack();

            $mensagem = $e instanceof PDOException ? 'Erro ao manipular o banco de dados da aplicação' : $e->getMessage();
            $_SESSION['mensagem_erro'] = $mensagem;

            header("Location: ../html/atendido/processo_aceitacao.php");
            Util::tratarException($e);
        }
    }

    public function criarAtendidoProcesso()
    {
        $idProcesso = (int)($_GET['id_processo'] ?? 0);

        if ($idProcesso <= 0) {
            $_SESSION['mensagem_erro'] = 'Processo inválido.';
            header("Location: ../html/atendido/processo_aceitacao.php");
            exit;
        }

        try {
            $dao = new ProcessoAceitacaoDAO($this->pdo);

            $procConcluido = $dao->buscarPorIdConcluido($idProcesso);
            if (!$procConcluido) {
                $_SESSION['mensagem_erro'] = 'Não é possível criar atendido: Processo ainda não foi concluído.';
                header("Location: ../html/atendido/processo_aceitacao.php");
                exit;
            }

            header(
                "Location: ../controle/control.php?nomeClasse=AtendidoControle&metodo=incluirExistenteDoProcesso"
                    . "&id_processo=" . $idProcesso
                    . "&intTipo=1&intStatus=1"
            );
            exit;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function getStatusDoProcesso()
    {
        $idProcesso = filter_input(INPUT_GET, 'id_processo', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!$idProcesso || $idProcesso < 1)
                throw new InvalidArgumentException('O id de um processo não pode ser menor que 1.', 412);

            $processoDao = new ProcessoAceitacaoDAO($this->pdo);

            $idStatus = $processoDao->getStatusDoProcesso($idProcesso);

            if ($idStatus === false) {
                echo json_encode([
                    "success" =>  false
                ]);
                exit();
            }

            echo json_encode([
                "success" =>  true,
                "id_status" => $idStatus
            ]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
