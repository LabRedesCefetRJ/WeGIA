<?php

namespace api\modules\Contribuicao;

use PDO;

class ContribuicaoRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(Contribuicao $contribuicao): bool
    {
        $query = "INSERT INTO contribuicao_log (id_socio, id_gateway, id_meio_pagamento, valor, data_pagamento, data_vencimento, data_geracao, status_pagamento, codigo)
                  VALUES (:id_socio, :id_gateway, :id_meio_pagamento, :valor, :data_pagamento, :data_vencimento, :data_geracao, :status_pagamento, :codigo)";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id_socio' => $contribuicao->getIdSocio(),
            ':id_gateway' => $contribuicao->getIdGateway(),
            ':id_meio_pagamento' => $contribuicao->getIdMeioPagamento(),
            ':valor' => $contribuicao->getValor(),
            ':data_pagamento' => $contribuicao->getDataPagamento() ? $contribuicao->getDataPagamento()->format('Y-m-d H:i:s') : null,
            ':data_vencimento' => $contribuicao->getDataVencimento()->format('Y-m-d H:i:s'),
            ':data_geracao' => $contribuicao->getDataGeracao()->format('Y-m-d H:i:s'),
            ':status_pagamento' => $contribuicao->getStatus() === 'paid' ? 1 : 0,
            ':codigo' => $contribuicao->getCodigo()
        ]);
    }

    public function findSociosComPessoas(): array
    {
        $stmt = $this->db->query(
            'SELECT s.id_socio, p.nome, p.sobrenome
             FROM socio s
             INNER JOIN pessoa p ON p.id_pessoa = s.id_pessoa'
        );

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result === false ? [] : $result;
    }

    public function existeContribuicao(int $idSocio, float $valor, string $dataPagamento): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM contribuicao_log
             WHERE id_socio = :id_socio
               AND valor = :valor
               AND data_pagamento = :data_pagamento
             LIMIT 1'
        );
        $stmt->execute([
            ':id_socio' => $idSocio,
            ':valor' => $valor,
            ':data_pagamento' => $dataPagamento,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Find all contributions for a given socio ID
     *
     * @param int $idSocio The socio ID
     * @return array Array of contributions or empty array if none found
     */
    public function findBySocioId(int $idSocio, ?string $dataPagamentoInicial = null, ?string $dataPagamentoFinal = null): array
    {
        $query = "SELECT 
                    cl.id,
                    cl.codigo,
                    cl.valor,
                    cl.data_geracao as dataGeracao,
                    cl.data_vencimento as dataVencimento,
                    cl.data_pagamento as dataPagamento,
                    cl.status_pagamento as statusPagamento,
                    cg.plataforma as plataforma,
                    cm.meio as meioPagamento
                  FROM contribuicao_log cl
                  LEFT JOIN contribuicao_gatewayPagamento cg ON cl.id_gateway = cg.id
                  LEFT JOIN contribuicao_meioPagamento cm ON cl.id_meio_pagamento = cm.id
                  WHERE cl.id_socio = :id_socio";
        
        if ($dataPagamentoInicial !== null) {
            $query .= " AND cl.data_pagamento >= :data_pagamento_inicial";
        }
        
        if ($dataPagamentoFinal !== null) {
            $query .= " AND cl.data_pagamento <= :data_pagamento_final";
        }
        
        $query .= " ORDER BY cl.data_vencimento DESC";
        
        $stmt = $this->db->prepare($query);
        $params = [':id_socio' => $idSocio];
        
        if ($dataPagamentoInicial !== null) {
            $params[':data_pagamento_inicial'] = $dataPagamentoInicial;
        }
        
        if ($dataPagamentoFinal !== null) {
            $params[':data_pagamento_final'] = $dataPagamentoFinal;
        }
        
        $stmt->execute($params);
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result === false ? [] : $result;
    }

    /**
     * Find contributions filtered by status
     *
     * @param int $idSocio The socio ID
     * @param bool|null $statusPagamento The payment status (null = all, true = paid, false = pending)
     * @return array Array of contributions or empty array if none found
     */
    public function findBySocioIdAndStatus(int $idSocio, ?bool $statusPagamento = null): array
    {
        $query = "SELECT 
                    cl.id,
                    cl.codigo,
                    cl.valor,
                    cl.data_geracao as dataGeracao,
                    cl.data_vencimento as dataVencimento,
                    cl.data_pagamento as dataPagamento,
                    cl.status_pagamento as statusPagamento,
                    cg.plataforma as plataforma,
                    cm.meio as meioPagamento
                  FROM contribuicao_log cl
                  LEFT JOIN contribuicao_gatewayPagamento cg ON cl.id_gateway = cg.id
                  LEFT JOIN contribuicao_meioPagamento cm ON cl.id_meio_pagamento = cm.id
                  WHERE cl.id_socio = :id_socio";
        
        if ($statusPagamento !== null) {
            $query .= " AND cl.status_pagamento = :status_pagamento";
        }
        
        $query .= " ORDER BY cl.data_geracao DESC";
        
        $stmt = $this->db->prepare($query);
        $params = [':id_socio' => $idSocio];
        
        if ($statusPagamento !== null) {
            $params[':status_pagamento'] = $statusPagamento ? 1 : 0;
        }
        
        $stmt->execute($params);
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result === false ? [] : $result;
    }

    /**
     * Find a single contribution by its ID and socio ID.
     *
     * @param int $idContribuicao
     * @param int $idSocio
     * @return array|null
     */
    public function findByIdAndSocioId(int $idContribuicao, int $idSocio): ?array
    {
        $query = "SELECT 
                    cl.id,
                    cl.id_socio,
                    cl.codigo,
                    cl.valor,
                    cl.data_geracao as dataGeracao,
                    cl.data_vencimento as dataVencimento,
                    cl.data_pagamento as dataPagamento,
                    cl.status_pagamento as statusPagamento,
                    cg.plataforma as plataforma,
                    cm.meio as meioPagamento
                  FROM contribuicao_log cl
                  LEFT JOIN contribuicao_gatewayPagamento cg ON cl.id_gateway = cg.id
                  LEFT JOIN contribuicao_meioPagamento cm ON cl.id_meio_pagamento = cm.id
                  WHERE cl.id = :id_contribuicao
                    AND cl.id_socio = :id_socio
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':id_contribuicao' => $idContribuicao,
            ':id_socio' => $idSocio
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    /**
     * Get summary of contributions for a socio
     *
     * @param int $idSocio The socio ID
     * @return array Array containing summary data
     */
    public function getSummaryBySocioId(int $idSocio): array
    {
        $query = "SELECT 
                    COUNT(*) as totalContributions,
                    SUM(CASE WHEN status_pagamento = 1 THEN 1 ELSE 0 END) as paidCount,
                    SUM(CASE WHEN status_pagamento = 0 THEN 1 ELSE 0 END) as pendingCount,
                    SUM(CASE WHEN status_pagamento = 1 THEN valor ELSE 0 END) as paidTotal,
                    SUM(CASE WHEN status_pagamento = 0 THEN valor ELSE 0 END) as pendingTotal
                  FROM contribuicao_log
                  WHERE id_socio = :id_socio";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id_socio' => $idSocio]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? [
            'totalContributions' => 0,
            'paidCount' => 0,
            'pendingCount' => 0,
            'paidTotal' => 0,
            'pendingTotal' => 0
        ] : $result;
    }
}
