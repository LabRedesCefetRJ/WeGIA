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

    /**
     * @return array{id:int, id_funcionario:int, global:int}|null
     */
    public function buscarModelo(int $id): ?array
    {
        $query = "
            SELECT id, id_funcionario, global
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

    public function salvarModelo(
        string $descricao,
        string $conteudoArquivo,
        string $bodyHtml,
        string $extensao,
        int $idFuncionario,
        bool $global
    ): int|false
    {
        $query = "
            INSERT INTO modelo_documento (id_funcionario, descricao, documento, body_html, extensao, `global`)
            VALUES (:id_funcionario, :descricao, :documento, :body_html, :extensao, :global)
        ";

        $stmt = $this->pdo->prepare($query);
        $executou = $stmt->execute([
            ':id_funcionario' => $idFuncionario,
            ':descricao' => $descricao,
            ':documento' => $conteudoArquivo,
            ':body_html' => $bodyHtml,
            ':extensao' => $extensao,
            ':global' => $global ? 1 : 0,
        ]);

        if (!$executou) {
            return false;
        }

        $id = $this->pdo->lastInsertId();

        return $id !== false ? (int) $id : false;
    }

    public function atualizarModelo(
        int $id,
        string $descricao,
        ?string $conteudoArquivo = null,
        ?string $extensao = null,
        ?string $bodyHtml = null
    ): bool
    {
        if ($conteudoArquivo === null) {
            $query = "
                UPDATE modelo_documento
                SET descricao = :descricao
                WHERE id = :id
            ";
            $parametros = [
                ':descricao' => $descricao,
                ':id' => $id,
            ];
        } else {
            if ($extensao === null || $bodyHtml === null) {
                throw new \InvalidArgumentException('Extensão e conteúdo HTML são obrigatórios ao substituir o arquivo.');
            }

            $query = "
                UPDATE modelo_documento
                SET descricao = :descricao, documento = :documento, body_html = :body_html, extensao = :extensao
                WHERE id = :id
            ";
            $parametros = [
                ':descricao' => $descricao,
                ':documento' => $conteudoArquivo,
                ':body_html' => $bodyHtml,
                ':extensao' => $extensao,
                ':id' => $id,
            ];
        }

        $stmt = $this->pdo->prepare($query);

        return $stmt->execute($parametros);
    }

    public function alterarGlobalidade(int $id, bool $global): bool
    {
        $query = "
            UPDATE modelo_documento
            SET `global` = :global
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($query);

        return $stmt->execute([
            ':global' => $global ? 1 : 0,
            ':id' => $id,
        ]);
    }

    public function excluirModelo(int $id): bool
    {
        $referencia = $this->pdo->prepare("
            SELECT 1
            FROM saude_parecer
            WHERE id_modelo_documento = :id
            LIMIT 1
        ");
        $referencia->execute([':id' => $id]);

        if ($referencia->fetchColumn() !== false) {
            throw new \RuntimeException('Não é possível excluir este modelo porque ele está vinculado a pareceres.', 409);
        }

        try {
            $stmt = $this->pdo->prepare('DELETE FROM modelo_documento WHERE id = :id');

            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1451) {
                throw new \RuntimeException('Não é possível excluir este modelo porque ele está vinculado a pareceres.', 409, $e);
            }

            throw $e;
        }
    }
}
