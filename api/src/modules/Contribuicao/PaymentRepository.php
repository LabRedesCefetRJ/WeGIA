<?php

namespace api\modules\Contribuicao;

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

    public function getActivePaymentMethods(): array
    {
        $query = 'SELECT id, meio FROM contribuicao_meioPagamento WHERE status = 1 ORDER BY meio';
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPaymentGatewayByPaymentMethod(string $paymentMethod): ?PaymentGateway {
        $query = 'SELECT * FROM contribuicao_gatewayPagamento cgp JOIN contribuicao_meioPagamento cmp ON (cgp.id = cmp.id_plataforma) WHERE cmp.meio = :paymentMethod AND cgp.status = 1';
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':paymentMethod', $paymentMethod);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new PaymentGateway(
                $row['plataforma'],
                $row['endPoint'],
                $row['private_token'],
                $row['public_token'],
                (bool)$row['status'],
                $row['id']
            );
        }

        return null;
    }
}
