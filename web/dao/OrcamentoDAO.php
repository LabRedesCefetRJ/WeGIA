<?php

require_once ROOT . '/classes/Orcamento.php';
require_once ROOT . '/dao/Conexao.php';

class OrcamentoDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::connect();
    }

    public function incluir(Orcamento $orcamento): int
    {
        $this->validarFornecedorDisponivel($orcamento);

        $sql = 'INSERT INTO orcamento
                    (id_cotacao, id_fornecedor, prazo_entrega, valor)
                SELECT
                    :id_cotacao,
                    :id_fornecedor,
                    :prazo_entrega,
                    :valor
                FROM cotacao c
                WHERE c.id_cotacao = :id_cotacao_existente
                AND c.status = :status_atual
                AND (
                    SELECT COUNT(*)
                    FROM orcamento o
                    WHERE o.id_cotacao = c.id_cotacao
                ) < :quantidade_maxima';

        $stmt = $this->pdo->prepare($sql);

        $this->vincularDados($stmt, $orcamento);

        $stmt->bindValue(
            ':id_cotacao_existente',
            $orcamento->getId_cotacao(),
            PDO::PARAM_INT
        );

        $stmt->bindValue(':status_atual', 'analise', PDO::PARAM_STR);
        $stmt->bindValue(':quantidade_maxima', 3, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount() > 0
            ? (int) $this->pdo->lastInsertId()
            : 0;
    }

    public function editar(Orcamento $orcamento): int
    {
        $this->validarFornecedorDisponivel($orcamento);

        $sql = 'UPDATE orcamento o
                INNER JOIN cotacao c
                    ON c.id_cotacao = o.id_cotacao
                SET o.id_cotacao = :id_cotacao,
                    o.id_fornecedor = :id_fornecedor,
                    o.prazo_entrega = :prazo_entrega,
                    o.valor = :valor
                WHERE o.id_orcamento = :id_orcamento
                AND o.id_cotacao = :id_cotacao_atual
                AND c.status = :status_atual';

        $stmt = $this->pdo->prepare($sql);

        $this->vincularDados($stmt, $orcamento);

        $stmt->bindValue(
            ':id_orcamento',
            $orcamento->getId_orcamento(),
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_cotacao_atual',
            $orcamento->getId_cotacao(),
            PDO::PARAM_INT
        );

        $stmt->bindValue(':status_atual', 'analise', PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function excluir($id_orcamento): int
    {
        $sql = 'DELETE o
                FROM orcamento o
                INNER JOIN cotacao c
                    ON c.id_cotacao = o.id_cotacao
                INNER JOIN orcamento outro
                    ON outro.id_cotacao = o.id_cotacao
                    AND outro.id_orcamento <> o.id_orcamento
                WHERE o.id_orcamento = :id_orcamento
                AND c.status = :status_atual';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_orcamento',
            $id_orcamento,
            PDO::PARAM_INT
        );

        $stmt->bindValue(':status_atual', 'analise', PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function buscarPorId($id_orcamento, $bloquear = false): ?Orcamento
    {
        $sql = 'SELECT *
                FROM orcamento
                WHERE id_orcamento = :id_orcamento';

        if ($bloquear) {
            if (!$this->pdo->inTransaction()) {
                throw new LogicException('O bloqueio do orçamento exige uma transação.');
            }
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_orcamento',
            $id_orcamento,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados === false) {
            return null;
        }

        return $this->montarOrcamento($dados);
    }

    public function listarDetalhesPorCotacao($id_cotacao): array
    {
        $sql = 'SELECT
                    o.id_orcamento,
                    o.id_cotacao,
                    o.id_fornecedor,
                    o.prazo_entrega,
                    o.valor,
                    origem.nome_origem AS fornecedor,
                    oa.id_orcamento_arquivo,
                    oa.arquivo_nome,
                    oa.arquivo_extensao
                FROM orcamento o
                INNER JOIN origem
                    ON origem.id_origem = o.id_fornecedor
                LEFT JOIN orcamento_arquivo oa
                    ON oa.id_orcamento = o.id_orcamento
                WHERE o.id_cotacao = :id_cotacao
                ORDER BY o.id_orcamento';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_cotacao',
            $id_cotacao,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarPorCotacao($id_cotacao): int
    {
        $sql = 'SELECT COUNT(*) AS quantidade
                FROM orcamento
                WHERE id_cotacao = :id_cotacao';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_cotacao',
            $id_cotacao,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $resultado['quantidade'];
    }

    private function validarFornecedorDisponivel(Orcamento $orcamento): void
    {
        if (!$this->pdo->inTransaction()) {
            throw new LogicException('A gravação de orçamento exige uma transação.');
        }

        // Serializa inclusões e edições da mesma cotação antes de consultar fornecedores.
        $lock = $this->pdo->prepare('SELECT id_cotacao FROM cotacao WHERE id_cotacao = :id FOR UPDATE');
        $lock->execute([':id' => $orcamento->getId_cotacao()]);
        $stmt = $this->pdo->prepare(
            'SELECT id_orcamento FROM orcamento
             WHERE id_cotacao = :cotacao AND id_fornecedor = :fornecedor
             AND id_orcamento <> :atual FOR UPDATE'
        );
        $stmt->execute([
            ':cotacao' => $orcamento->getId_cotacao(),
            ':fornecedor' => $orcamento->getId_fornecedor(),
            ':atual' => $orcamento->getId_orcamento() ?? 0
        ]);
        if ($stmt->fetchColumn() !== false) {
            throw new InvalidArgumentException('Este fornecedor já possui um orçamento nesta cotação. Escolha outro fornecedor.', 409);
        }
    }

    private function vincularDados(PDOStatement $stmt, Orcamento $orcamento): void
    {
        $stmt->bindValue(
            ':id_cotacao',
            $orcamento->getId_cotacao(),
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_fornecedor',
            $orcamento->getId_fornecedor(),
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':prazo_entrega',
            $orcamento->getPrazo_entrega(),
            $orcamento->getPrazo_entrega() === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':valor',
            $orcamento->getValor(),
            PDO::PARAM_STR
        );
    }

    private function montarOrcamento(array $dados): Orcamento
    {
        $orcamento = new Orcamento(
            $dados['id_cotacao'],
            $dados['id_fornecedor'],
            $dados['prazo_entrega'],
            $dados['valor']
        );

        $orcamento->setId_orcamento($dados['id_orcamento']);

        return $orcamento;
    }
}
