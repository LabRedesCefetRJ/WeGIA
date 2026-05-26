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

    /**
     * Find all contributions for a given socio ID
     *
     * @param int $idSocio The socio ID
     * @return array Array of contributions or empty array if none found
     */
    public function findBySocioId(int $idSocio): array
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
                  WHERE cl.id_socio = :id_socio
                  ORDER BY cl.data_geracao DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id_socio' => $idSocio]);
        
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
