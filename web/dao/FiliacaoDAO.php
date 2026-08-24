<?php
require_once ROOT . "/dao/Conexao.php";

class FiliacaoDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::connect();
    }

    public function buscarFiliacaoAutorizada(int $idFiliado, int $idFuncionario): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT fi.id_pessoa, fi.id_filiado, fi.id_parentesco,
                    par.descricao AS parentesco, p.cpf, p.nome, p.sexo, p.email, p.telefone,
                    p.data_nascimento, p.cep, p.estado, p.cidade, p.bairro, p.logradouro,
                    p.numero_endereco, p.complemento, p.ibge, p.registro_geral,
                    p.orgao_emissor, p.data_expedicao
             FROM filiacao fi
             INNER JOIN funcionario responsavel ON responsavel.id_pessoa = fi.id_pessoa
             INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
             INNER JOIN parentesco par ON par.id_parentesco = fi.id_parentesco
             WHERE fi.id_filiado = :id_filiado AND responsavel.id_funcionario = :id_funcionario'
        );
        $stmt->execute([
            ':id_filiado' => $idFiliado,
            ':id_funcionario' => $idFuncionario,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPessoaPorCpf(string $cpf): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id_pessoa, nome, sexo, email, telefone FROM pessoa WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = REPLACE(REPLACE(REPLACE(:cpf, '.', ''), '-', ''), ' ', '') LIMIT 1");
        $stmt->execute([':cpf' => $cpf]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function cadastrarFiliacao(int $idFuncionario, int $idParentesco, int $idFiliado, string $cpf, ?string $genero, string $nome, string $email, string $telefone): void
    {
        try {
            // AQUI ESTÁ SEU CONTROLE DE TRANSAÇÃO MOVIDO PARA O LUGAR CERTO!
            $this->pdo->beginTransaction();

            $stmtResp = $this->pdo->prepare('SELECT id_pessoa FROM funcionario WHERE id_funcionario = :id_funcionario');
            $stmtResp->execute([':id_funcionario' => $idFuncionario]);
            $idPessoaResponsavel = (int)$stmtResp->fetchColumn();

            if ($idPessoaResponsavel < 1) {
                throw new InvalidArgumentException('O funcionário informado não foi encontrado.', 404);
            }

            if ($cpf !== '') {
                $stmtExistente = $this->pdo->prepare("SELECT id_pessoa FROM pessoa WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = REPLACE(REPLACE(REPLACE(:cpf, '.', ''), '-', ''), ' ', '') LIMIT 1");
                $stmtExistente->execute([':cpf' => $cpf]);
                $idEncontrado = (int)$stmtExistente->fetchColumn();
                if ($idEncontrado > 0) {
                    $idFiliado = $idEncontrado;
                }
            }

            if (!$idFiliado) {
                $stmtPessoa = $this->pdo->prepare('INSERT INTO pessoa (cpf, nome, sexo, email, telefone) VALUES (:cpf, :nome, :sexo, :email, :telefone)');
                $stmtPessoa->execute([
                    ':cpf' => $cpf !== '' ? $cpf : null,
                    ':nome' => $nome,
                    ':sexo' => $genero,
                    ':email' => $email !== '' ? $email : null,
                    ':telefone' => $telefone !== '' ? $telefone : null,
                ]);
                $idFiliado = (int)$this->pdo->lastInsertId();
            }

            $stmt = $this->pdo->prepare('INSERT INTO filiacao (id_pessoa, id_filiado, id_parentesco) VALUES (:id_pessoa, :id_filiado, :id_parentesco)');
            $stmt->execute([':id_pessoa' => $idPessoaResponsavel, ':id_filiado' => $idFiliado, ':id_parentesco' => $idParentesco]);

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function editar(int $idFiliado, int $idFuncionario, int $idParentesco, string $cpf, ?string $genero, string $nome, string $email, string $telefone): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE filiacao fi
             INNER JOIN funcionario f ON f.id_pessoa = fi.id_pessoa
             INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
             SET fi.id_parentesco = :id_parentesco, p.cpf = :cpf, p.nome = :nome, p.sexo = :sexo, p.email = :email, p.telefone = :telefone
             WHERE fi.id_filiado = :id_filiado AND f.id_funcionario = :id_funcionario'
        );
        $stmt->execute([
            ':id_filiado' => $idFiliado,
            ':id_funcionario' => $idFuncionario,
            ':cpf' => $cpf !== '' ? $cpf : null,
            ':id_parentesco' => $idParentesco,
            ':sexo' => $genero,
            ':nome' => $nome,
            ':email' => $email !== '' ? $email : null,
            ':telefone' => $telefone !== '' ? $telefone : null,
        ]);
        return $stmt->rowCount();
    }

    public function editarInfoPessoal(int $idFiliado, int $idFuncionario, int $idParentesco, string $nome, ?string $genero, string $email, string $telefone, string $dataNascimento): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE filiacao fi
             INNER JOIN funcionario f ON f.id_pessoa = fi.id_pessoa
             INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
             SET fi.id_parentesco = :id_parentesco,
                 p.nome = :nome,
                 p.sexo = :sexo,
                 p.email = :email,
                 p.telefone = :telefone,
                 p.data_nascimento = :data_nascimento
             WHERE fi.id_filiado = :id_filiado AND f.id_funcionario = :id_funcionario'
        );
        $stmt->execute([
            ':id_parentesco' => $idParentesco,
            ':nome' => $nome,
            ':sexo' => $genero !== '' ? $genero : null,
            ':email' => $email !== '' ? $email : null,
            ':telefone' => $telefone !== '' ? $telefone : null,
            ':data_nascimento' => $dataNascimento !== '' ? $dataNascimento : null,
            ':id_filiado' => $idFiliado,
            ':id_funcionario' => $idFuncionario,
        ]);
    }

    public function editarDocumentacao(int $idFiliado, int $idFuncionario, string $cpf, string $rg, string $orgaoEmissor, string $dataExpedicao): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE filiacao fi
             INNER JOIN funcionario f ON f.id_pessoa = fi.id_pessoa
             INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
             SET p.cpf = :cpf,
                 p.registro_geral = :rg,
                 p.orgao_emissor = :orgao_emissor,
                 p.data_expedicao = :data_expedicao
             WHERE fi.id_filiado = :id_filiado AND f.id_funcionario = :id_funcionario'
        );
        $stmt->execute([
            ':cpf' => $cpf !== '' ? $cpf : null,
            ':rg' => $rg !== '' ? $rg : null,
            ':orgao_emissor' => $orgaoEmissor !== '' ? $orgaoEmissor : null,
            ':data_expedicao' => $dataExpedicao !== '' ? $dataExpedicao : null,
            ':id_filiado' => $idFiliado,
            ':id_funcionario' => $idFuncionario,
        ]);
    }

    public function editarEndereco(int $idFiliado, int $idFuncionario, string $cep, string $estado, string $cidade, string $bairro, string $logradouro, string $numero, string $complemento, string $ibge): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE filiacao fi
             INNER JOIN funcionario f ON f.id_pessoa = fi.id_pessoa
             INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
             SET p.cep = :cep,
                 p.estado = :estado,
                 p.cidade = :cidade,
                 p.bairro = :bairro,
                 p.logradouro = :logradouro,
                 p.numero_endereco = :numero_endereco,
                 p.complemento = :complemento,
                 p.ibge = :ibge
             WHERE fi.id_filiado = :id_filiado AND f.id_funcionario = :id_funcionario'
        );
        $stmt->execute([
            ':cep' => $cep !== '' ? $cep : null,
            ':estado' => $estado !== '' ? $estado : null,
            ':cidade' => $cidade !== '' ? $cidade : null,
            ':bairro' => $bairro !== '' ? $bairro : null,
            ':logradouro' => $logradouro !== '' ? $logradouro : null,
            ':numero_endereco' => $numero !== '' ? $numero : null,
            ':complemento' => $complemento !== '' ? $complemento : null,
            ':ibge' => $ibge !== '' ? $ibge : null,
            ':id_filiado' => $idFiliado,
            ':id_funcionario' => $idFuncionario,
        ]);
    }

    public function excluir(int $idFiliado, int $idFuncionario): int
    {
        $stmt = $this->pdo->prepare('DELETE fi FROM filiacao fi INNER JOIN funcionario f ON f.id_pessoa = fi.id_pessoa WHERE fi.id_filiado = :id_filiado AND f.id_funcionario = :id_funcionario');
        $stmt->execute([
            ':id_filiado' => $idFiliado, 
            ':id_funcionario' => $idFuncionario,
        ]);
        return $stmt->rowCount();
    }
}