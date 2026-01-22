<?php
class EtapaArquivoDAO
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function inserir($etapaId, $nome, $extensao, $blob)
    {
        $sql = "INSERT INTO etapa_arquivo (etapa_id, arquivo_nome, arquivo_extensao, arquivo, data_upload)
                VALUES (:etapa_id, :nome, :ext, :arquivo, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':etapa_id', $etapaId, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindValue(':ext', $extensao, PDO::PARAM_STR);
        $stmt->bindValue(':arquivo', $blob, PDO::PARAM_LOB);
        return $stmt->execute();
    }

    public function listarPorEtapa($etapaId)
    {
        $sql = "SELECT id, arquivo_nome, arquivo_extensao, data_upload
                FROM etapa_arquivo
                WHERE etapa_id = :etapa_id
                ORDER BY data_upload DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':etapa_id', $etapaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarArquivo($id)
    {
        $sql = "SELECT arquivo_nome, arquivo_extensao, arquivo
                FROM etapa_arquivo
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT id, arquivo_nome FROM etapa_arquivo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluir($id)
    {
        $sql = "DELETE FROM etapa_arquivo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
