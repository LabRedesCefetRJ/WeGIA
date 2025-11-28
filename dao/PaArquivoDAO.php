<?php

class PaArquivoDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function inserirArquivo(int $idProcesso, int $idEtapa, string $caminho): int
    {
        $sql = "INSERT INTO pa_arquivo (id_processo, id_etapa) VALUES (:id_processo, :id_etapa)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_processo', $idProcesso, PDO::PARAM_INT);
        $stmt->bindParam(':id_etapa', $idEtapa, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }
}
