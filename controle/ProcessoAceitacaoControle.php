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

        if ($idProcesso <= 0 || $idStatus <= 0) {
            $_SESSION['mensagem_erro'] = 'Processo ou status inválido.';
            header("Location: ../html/atendido/processo_aceitacao.php");
            exit();
        }

        try {
            $dao = new ProcessoAceitacaoDAO($this->pdo);
            $dao->atualizarStatus($idProcesso, $idStatus);

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
            $nome = trim($_POST['nome']);
            $sobrenome = trim($_POST['sobrenome']);
            $cpf = trim($_POST['cpf']);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE cpf = ?");
            $stmt->execute([$cpf]);

            //futuramente caso um CPF já esteja cadastrado, o sistema deve pegar os dados da pessoa existente no sistema e usar para criar o processo
            if ((int)$stmt->fetchColumn() > 0) {
                throw new InvalidArgumentException('Erro: CPF já cadastrado no sistema.', 400);
            }

            if (!Util::validarCPF($cpf)) {
                throw new InvalidArgumentException('Erro: o CPF informado não é válido.', 400);
            }

            if (empty($nome) || empty($sobrenome)) {
                throw new InvalidArgumentException('Erro: Nome e Sobrenome são obrigatórios.', 400);
            }

            $pessoaDAO = new PessoaDAO($this->pdo);
            $id_pessoa = $pessoaDAO->inserirPessoa($cpf, $nome, $sobrenome);

            $processoDAO = new ProcessoAceitacaoDAO($this->pdo);
            $processoDAO->criarProcessoInicial($id_pessoa);

            $_SESSION['msg'] = "Processo cadastrado com sucesso!";
            header("Location: ../html/atendido/processo_aceitacao.php");
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
            header("Location: ../html/atendido/processo_aceitacao.php");
            exit();
        } catch (PDOException $e) {
            Util::tratarException($e);
            exit();
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
