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
}
