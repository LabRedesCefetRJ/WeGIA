<?php

class PaArquivoDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function inserir(int $idProcesso, ?int $idEtapa, int $idPessoaArquivo): bool
    {
        $sql = "INSERT INTO pa_arquivo (id_processo, id_etapa, id_pessoa_arquivo)
                VALUES (:id_processo, :id_etapa, :id_pessoa_arquivo)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_processo', $idProcesso, PDO::PARAM_INT);

        if ($idEtapa === null) {
            $stmt->bindValue(':id_etapa', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':id_etapa', $idEtapa, PDO::PARAM_INT);
        }

        $stmt->bindValue(':id_pessoa_arquivo', $idPessoaArquivo, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function listarPorProcesso(int $idProcesso): array
    {
        $sql = "SELECT pa.id,
                       pa.id_etapa,
                       pa.id_pessoa_arquivo,
                       p.arquivo_nome,
                       p.arquivo_extensao,
                       p.data
                FROM pa_arquivo pa
                JOIN pessoa_arquivo p ON p.id = pa.id_pessoa_arquivo
                WHERE pa.id_processo = :id_processo
                ORDER BY p.data DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_processo', $idProcesso, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarArquivo(int $idPaArquivo): ?array
    {
        $sql = "SELECT p.arquivo_nome, p.arquivo_extensao, p.arquivo
                FROM pa_arquivo pa
                JOIN pessoa_arquivo p ON p.id = pa.id_pessoa_arquivo
                WHERE pa.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idPaArquivo, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function listarIdsPessoaArquivoPorProcesso(int $idProcesso): array
    {
        $sql = "SELECT id_pessoa_arquivo
                FROM pa_arquivo
                WHERE id_processo = :id_processo";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_processo', $idProcesso, PDO::PARAM_INT);
        $stmt->execute();

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function getIdPessoaArquivoById(int $idPaArquivo): ?int
    {
        $sql = "SELECT id_pessoa_arquivo FROM pa_arquivo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idPaArquivo, PDO::PARAM_INT);
        $stmt->execute();

        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public function excluir(int $idPaArquivo): bool
    {
        $sql = "DELETE FROM pa_arquivo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idPaArquivo, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function excluirPorProcesso(int $idProcesso): bool
    {
        $sql = "DELETE FROM pa_arquivo WHERE id_processo = :id_processo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_processo', $idProcesso, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
