<?php

namespace api\Modules\Contribuicao;

use PDO;

class PaymentRepository{
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAllPaymentRules(): array{
        $query = 'SELECT ccr.valor as value, cmp.meio as payment_method, cr.regra as rule
                  FROM contribuicao_conjuntoRegras ccr
                  JOIN contribuicao_meioPagamento cmp ON ccr.id_meioPagamento = cmp.id
                  JOIN contribuicao_regras cr ON ccr.id_regra = cr.id
                  WHERE cmp.status = 1 AND ccr.status = 1';

        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}