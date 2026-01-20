<?php
class PaArquivoDAO
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function inserir($idProcesso, $idEtapa, $nome, $extensao, $blob)
    {
        $sql = "INSERT INTO pa_arquivo (id_processo, id_etapa, arquivo_nome, arquivo_extensao, arquivo, data_upload)
                VALUES (:id_processo, :id_etapa, :nome, :ext, :arquivo, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_processo', $idProcesso, PDO::PARAM_INT);
        $stmt->bindValue(':id_etapa', $idEtapa, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindValue(':ext', $extensao, PDO::PARAM_STR);
        $stmt->bindValue(':arquivo', $blob, PDO::PARAM_LOB);
        return $stmt->execute();
    }

    public function listarPorProcesso($idProcesso)
    {
        $sql = "SELECT id, arquivo_nome, arquivo_extensao, data_upload
                FROM pa_arquivo
                WHERE id_processo = :id_processo
                ORDER BY data_upload DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_processo', $idProcesso, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarArquivo($id)
    {
        $sql = "SELECT arquivo_nome, arquivo_extensao, arquivo
                FROM pa_arquivo
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT id, arquivo_nome FROM pa_arquivo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluir($id)
    {
        $sql = "DELETE FROM pa_arquivo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
