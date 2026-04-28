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
            $nome = $this->getPostValue('nome');
            $sobrenome = $this->getPostValue('sobrenome');
            $cpf = $this->normalizeCpf($this->getPostValue('cpf'));
            $descricao = $this->getPostValue('descricao');
            $telefone = $this->getPostValue('telefone');
            $cep = $this->getPostValue('cep');
            $rua = $this->getPostValue('rua');
            $bairro = $this->getPostValue('bairro');
            $cidade = $this->getPostValue('cidade');
            $uf = $this->getPostValue('uf');
            $numero = $this->getPostValue('numero_residencia');
            $complemento = $this->getPostValue('complemento');
            $ibge = $this->getPostValue('ibge');

            if (empty($nome) || empty($sobrenome)) {
                throw new InvalidArgumentException('Nome e Sobrenome são obrigatórios.', 400);
            }

            if ($cpf !== null && !Util::validarCPF($cpf)) {
                throw new InvalidArgumentException('CPF inválido. Verifique o número informado.', 400);
            }

            $this->validarTelefone($telefone);
            $cep = $this->validarCep($cep);
            $this->validarEndereco([
                'cep' => $cep,
                'rua' => $rua,
                'bairro' => $bairro,
                'cidade' => $cidade,
                'uf' => $uf,
                'numero_residencia' => $numero,
                'complemento' => $complemento,
                'ibge' => $ibge,
            ]);

            if ($cpf !== null) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE cpf = ?");
                $stmt->execute([$cpf]);

                if ((int)$stmt->fetchColumn() > 0) {
                    throw new InvalidArgumentException('CPF já cadastrado no sistema.', 400);
                }
            }

            $pessoaDAO = new PessoaDAO($this->pdo);
            $processoDAO = new ProcessoAceitacaoDAO($this->pdo);

            $this->pdo->beginTransaction();

            $id_pessoa = $pessoaDAO->inserirPessoa(
                $cpf,
                $nome,
                $sobrenome,
                $telefone,
                $cep,
                $rua,
                $bairro,
                $cidade,
                $uf,
                $numero,
                $complemento,
                $ibge
            );

            $resultado = $processoDAO->criarProcessoInicial($id_pessoa, 1, $descricao);
            if (!$resultado || $resultado <= 0) {
                throw new Exception('Erro ao cadastrar processo de aceitação no servidor.', 500);
            }

            $this->pdo->commit();

            $_SESSION['msg'] = 'Processo cadastrado com sucesso!';
            header('Location: ../html/atendido/processo_aceitacao.php');
            exit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $mensagem = $e instanceof PDOException ? 'Erro ao manipular o banco de dados da aplicação.' : $e->getMessage();
            $_SESSION['mensagem_erro'] = $mensagem;

            header('Location: ../html/atendido/processo_aceitacao.php');
            exit();
        }
    }

    private function getPostValue(string $name, int $filter = FILTER_SANITIZE_SPECIAL_CHARS): ?string
    {
        $value = filter_input(INPUT_POST, $name, $filter);

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private function normalizeCpf(?string $cpf): ?string
    {
        if ($cpf === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $cpf);
        return $digits === '' ? null : $digits;
    }

    private function validarTelefone(?string $telefone): void
    {
        if ($telefone === null) {
            return;
        }

        $digits = preg_replace('/\D+/', '', $telefone);
        if (!preg_match('/^\d{10,11}$/', $digits)) {
            throw new InvalidArgumentException('Telefone inválido. Informe DDD + número, com 10 ou 11 dígitos.', 400);
        }
    }

    private function validarCep(?string $cep): ?string
    {
        if ($cep === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $cep);
        if (!preg_match('/^\d{8}$/', $digits)) {
            throw new InvalidArgumentException('CEP inválido. Use o formato 00000-000.', 400);
        }

        return substr($digits, 0, 5) . '-' . substr($digits, 5);
    }

    private function validarEndereco(array $endereco): void
    {
        $anyAddressField = false;
        foreach ($endereco as $value) {
            if (!empty($value)) {
                $anyAddressField = true;
                break;
            }
        }

        if (!$anyAddressField) {
            return;
        }

        $requiredFields = ['rua', 'bairro', 'cidade', 'uf'];
        foreach ($requiredFields as $field) {
            if (empty($endereco[$field])) {
                throw new InvalidArgumentException('Informe o endereço completo ou deixe todos os campos de endereço em branco.', 400);
            }
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
