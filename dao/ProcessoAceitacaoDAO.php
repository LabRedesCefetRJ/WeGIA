<?php

class ProcessoAceitacaoDAO
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Cria um processo de aceitação inicial para a pessoa informada.
     * 
     * @param int $id_pessoa ID da pessoa vinculada ao processo.
     * @param int $id_status Opcional, status inicial padrão 1.
     * @param string|null $descricao Opcional descrição inicial do processo.
     * @return int ID do processo criado.
     * @throws PDOException Em caso de erro no banco.
     */
    public function criarProcessoInicial(int $id_pessoa, int $id_status = 1, string $descricao = 'Processo de aceitação inicial'): int
    {
        $data_inicio = date('Y-m-d H:i:s');
        $data_fim = null; // processo em andamento

        $sql = "
            INSERT INTO processo_aceitacao (data_inicio, data_fim, descricao, id_status, id_pessoa)
            VALUES (:data_inicio, :data_fim, :descricao, :id_status, :id_pessoa)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':id_status', $id_status, PDO::PARAM_INT);
        $stmt->bindParam(':id_pessoa', $id_pessoa, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new PDOException("Erro ao criar processo de aceitação.");
        }

        return (int)$this->pdo->lastInsertId();
    }

    public function listarProcessosAtivos(): array
    {
        $sql = "
        SELECT 
            p.id_pessoa,
            p.nome,
            p.sobrenome,
            p.cpf,
            s.descricao AS status,
            pa.id,
            pa.id_status 
        FROM processo_aceitacao pa
        JOIN pessoa p ON pa.id_pessoa = p.id_pessoa
        JOIN pa_status s ON pa.id_status = s.id
        WHERE pa.data_fim IS NULL
        ORDER BY pa.data_inicio DESC
    ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarResumoPorId(int $idProcesso): ?array
    {
        $sql = "
        SELECT 
            pa.id,
            pa.id_pessoa,
            pa.id_status,
            p.nome,
            p.sobrenome
        FROM processo_aceitacao pa
        JOIN pessoa p ON pa.id_pessoa = p.id_pessoa
        WHERE pa.id = :id
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $idProcesso, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function atualizarStatus(int $idProcesso, int $idStatus): bool
    {
        $sql = "UPDATE processo_aceitacao
            SET id_status = :id_status
            WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_status', $idStatus, PDO::PARAM_INT);
        $stmt->bindParam(':id',        $idProcesso, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function buscarPorIdConcluido(int $idProcesso): ?array
    {
        $sql = "
        SELECT pa.*, s.descricao AS status
        FROM processo_aceitacao pa
        JOIN pa_status s ON pa.id_status = s.id
        WHERE pa.id = :id
          AND UPPER(TRIM(s.descricao)) = 'CONCLUÍDO'
        LIMIT 1
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $idProcesso, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getIdPessoaByProcesso(int $idProcesso): int
    {
        $sql = "SELECT id_pessoa
            FROM processo_aceitacao
            WHERE id = :id
            LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idProcesso, PDO::PARAM_INT);
        $stmt->execute();

        $idPessoa = $stmt->fetchColumn();

        if (!$idPessoa) {
            throw new RuntimeException('Processo não encontrado ou sem pessoa vinculada.');
        }

        return (int)$idPessoa;
    }

    public function getByStatus(int $status){
        $query = 'SELECT 
            p.id_pessoa,
            p.nome,
            p.sobrenome,
            p.cpf,
            s.descricao AS status,
            pa.id,
            pa.id_status 
        FROM processo_aceitacao pa
        JOIN pessoa p ON pa.id_pessoa = p.id_pessoa
        JOIN pa_status s ON pa.id_status = s.id
        WHERE pa.id_status = :idStatus
        ORDER BY p.nome ASC';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':idStatus', $status);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
