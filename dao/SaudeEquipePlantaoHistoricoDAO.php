<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';

class SaudeEquipePlantaoHistoricoDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::connect();
    }

    public function listarHistoricoEscalasMensais(?int $limite = 120): array
    {
        $limite = is_null($limite) || $limite < 1 ? 120 : min($limite, 360);

        $sql = "SELECT
                    sem.id_escala_mensal,
                    sem.ano,
                    sem.mes,
                    sem.bloqueada,
                    sem.observacao,
                    sem.data_criacao,
                    sem.data_atualizacao,
                    COUNT(sed.id_escala_dia) AS quantidade_turnos_definidos,
                    COUNT(DISTINCT sed.dia) AS quantidade_dias_com_escala
                FROM saude_escala_mensal sem
                LEFT JOIN saude_escala_dia sed ON sed.id_escala_mensal = sem.id_escala_mensal
                GROUP BY
                    sem.id_escala_mensal,
                    sem.ano,
                    sem.mes,
                    sem.bloqueada,
                    sem.observacao,
                    sem.data_criacao,
                    sem.data_atualizacao
                ORDER BY sem.ano DESC, sem.mes DESC
                LIMIT :limite";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
