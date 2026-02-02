<?php

require_once dirname(__FILE__) . '/Conexao.php';

class PaEtapaDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarPorProcesso(int $idProcesso): array
    {
        $sql = "SELECT 
                    e.id,
                    e.data_inicio,
                    e.data_fim,
                    e.descricao,
                    e.id_status,
                    s.descricao AS status_nome
                FROM pa_etapa e
                JOIN pa_status s ON e.id_status = s.id
                WHERE e.id_processo_aceitacao = :id_processo
                ORDER BY e.data_inicio ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_processo', $idProcesso, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inserirEtapa(
        int $idProcesso,
        int $statusId,
        string $descricao,
        ?string $dataInicio,
        ?string $dataFim
    ): int {
        $sql = "INSERT INTO pa_etapa (data_inicio, data_fim, descricao, id_processo_aceitacao, id_status)
                VALUES (:data_inicio, :data_fim, :descricao, :id_processo, :id_status)";
        $stmt = $this->pdo->prepare($sql);

        $data_inicio = $dataInicio ?: date('Y-m-d');
        $data_fim    = $dataFim ?: null;

        $stmt->bindParam(':data_inicio',  $data_inicio);
        $stmt->bindParam(':data_fim',     $data_fim);
        $stmt->bindParam(':descricao',    $descricao);
        $stmt->bindParam(':id_processo',  $idProcesso, PDO::PARAM_INT);
        $stmt->bindParam(':id_status',    $statusId,   PDO::PARAM_INT);

        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    public function atualizar(int $idEtapa, int $statusId, ?string $dataFim, string $descricao): bool
    {
        $sql = "UPDATE pa_etapa
                SET id_status = :status_id,
                    data_fim  = :data_fim,
                    descricao = :descricao
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':status_id', $statusId, PDO::PARAM_INT);
        $stmt->bindParam(':data_fim',  $dataFim);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':id',        $idEtapa, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function excluir($idEtapa)
    {

        $sqlArq = "DELETE FROM etapa_arquivo WHERE etapa_id = :id";
        $stmtArq = $this->pdo->prepare($sqlArq);
        $stmtArq->bindValue(':id', $idEtapa, PDO::PARAM_INT);
        $stmtArq->execute();


        $sql = "DELETE FROM pa_etapa WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idEtapa, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function buscarPorId(int $idEtapa): ?array
    {
        $sql = "SELECT id, id_processo_aceitacao, data_inicio, data_fim FROM pa_etapa WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idEtapa, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
