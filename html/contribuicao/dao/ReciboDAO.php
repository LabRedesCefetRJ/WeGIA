<?php
require_once '../model/Recibo.php';

class ReciboDAO {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Salvar recibo no banco de dados
     */
    public function salvar(Recibo $recibo) {
        try {
            $sql = "INSERT INTO recibo_emitido (
                codigo, id_socio, email, data_inicio, data_fim, 
                valor_total, total_contribuicoes, data_geracao
            ) VALUES (
                :codigo, :idSocio, :email, :dataInicio, :dataFim,
                :valorTotal, :totalContribuicoes, NOW()
            )";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':codigo' => $recibo->getCodigo(),
                ':idSocio' => $recibo->getIdSocio(),
                ':email' => $recibo->getEmail(),
                ':dataInicio' => $recibo->getDataInicio()->format('Y-m-d'),
                ':dataFim' => $recibo->getDataFim()->format('Y-m-d'),
                ':valorTotal' => $recibo->getValorTotal(),
                ':totalContribuicoes' => $recibo->getTotalContribuicoes()
            ]);
            
            return $this->pdo->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Erro PDO ao salvar recibo: " . $e->getMessage());
            throw new Exception('Erro no banco de dados ao salvar recibo');
        }
    }

    /**
     * Buscar recibo por código
     */
    public function buscarPorCodigo($codigo) {
        try {
            $sql = "SELECT * FROM recibo_emitido WHERE codigo = :codigo";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':codigo' => $codigo]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro PDO ao buscar recibo: " . $e->getMessage());
            return false;
        }
    }
}