<?php

namespace api\modules\Saude;

use PDO;

class SaudeRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array<int, array{id:int, descricao:string}>
     */
    public function listarEspecialidades(): array
    {
        $query = "
            SELECT id, descricao
            FROM saude_especialidade
            ORDER BY descricao ASC, id ASC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result === false ? [] : $result;
    }

    /**
     * @return array<int, array{id:int, descricao:string, extensao:string|null, created_at:string|null, updated_at:string|null}>
     */
    public function listarModelosParecer(): array
    {
        $query = "
            SELECT id, descricao, extensao, created_at, updated_at
            FROM saude_parecer_modelo
            ORDER BY created_at DESC, id DESC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result === false ? [] : $result;
    }

    public function salvarModeloParecer(string $descricao, string $conteudoArquivo, string $extensao): int|false
    {
        $query = "
            INSERT INTO saude_parecer_modelo (descricao, arquivo, extensao)
            VALUES (:descricao, :arquivo, :extensao)
        ";

        $stmt = $this->pdo->prepare($query);
        $executou = $stmt->execute([
            ':descricao' => $descricao,
            ':arquivo' => $conteudoArquivo,
            ':extensao' => $extensao,
        ]);

        if (!$executou) {
            return false;
        }

        $id = $this->pdo->lastInsertId();

        return $id !== false ? (int) $id : false;
    }
}
