<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';

class FiliacaoControle
{
    private function buscarFiliacaoAutorizada(int $idFiliacao, int $idFuncionario, PDO $pdo): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT fi.id_filiacao, fi.id_pessoa, fi.id_filiado, fi.id_parentesco,
                    par.descricao AS parentesco, p.cpf, p.nome, p.sexo, p.email, p.telefone,
                    p.data_nascimento, p.cep, p.estado, p.cidade, p.bairro, p.logradouro,
                    p.numero_endereco, p.complemento, p.ibge, p.registro_geral,
                    p.orgao_emissor, p.data_expedicao
             FROM filiacao fi
             INNER JOIN funcionario responsavel ON responsavel.id_pessoa = fi.id_pessoa
             INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
             INNER JOIN parentesco par ON par.id_parentesco = fi.id_parentesco
             WHERE fi.id_filiacao = :id_filiacao AND responsavel.id_funcionario = :id_funcionario'
        );
        $stmt->execute([
            ':id_filiacao' => $idFiliacao,
            ':id_funcionario' => $idFuncionario,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function validarAcessoEFormulario(int $idFiliacao, int $idFuncionario, PDO $pdo): array
    {
        if ($idFiliacao < 1 || $idFuncionario < 1) {
            throw new InvalidArgumentException('Os dados da filiação informada não são válidos.', 400);
        }

        $filiacao = $this->buscarFiliacaoAutorizada($idFiliacao, $idFuncionario, $pdo);
        if (!$filiacao) {
            throw new InvalidArgumentException('A filiação não foi encontrada.', 404);
        }

        return $filiacao;
    }

    private function redirecionarEdicao(int $idFiliacao, int $idFuncionario, string $aba = 'informacoes-pessoais'): void
    {
        header('Location: ../html/funcionario/filiacao_editar.php?id_filiacao=' . urlencode((string)$idFiliacao) . '&id_funcionario=' . urlencode((string)$idFuncionario) . '#' . $aba);
        exit;
    }

    private function buscarIdPessoaResponsavel(int $idFuncionario, PDO $pdo): int
    {
        $stmt = $pdo->prepare('SELECT id_pessoa FROM funcionario WHERE id_funcionario = :id_funcionario');
        $stmt->execute([':id_funcionario' => $idFuncionario]);
        $idPessoa = (int)$stmt->fetchColumn();
        if ($idPessoa < 1) {
            throw new InvalidArgumentException('O funcionário informado não foi encontrado.', 404);
        }
        return $idPessoa;
    }

    public function buscarPessoa(): void
    {
        $cpf = trim((string)filter_input(INPUT_GET, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS));
        if ($cpf === '') {
            echo json_encode([]);
            return;
        }
        $stmt = Conexao::connect()->prepare("SELECT id_pessoa, nome, sexo, email, telefone FROM pessoa WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = REPLACE(REPLACE(REPLACE(:cpf, '.', ''), '-', ''), ' ', '') LIMIT 1");
        $stmt->execute([':cpf' => $cpf]);
        header('Content-Type: application/json');
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: []);
    }

    private function validarDados(int $idFuncionario, int $idParentesco, string $nome, string $email): void
    {
        if ($idFuncionario < 1) {
            throw new InvalidArgumentException('O id do funcionário informado não é válido.', 400);
        }
        if ($idParentesco < 1) {
            throw new InvalidArgumentException('Selecione um parentesco válido.', 412);
        }
        Util::validarNomePessoaOuLancar($nome, 'nome', 412);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('O e-mail informado não é válido.', 412);
        }
    }

    public function cadastrar(): void
    {
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $idParentesco = filter_input(INPUT_POST, 'id_parentesco', FILTER_VALIDATE_INT);
        $idFiliado = filter_input(INPUT_POST, 'id_filiado', FILTER_VALIDATE_INT);
        $cpf = trim((string)filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS));
        $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
        $nome = trim((string)filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
        $email = trim((string)filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $telefone = trim((string)filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS));

        $redirect = '../html/funcionario/profile_funcionario.php?id_funcionario=' . urlencode((string)$idFuncionario) . '#filiacao';

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            $this->validarDados((int)$idFuncionario, (int)$idParentesco, $nome, $email);

            $pdo = Conexao::connect();
            $pdo->beginTransaction();
            $idPessoaResponsavel = $this->buscarIdPessoaResponsavel((int)$idFuncionario, $pdo);
            if ($cpf !== '') {
                $stmtExistente = $pdo->prepare("SELECT id_pessoa FROM pessoa WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = REPLACE(REPLACE(REPLACE(:cpf, '.', ''), '-', ''), ' ', '') LIMIT 1");
                $stmtExistente->execute([':cpf' => $cpf]);
                $idFiliado = (int)$stmtExistente->fetchColumn() ?: $idFiliado;
            }
            if (!$idFiliado) {
                $stmtPessoa = $pdo->prepare('INSERT INTO pessoa (cpf, nome, sexo, email, telefone) VALUES (:cpf, :nome, :sexo, :email, :telefone)');
                $stmtPessoa->execute([
                    ':cpf' => $cpf !== '' ? $cpf : null,
                    ':nome' => $nome,
                    ':sexo' => $genero,
                    ':email' => $email !== '' ? $email : null,
                    ':telefone' => $telefone !== '' ? $telefone : null,
                ]);
                $idFiliado = (int)$pdo->lastInsertId();
            }
            $stmt = $pdo->prepare('INSERT INTO filiacao (id_pessoa, id_filiado, id_parentesco) VALUES (:id_pessoa, :id_filiado, :id_parentesco)');
            $stmt->execute([':id_pessoa' => $idPessoaResponsavel, ':id_filiado' => $idFiliado, ':id_parentesco' => $idParentesco]);
            $pdo->commit();
            $_SESSION['msg'] = 'Filiação adicionada com sucesso!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }

        header("Location: $redirect");
        exit;
    }

    public function editar(): void
    {
        $idFiliacao = filter_input(INPUT_POST, 'id_filiacao', FILTER_VALIDATE_INT);
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $idParentesco = filter_input(INPUT_POST, 'id_parentesco', FILTER_VALIDATE_INT);
        $idFiliado = filter_input(INPUT_POST, 'id_filiado', FILTER_VALIDATE_INT);
        $cpf = trim((string)filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS));
        $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
        $nome = trim((string)filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
        $email = trim((string)filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $telefone = trim((string)filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS));
        $redirect = '../html/funcionario/profile_funcionario.php?id_funcionario=' . urlencode((string)$idFuncionario) . '#filiacao';

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            if (!$idFiliacao || $idFiliacao < 1) {
                throw new InvalidArgumentException('O id da filiação informado não é válido.', 400);
            }
            $this->validarDados((int)$idFuncionario, (int)$idParentesco, $nome, $email);

            $pdo = Conexao::connect();
            $stmt = $pdo->prepare(
                'UPDATE filiacao fi
                 INNER JOIN funcionario f ON f.id_pessoa = fi.id_pessoa
                 INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
                 SET fi.id_parentesco = :id_parentesco, p.cpf = :cpf, p.nome = :nome, p.sexo = :sexo, p.email = :email, p.telefone = :telefone
                 WHERE fi.id_filiacao = :id_filiacao AND f.id_funcionario = :id_funcionario'
            );
            $stmt->execute([
                ':id_filiacao' => $idFiliacao,
                ':id_funcionario' => $idFuncionario,
                ':cpf' => $cpf !== '' ? $cpf : null,
                ':id_parentesco' => $idParentesco,
                ':sexo' => $genero,
                ':nome' => $nome,
                ':email' => $email !== '' ? $email : null,
                ':telefone' => $telefone !== '' ? $telefone : null,
            ]);
            if ($stmt->rowCount() !== 1) {
                throw new InvalidArgumentException('A filiação não foi encontrada ou não foi alterada.', 404);
            }
            $_SESSION['msg'] = 'Filiação atualizada com sucesso!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }

        header("Location: $redirect");
        exit;
    }

    public function editarInfoPessoal(): void
    {
        $idFiliacao = (int)filter_input(INPUT_POST, 'id_filiacao', FILTER_VALIDATE_INT);
        $idFuncionario = (int)filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $idParentesco = (int)filter_input(INPUT_POST, 'id_parentesco', FILTER_VALIDATE_INT);
        $nome = trim((string)filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
        $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
        $email = trim((string)filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $telefone = trim((string)filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS));
        $dataNascimento = trim((string)filter_input(INPUT_POST, 'data_nascimento', FILTER_UNSAFE_RAW));

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            if ($idParentesco < 1) {
                throw new InvalidArgumentException('Selecione um parentesco válido.', 412);
            }
            $pdo = Conexao::connect();
            $this->validarAcessoEFormulario($idFiliacao, $idFuncionario, $pdo);
            Util::validarNomePessoaOuLancar($nome, 'nome', 412);

            if ($genero !== null && $genero !== '' && !Util::validarGenero($genero)) {
                throw new InvalidArgumentException('O gênero informado não é válido.', 412);
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('O e-mail informado não é válido.', 412);
            }
            if ($telefone !== '' && !preg_match('/^(?:\+55\s?)?\(?[1-9][0-9]\)?\s?(?:9[0-9]{4}|[2-8][0-9]{3})-?[0-9]{4}$/', $telefone)) {
                throw new InvalidArgumentException('O telefone informado não está em um formato válido.', 412);
            }
            if ($dataNascimento !== '') {
                $nascimento = DateTime::createFromFormat('!Y-m-d', $dataNascimento);
                if (!$nascimento || $nascimento->format('Y-m-d') !== $dataNascimento || $nascimento > new DateTime('today')) {
                    throw new InvalidArgumentException('A data de nascimento informada não é válida.', 412);
                }
            }

            $stmt = $pdo->prepare(
                'UPDATE filiacao fi
                 INNER JOIN funcionario f ON f.id_pessoa = fi.id_pessoa
                 INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
                 SET fi.id_parentesco = :id_parentesco,
                     p.nome = :nome,
                     p.sexo = :sexo,
                     p.email = :email,
                     p.telefone = :telefone,
                     p.data_nascimento = :data_nascimento
                 WHERE fi.id_filiacao = :id_filiacao AND f.id_funcionario = :id_funcionario'
            );
            $stmt->execute([
                ':id_parentesco' => $idParentesco,
                ':nome' => $nome,
                ':sexo' => $genero !== '' ? $genero : null,
                ':email' => $email !== '' ? $email : null,
                ':telefone' => $telefone !== '' ? $telefone : null,
                ':data_nascimento' => $dataNascimento !== '' ? $dataNascimento : null,
                ':id_filiacao' => $idFiliacao,
                ':id_funcionario' => $idFuncionario,
            ]);
            $_SESSION['msg'] = 'Informações pessoais atualizadas!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }

        $this->redirecionarEdicao($idFiliacao, $idFuncionario, 'informacoes-pessoais');
    }

    public function editarDocumentacao(): void
    {
        $idFiliacao = (int)filter_input(INPUT_POST, 'id_filiacao', FILTER_VALIDATE_INT);
        $idFuncionario = (int)filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $cpf = trim((string)filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS));
        $rg = trim((string)filter_input(INPUT_POST, 'rg', FILTER_SANITIZE_SPECIAL_CHARS));
        $orgaoEmissor = trim((string)filter_input(INPUT_POST, 'orgao_emissor', FILTER_SANITIZE_SPECIAL_CHARS));
        $dataExpedicao = trim((string)filter_input(INPUT_POST, 'data_expedicao', FILTER_UNSAFE_RAW));

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            $pdo = Conexao::connect();
            $filiacao = $this->validarAcessoEFormulario($idFiliacao, $idFuncionario, $pdo);

            if ($cpf !== '' && !Util::validarCPF($cpf)) {
                throw new InvalidArgumentException('O CPF informado não é válido.', 412);
            }
            if ($dataExpedicao !== '') {
                $expedicao = DateTime::createFromFormat('!Y-m-d', $dataExpedicao);
                if (!$expedicao || $expedicao->format('Y-m-d') !== $dataExpedicao || $expedicao > new DateTime('today')) {
                    throw new InvalidArgumentException('A data de expedição informada não é válida.', 412);
                }
                if (!empty($filiacao['data_nascimento']) && $dataExpedicao <= $filiacao['data_nascimento']) {
                    throw new InvalidArgumentException('A data de expedição deve ser posterior à data de nascimento.', 412);
                }
            }

            $stmt = $pdo->prepare(
                'UPDATE filiacao fi
                 INNER JOIN funcionario f ON f.id_pessoa = fi.id_pessoa
                 INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
                 SET p.cpf = :cpf,
                     p.registro_geral = :rg,
                     p.orgao_emissor = :orgao_emissor,
                     p.data_expedicao = :data_expedicao
                 WHERE fi.id_filiacao = :id_filiacao AND f.id_funcionario = :id_funcionario'
            );
            $stmt->execute([
                ':cpf' => $cpf !== '' ? $cpf : null,
                ':rg' => $rg !== '' ? $rg : null,
                ':orgao_emissor' => $orgaoEmissor !== '' ? $orgaoEmissor : null,
                ':data_expedicao' => $dataExpedicao !== '' ? $dataExpedicao : null,
                ':id_filiacao' => $idFiliacao,
                ':id_funcionario' => $idFuncionario,
            ]);
            $_SESSION['msg'] = 'Documentação atualizada!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }

        $this->redirecionarEdicao($idFiliacao, $idFuncionario, 'documentacao');
    }

    public function editarEndereco(): void
    {
        $idFiliacao = (int)filter_input(INPUT_POST, 'id_filiacao', FILTER_VALIDATE_INT);
        $idFuncionario = (int)filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $cep = trim((string)filter_input(INPUT_POST, 'cep', FILTER_UNSAFE_RAW));
        $estado = trim((string)filter_input(INPUT_POST, 'estado', FILTER_UNSAFE_RAW));
        $cidade = trim((string)filter_input(INPUT_POST, 'cidade', FILTER_UNSAFE_RAW));
        $bairro = trim((string)filter_input(INPUT_POST, 'bairro', FILTER_UNSAFE_RAW));
        $logradouro = trim((string)filter_input(INPUT_POST, 'logradouro', FILTER_UNSAFE_RAW));
        $numero = trim((string)filter_input(INPUT_POST, 'numero_endereco', FILTER_UNSAFE_RAW));
        $complemento = trim((string)filter_input(INPUT_POST, 'complemento', FILTER_UNSAFE_RAW));
        $ibge = trim((string)filter_input(INPUT_POST, 'ibge', FILTER_UNSAFE_RAW));

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            $pdo = Conexao::connect();
            $this->validarAcessoEFormulario($idFiliacao, $idFuncionario, $pdo);
            if ($cep !== '' && !preg_match('/^\d{5}-?\d{3}$/', $cep)) {
                throw new InvalidArgumentException('O CEP informado não está em um formato válido.', 412);
            }

            $stmt = $pdo->prepare(
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
                 WHERE fi.id_filiacao = :id_filiacao AND f.id_funcionario = :id_funcionario'
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
                ':id_filiacao' => $idFiliacao,
                ':id_funcionario' => $idFuncionario,
            ]);
            $_SESSION['msg'] = 'Endereço atualizado!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }

        $this->redirecionarEdicao($idFiliacao, $idFuncionario, 'endereco');
    }

    public function excluir(): void
    {
        $idFiliacao = filter_input(INPUT_POST, 'id_filiacao', FILTER_VALIDATE_INT);
        $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $redirect = '../html/funcionario/profile_funcionario.php?id_funcionario=' . urlencode((string)$idFuncionario) . '#filiacao';

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            if (!$idFiliacao || !$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('Os dados da filiação informada não são válidos.', 400);
            }

            $pdo = Conexao::connect();
            $stmt = $pdo->prepare('DELETE fi FROM filiacao fi INNER JOIN funcionario f ON f.id_pessoa = fi.id_pessoa WHERE fi.id_filiacao = :id_filiacao AND f.id_funcionario = :id_funcionario');
            $stmt->execute([
                ':id_filiacao' => $idFiliacao,
                ':id_funcionario' => $idFuncionario,
            ]);
            if ($stmt->rowCount() !== 1) {
                throw new InvalidArgumentException('A filiação não foi encontrada.', 404);
            }
            $_SESSION['msg'] = 'Filiação excluída com sucesso!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }
 
        header("Location: $redirect");
        exit;
    }
}
