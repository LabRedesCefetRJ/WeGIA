<?php

require_once dirname(__FILE__) . '/Conexao.php';

class PaStatusDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::connect();
    }

    public function inserir(string $descricao): int
    {
        $sql = "INSERT INTO pa_status (descricao) VALUES (:descricao)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':descricao', $descricao);
        if (!$stmt->execute()) {
            throw new PDOException("Erro ao inserir status.");
        }
        return (int)$this->pdo->lastInsertId();
    }

    public function atualizar(int $id, string $descricao): bool
    {
        $sql = "UPDATE pa_status SET descricao = :descricao WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function excluir(int $id): bool
    {
        $sql = "DELETE FROM pa_status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT id, descricao FROM pa_status ORDER BY id ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT id, descricao FROM pa_status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
