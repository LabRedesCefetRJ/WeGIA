<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';

class RelatorioGrupoDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    public function buscarResumoProdutos(int $idGrupo, int $idAlmoxarifado, ?string $dataInicio, ?string $dataFim): array 
    {
        $sql = "
            SELECT
                p.id_produto,
                p.codigo,
                p.descricao AS produto,
                c.descricao_categoria AS categoria,
                u.descricao_unidade AS unidade,
                COALESCE(e.qtd, 0) AS estoque_atual,
                COALESCE(ent.total_entradas, 0) AS total_entradas,
                COALESCE(sai.total_saidas, 0) AS total_saidas

            FROM produto p

            LEFT JOIN categoria_produto c
                ON c.id_categoria_produto = p.id_categoria_produto

            LEFT JOIN unidade u
                ON u.id_unidade = p.id_unidade

            LEFT JOIN estoque e
                ON e.id_produto = p.id_produto
                AND e.id_almoxarifado = :id_almoxarifado_estoque

            LEFT JOIN (
                SELECT
                    ie.id_produto,
                    SUM(ie.qtd) AS total_entradas

                FROM ientrada ie

                INNER JOIN entrada en
                    ON en.id_entrada = ie.id_entrada

                WHERE en.id_almoxarifado = :id_almoxarifado_entrada
                    AND en.ativo = 1
                    AND ie.oculto = false
        ";

        if (!empty($dataInicio)) {
            $sql .= " AND en.data >= :data_inicio_entrada";
        }

        if (!empty($dataFim)) {
            $sql .= " AND en.data <= :data_fim_entrada";
        }

        $sql .= "
                GROUP BY ie.id_produto
            ) ent
                ON ent.id_produto = p.id_produto

            LEFT JOIN (
                SELECT
                    isa.id_produto,
                    SUM(isa.qtd) AS total_saidas

                FROM isaida isa

                INNER JOIN saida s
                    ON s.id_saida = isa.id_saida

                WHERE s.id_almoxarifado = :id_almoxarifado_saida
                    AND s.ativo = 1
                    AND isa.oculto = false
        ";

        if (!empty($dataInicio)) {
            $sql .= " AND s.data >= :data_inicio_saida";
        }

        if (!empty($dataFim)) {
            $sql .= " AND s.data <= :data_fim_saida";
        }

        $sql .= "
                GROUP BY isa.id_produto
            ) sai
                ON sai.id_produto = p.id_produto

            WHERE p.id_grupo_produto = :id_grupo
                AND p.ativo = 1
                AND p.oculto = false

            ORDER BY p.descricao
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_grupo',
            $idGrupo,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_almoxarifado_estoque',
            $idAlmoxarifado,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_almoxarifado_entrada',
            $idAlmoxarifado,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_almoxarifado_saida',
            $idAlmoxarifado,
            PDO::PARAM_INT
        );

        if (!empty($dataInicio)) {
            $stmt->bindValue(
                ':data_inicio_entrada',
                $dataInicio,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':data_inicio_saida',
                $dataInicio,
                PDO::PARAM_STR
            );
        }

        if (!empty($dataFim)) {
            $stmt->bindValue(
                ':data_fim_entrada',
                $dataFim,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':data_fim_saida',
                $dataFim,
                PDO::PARAM_STR
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarNomeGrupo(int $idGrupo): ?string
    {
        $sql = "
            SELECT descricao_grupo
            FROM grupo_produto
            WHERE id_grupo_produto = :id_grupo
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_grupo',
            $idGrupo,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetchColumn();

        return $resultado !== false ? $resultado : null;
    }

    public function buscarNomeAlmoxarifado(int $idAlmoxarifado): ?string 
    {
        $sql = "
            SELECT descricao_almoxarifado
            FROM almoxarifado
            WHERE id_almoxarifado = :id_almoxarifado
                AND ativo = 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_almoxarifado',
            $idAlmoxarifado,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetchColumn();

        return $resultado !== false ? $resultado : null;
    }

    public function buscarEntradasDetalhadas(int $idGrupo, int $idAlmoxarifado, ?string $dataInicio, ?string $dataFim): array 
    {
        $sql = "
            SELECT
                ie.id_produto,
                en.data AS data_entrada,
                en.hora AS hora_entrada,
                ie.qtd AS quantidade_entrada,
                te.descricao AS descricao_tipo_entrada

            FROM ientrada ie

            INNER JOIN entrada en
                ON en.id_entrada = ie.id_entrada

            INNER JOIN produto p
                ON p.id_produto = ie.id_produto

            LEFT JOIN tipo_entrada te
                ON te.id_tipo = en.id_tipo

            WHERE p.id_grupo_produto = :id_grupo
                AND en.id_almoxarifado = :id_almoxarifado
                AND p.ativo = 1
                AND p.oculto = false
                AND en.ativo = 1
                AND ie.oculto = false
        ";

        if (!empty($dataInicio)) {
            $sql .= " AND en.data >= :data_inicio";
        }

        if (!empty($dataFim)) {
            $sql .= " AND en.data <= :data_fim";
        }

        $sql .= "
            ORDER BY
                p.descricao,
                en.data,
                en.hora
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_grupo',
            $idGrupo,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_almoxarifado',
            $idAlmoxarifado,
            PDO::PARAM_INT
        );

        if (!empty($dataInicio)) {
            $stmt->bindValue(
                ':data_inicio',
                $dataInicio,
                PDO::PARAM_STR
            );
        }

        if (!empty($dataFim)) {
            $stmt->bindValue(
                ':data_fim',
                $dataFim,
                PDO::PARAM_STR
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarSaidasDetalhadas(int $idGrupo, int $idAlmoxarifado, ?string $dataInicio, ?string $dataFim): array 
    {
        $sql = "
            SELECT
                isa.id_produto,
                s.data AS data_saida,
                s.hora AS hora_saida,
                isa.qtd AS quantidade_saida,
                ts.descricao AS descricao_tipo_saida

            FROM isaida isa

            INNER JOIN saida s
                ON s.id_saida = isa.id_saida

            INNER JOIN produto p
                ON p.id_produto = isa.id_produto

            LEFT JOIN tipo_saida ts
                ON ts.id_tipo = s.id_tipo

            WHERE p.id_grupo_produto = :id_grupo
                AND s.id_almoxarifado = :id_almoxarifado
                AND p.ativo = 1
                AND p.oculto = false
                AND s.ativo = 1
                AND isa.oculto = false
        ";

        if (!empty($dataInicio)) {
            $sql .= " AND s.data >= :data_inicio";
        }

        if (!empty($dataFim)) {
            $sql .= " AND s.data <= :data_fim";
        }

        $sql .= "
            ORDER BY
                p.descricao,
                s.data,
                s.hora
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_grupo',
            $idGrupo,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_almoxarifado',
            $idAlmoxarifado,
            PDO::PARAM_INT
        );

        if (!empty($dataInicio)) {
            $stmt->bindValue(
                ':data_inicio',
                $dataInicio,
                PDO::PARAM_STR
            );
        }

        if (!empty($dataFim)) {
            $stmt->bindValue(
                ':data_fim',
                $dataFim,
                PDO::PARAM_STR
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}