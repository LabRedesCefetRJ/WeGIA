<?php

namespace api\modules\Configuracao;

use PDO;

class DocumentoModeloRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array<int, array{id:int, descricao:string, extensao:string|null, created_at:string|null, updated_at:string|null, global:int}>
     */
    public function listarModelos(): array
    {
        $query = "
            SELECT id, id_funcionario, descricao, extensao, created_at, updated_at, global
            FROM modelo_documento
            ORDER BY created_at DESC, id DESC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result === false ? [] : $result;
    }

    /**
     * @return array{id:int, documento:?string, extensao:?string}|null
     */
    public function buscarArquivoModelo(int $id): ?array
    {
        $query = "
            SELECT id, documento, extensao
            FROM modelo_documento
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    public function salvarModelo(string $descricao, string $conteudoArquivo, string $extensao, int $idFuncionario, bool $global): int|false
    {
        $query = "
            INSERT INTO modelo_documento (id_funcionario, descricao, documento, extensao, global)
            VALUES (:id_funcionario, :descricao, :documento, :extensao, :global)
        ";

        $stmt = $this->pdo->prepare($query);
        $executou = $stmt->execute([
            ':id_funcionario' => $idFuncionario,
            ':descricao' => $descricao,
            ':documento' => $conteudoArquivo,
            ':extensao' => $extensao,
            ':global' => $global ? 1 : 0,
        ]);

        if (!$executou) {
            return false;
        }

        $id = $this->pdo->lastInsertId();

        return $id !== false ? (int) $id : false;
    }
}
