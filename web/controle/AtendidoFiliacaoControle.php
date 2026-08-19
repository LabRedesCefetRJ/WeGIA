<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';

class AtendidoFiliacaoControle
{
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

    private function validar(int $idAtendido, int $idParentesco, string $nome, string $email): void
    {
        if ($idAtendido < 1) {
            throw new InvalidArgumentException('O id do atendido informado não é válido.', 400);
        }
        if ($idParentesco < 1) {
            throw new InvalidArgumentException('Selecione um parentesco válido.', 412);
        }
        Util::validarNomePessoaOuLancar($nome, 'nome', 412);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('O e-mail informado não é válido.', 412);
        }
    }

    private function dadosPost(): array
    {
        return [
            'id' => filter_input(INPUT_POST, 'idatendido', FILTER_VALIDATE_INT),
            'id_parentesco' => filter_input(INPUT_POST, 'id_parentesco', FILTER_VALIDATE_INT),
            'id_filiado' => filter_input(INPUT_POST, 'id_filiado', FILTER_VALIDATE_INT),
            'cpf' => trim((string)filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS)),
            'genero' => filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'nome' => trim((string)filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS)),
            'email' => trim((string)filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)),
            'telefone' => trim((string)filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS)),
        ];
    }

    private function redirecionar(int $idAtendido): void
    {
        header('Location: ../html/atendido/Profile_Atendido.php?idatendido=' . urlencode((string)$idAtendido) . '#filiacao');
        exit;
    }

    public function cadastrar(): void
    {
        $dados = $this->dadosPost();
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            $this->validar($dados['id'], $dados['id_parentesco'], $dados['nome'], $dados['email']);
            $pdo = Conexao::connect();
            $pdo->beginTransaction();
            $stmtAtendido = $pdo->prepare('SELECT pessoa_id_pessoa FROM atendido WHERE idatendido = :idatendido');
            $stmtAtendido->execute([':idatendido' => $dados['id']]);
            $idPessoaResponsavel = (int)$stmtAtendido->fetchColumn();
            if ($idPessoaResponsavel < 1) {
                $pdo->rollBack();
                throw new InvalidArgumentException('O atendido informado não foi encontrado.', 404);
            }
            $idFiliado = $dados['id_filiado'];
            if ($dados['cpf'] !== '') {
                $stmtExistente = $pdo->prepare("SELECT id_pessoa FROM pessoa WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = REPLACE(REPLACE(REPLACE(:cpf, '.', ''), '-', ''), ' ', '') LIMIT 1");
                $stmtExistente->execute([':cpf' => $dados['cpf']]);
                $idFiliado = (int)$stmtExistente->fetchColumn() ?: $idFiliado;
            }
            if (!$idFiliado) {
                $stmtPessoa = $pdo->prepare('INSERT INTO pessoa (cpf, nome, sexo, email, telefone) VALUES (:cpf, :nome, :sexo, :email, :telefone)');
                $stmtPessoa->execute([
                    ':cpf' => $dados['cpf'] !== '' ? $dados['cpf'] : null,
                    ':nome' => $dados['nome'],
                    ':sexo' => $dados['genero'],
                    ':email' => $dados['email'] !== '' ? $dados['email'] : null,
                    ':telefone' => $dados['telefone'] !== '' ? $dados['telefone'] : null,
                ]);
                $idFiliado = (int)$pdo->lastInsertId();
            }
            $stmt = $pdo->prepare('INSERT INTO filiacao (id_pessoa, id_filiado, id_parentesco) VALUES (:id_pessoa, :id_filiado, :id_parentesco)');
            $stmt->execute([':id_pessoa' => $idPessoaResponsavel, ':id_filiado' => $idFiliado, ':id_parentesco' => $dados['id_parentesco']]);
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
        $this->redirecionar((int)$dados['id']);
    }

    public function editar(): void
    {
        $dados = $this->dadosPost();
        $idFiliacao = filter_input(INPUT_POST, 'id_filiacao', FILTER_VALIDATE_INT);
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            if (!$idFiliacao || $idFiliacao < 1) {
                throw new InvalidArgumentException('O id da filiação informado não é válido.', 400);
            }
            $this->validar($dados['id'], $dados['id_parentesco'], $dados['nome'], $dados['email']);
            $stmt = Conexao::connect()->prepare(
                'UPDATE filiacao fi
                 INNER JOIN atendido a ON a.pessoa_id_pessoa = fi.id_pessoa
                 INNER JOIN pessoa p ON p.id_pessoa = fi.id_filiado
                 SET fi.id_parentesco = :id_parentesco, p.cpf = :cpf, p.nome = :nome, p.sexo = :sexo, p.email = :email, p.telefone = :telefone
                 WHERE fi.id_filiacao = :id_filiacao AND a.idatendido = :idatendido'
            );
            $stmt->execute([
                ':id_filiacao' => $idFiliacao,
                ':idatendido' => $dados['id'],
                ':cpf' => $dados['cpf'] !== '' ? $dados['cpf'] : null,
                ':id_parentesco' => $dados['id_parentesco'],
                ':sexo' => $dados['genero'],
                ':nome' => $dados['nome'],
                ':email' => $dados['email'] !== '' ? $dados['email'] : null,
                ':telefone' => $dados['telefone'] !== '' ? $dados['telefone'] : null,
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
        $this->redirecionar((int)$dados['id']);
    }

    public function excluir(): void
    {
        $idAtendido = filter_input(INPUT_POST, 'idatendido', FILTER_VALIDATE_INT);
        $idFiliacao = filter_input(INPUT_POST, 'id_filiacao', FILTER_VALIDATE_INT);
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
                throw new InvalidArgumentException('Token de segurança inválido.', 400);
            }
            if (!$idAtendido || $idAtendido < 1 || !$idFiliacao || $idFiliacao < 1) {
                throw new InvalidArgumentException('Os dados da filiação informada não são válidos.', 400);
            }
            $stmt = Conexao::connect()->prepare('DELETE fi FROM filiacao fi INNER JOIN atendido a ON a.pessoa_id_pessoa = fi.id_pessoa WHERE fi.id_filiacao = :id_filiacao AND a.idatendido = :idatendido');
            $stmt->execute([':id_filiacao' => $idFiliacao, ':idatendido' => $idAtendido]);
            if ($stmt->rowCount() !== 1) {
                throw new InvalidArgumentException('A filiação não foi encontrada.', 404);
            }
            $_SESSION['msg'] = 'Filiação excluída com sucesso!';
            $_SESSION['tipo'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = $e->getMessage();
            $_SESSION['tipo'] = 'err';
        }
        $this->redirecionar((int)$idAtendido);
    }
}
