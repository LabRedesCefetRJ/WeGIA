<?php

require_once ROOT . '/classes/OrcamentoDoc.php';
require_once ROOT . '/classes/Arquivo.php';
require_once ROOT . '/dao/Conexao.php';

class OrcamentoDocDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::connect();
    }

    public function incluir(OrcamentoDoc $documento): int
    {
        $arquivo = $documento->getArquivo();
        $conteudoCompactado = gzcompress($arquivo->getConteudo());

        if ($conteudoCompactado === false) {
            throw new RuntimeException(
                'Não foi possível processar o arquivo do orçamento.',
                500
            );
        }

        $sql = 'INSERT INTO orcamento_arquivo
                    (id_orcamento, arquivo_nome, arquivo_extensao, arquivo)
                SELECT
                    :id_orcamento,
                    :arquivo_nome,
                    :arquivo_extensao,
                    :arquivo
                FROM orcamento o
                INNER JOIN cotacao c
                    ON c.id_cotacao = o.id_cotacao
                WHERE o.id_orcamento = :id_orcamento_existente
                AND c.status = :status_atual
                AND NOT EXISTS (
                    SELECT 1
                    FROM orcamento_arquivo oa
                    WHERE oa.id_orcamento = o.id_orcamento
                )';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_orcamento',
            $documento->getId_orcamento(),
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':arquivo_nome',
            $arquivo->getNome(),
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':arquivo_extensao',
            $arquivo->getExtensao(),
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':arquivo',
            $conteudoCompactado,
            PDO::PARAM_LOB
        );

        $stmt->bindValue(
            ':id_orcamento_existente',
            $documento->getId_orcamento(),
            PDO::PARAM_INT
        );

        $stmt->bindValue(':status_atual', 'analise', PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->rowCount() > 0
            ? (int) $this->pdo->lastInsertId()
            : 0;
    }

    public function buscarPorOrcamento($id_orcamento): ?OrcamentoDoc
    {
        $sql = 'SELECT *
                FROM orcamento_arquivo
                WHERE id_orcamento = :id_orcamento';

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

        return $this->montarDocumento($dados);
    }

    public function excluir($id_orcamento): int
    {
        $sql = 'DELETE oa
                FROM orcamento_arquivo oa
                INNER JOIN orcamento o
                    ON o.id_orcamento = oa.id_orcamento
                INNER JOIN cotacao c
                    ON c.id_cotacao = o.id_cotacao
                WHERE oa.id_orcamento = :id_orcamento
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

    private function montarDocumento(array $dados): OrcamentoDoc
    {
        $conteudo = @gzuncompress($dados['arquivo']);

        if ($conteudo === false) {
            throw new RuntimeException(
                'Não foi possível processar o arquivo do orçamento.',
                500
            );
        }

        $arquivo = Arquivo::fromDatabase(
            $conteudo,
            $dados['arquivo_nome'],
            $dados['arquivo_extensao']
        );

        $documento = new OrcamentoDoc(
            $dados['id_orcamento'],
            $arquivo
        );

        $documento->setId_orcamento_arquivo(
            $dados['id_orcamento_arquivo']
        );

        return $documento;
    }
}
