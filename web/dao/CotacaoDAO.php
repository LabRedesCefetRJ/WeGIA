<?php

require_once ROOT . '/classes/Cotacao.php';
require_once ROOT . '/dao/Conexao.php';

class CotacaoDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::connect();
    }

    public function incluir(Cotacao $cotacao): int
    {
        $sql = 'INSERT INTO cotacao
                    (id_responsavel, id_orcamento_escolhido, status, descricao, justificativa)
                VALUES
                    (:id_responsavel, :id_orcamento_escolhido, :status, :descricao, :justificativa)';

        $stmt = $this->pdo->prepare($sql);
        $this->vincularDados($stmt, $cotacao);
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function excluir($id_cotacao): int
    {
        $sql = "DELETE FROM cotacao
                WHERE id_cotacao = :id_cotacao AND status = 'analise'
                AND id_orcamento_escolhido IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_cotacao', $id_cotacao, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    private function vincularDados(PDOStatement $stmt, Cotacao $cotacao): void
    {
        $stmt->bindValue(':id_responsavel', $cotacao->getId_responsavel(), PDO::PARAM_INT);
        $stmt->bindValue(
            ':id_orcamento_escolhido',
            $cotacao->getId_orcamento_escolhido(),
            $cotacao->getId_orcamento_escolhido() === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );
        $stmt->bindValue(':status', $cotacao->getStatus(), $cotacao->getStatus() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $cotacao->getDescricao(), $cotacao->getDescricao() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':justificativa', $cotacao->getJustificativa(), $cotacao->getJustificativa() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }

    public function listarEmAndamento(): array
    {
        $sql = 'SELECT c.*,
                    p.nome,
                    p.sobrenome
                FROM cotacao c
                INNER JOIN pessoa p
                    ON p.id_pessoa = c.id_responsavel
                WHERE c.status = :status
                ORDER BY c.id_cotacao DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':status', 'analise', PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarHistorico(): array
    {
        $sql = 'SELECT c.*,
                    p.nome,
                    p.sobrenome
                FROM cotacao c
                INNER JOIN pessoa p
                    ON p.id_pessoa = c.id_responsavel
                WHERE c.status = :concluido
                ORDER BY c.id_cotacao DESC';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':concluido',
            'concluido',
            PDO::PARAM_STR
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id_cotacao): ?array
    {
        $sql = "SELECT c.*,
                    p.nome,
                    p.sobrenome
                FROM cotacao c
                INNER JOIN pessoa p
                    ON p.id_pessoa = c.id_responsavel
                WHERE c.id_cotacao = :id_cotacao";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':id_cotacao', $id_cotacao, PDO::PARAM_INT);
        $stmt->execute();

        $cotacao = $stmt->fetch(PDO::FETCH_ASSOC);

        return $cotacao !== false ? $cotacao : null;
    }

    public function buscarPorIdParaAtualizacao($id_cotacao): ?array
    {
        if (!$this->pdo->inTransaction()) {
            throw new LogicException('O bloqueio da cotação exige uma transação.');
        }

        $stmt = $this->pdo->prepare(
            'SELECT * FROM cotacao WHERE id_cotacao = :id_cotacao FOR UPDATE'
        );
        $stmt->bindValue(':id_cotacao', $id_cotacao, PDO::PARAM_INT);
        $stmt->execute();
        $cotacao = $stmt->fetch(PDO::FETCH_ASSOC);

        return $cotacao !== false ? $cotacao : null;
    }

    public function escolherOrcamento($id_cotacao, $id_orcamento, $justificativa): int
    {
        $sql = 'UPDATE cotacao
                SET id_orcamento_escolhido = :id_orcamento,
                    justificativa = :justificativa,
                    status = :status
                WHERE id_cotacao = :id_cotacao
                AND status = :status_atual
                AND EXISTS (
                    SELECT 1
                    FROM orcamento o
                    WHERE o.id_orcamento = :id_orcamento_pertencente
                    AND o.id_cotacao = cotacao.id_cotacao
                )';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':id_orcamento', $id_orcamento, PDO::PARAM_INT);

        $stmt->bindValue(':justificativa', $justificativa, $justificativa === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        $stmt->bindValue(':status', 'concluido', PDO::PARAM_STR);

        $stmt->bindValue(':status_atual', 'analise', PDO::PARAM_STR);

        $stmt->bindValue(':id_cotacao', $id_cotacao, PDO::PARAM_INT);

        $stmt->bindValue(':id_orcamento_pertencente', $id_orcamento, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}
