<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SocioBenefitRule.php';

class SocioBenefitDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Busca as regras de benefícios para sócios no banco de dados
     *
     * @return array Lista de regras de benefícios
     */
    public function getBenefitRules(): array
    {
        $query = "SELECT id, value_per_point, max_points_concurrent, duration_point_months, analysis_window_months, active FROM socio_benefit_rule";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $socioBenefitRules = [];
        foreach ($rules as $rule) {
            $socioBenefitRules[] = new SocioBenefitRule(
                $rule['value_per_point'],
                $rule['max_points_concurrent'],
                $rule['duration_point_months'],
                $rule['analysis_window_months'],
                $rule['active'],
                $rule['id']
            );
        }

        return $socioBenefitRules;
    }

    /**
     * Insere uma nova regra de benefício para sócios no banco de dados
     *
     * @param SocioBenefitRule $rule A regra de benefício a ser inserida
     * @return int O ID da nova regra inserida
     */
    public function createBenefitRule(SocioBenefitRule $rule): int
    {
        $query = "INSERT INTO socio_benefit_rule (value_per_point, max_points_concurrent, duration_point_months, analysis_window_months, active) VALUES (:valuePerPoint, :maxPointsConcurrent, :durationPointMonths, :analysisWindowMonths, :active)";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':valuePerPoint', $rule->getValuePerPoint());
        $stmt->bindValue(':maxPointsConcurrent', $rule->getMaxPointsConcurrent());
        $stmt->bindValue(':durationPointMonths', $rule->getDurationPointMonths());
        $stmt->bindValue(':analysisWindowMonths', $rule->getAnalysisWindowMonths());
        $stmt->bindValue(':active', $rule->isActive(), PDO::PARAM_BOOL);
        $stmt->execute();

        return $this->pdo->lastInsertId() != false ? (int)$this->pdo->lastInsertId() : false;
    }

    /**
     * Atualiza uma regra de benefício existente no banco de dados
     *
     * @param SocioBenefitRule $rule A regra de benefício a ser atualizada
     * @return bool True se a atualização foi bem-sucedida, false caso contrário
     */
    public function updateBenefitRule(SocioBenefitRule $rule): bool
    {
        $query = "UPDATE socio_benefit_rule SET value_per_point = :valuePerPoint, max_points_concurrent = :maxPointsConcurrent, duration_point_months = :durationPointMonths, analysis_window_months = :analysisWindowMonths, active = :active WHERE id = :id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $rule->getId());
        $stmt->bindValue(':valuePerPoint', $rule->getValuePerPoint());
        $stmt->bindValue(':maxPointsConcurrent', $rule->getMaxPointsConcurrent());
        $stmt->bindValue(':durationPointMonths', $rule->getDurationPointMonths());
        $stmt->bindValue(':analysisWindowMonths', $rule->getAnalysisWindowMonths());
        $stmt->bindValue(':active', $rule->isActive(), PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }

    /**
     * Deleta uma regra de benefício do banco de dados
     *
     * @param int $id O ID da regra de benefício a ser deletada
     * @return bool True se a deleção foi bem-sucedida, false caso contrário
     */
    public function deleteBenefitRule(int $id): bool
    {
        $query = "DELETE FROM socio_benefit_rule WHERE id = :id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Ativa uma regra de benefício
     *
     * @param int $id O ID da regra de benefício a ser ativada
     * @return bool True se a operação foi bem-sucedida, false caso contrário
     */
    public function activateBenefitRule(int $id): bool
    {
        $query = "UPDATE socio_benefit_rule SET active = true WHERE id = :id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Desativa uma regra de benefício
     *
     * @param int $id O ID da regra de benefício a ser desativada
     * @return bool True se a operação foi bem-sucedida, false caso contrário
     */
    public function deactivateBenefitRule(int $id): bool
    {
        $query = "UPDATE socio_benefit_rule SET active = false WHERE id = :id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Busca uma regra de benefício específica pelo ID
     *
     * @param int $id O ID da regra de benefício
     * @return SocioBenefitRule|null A regra de benefício ou null se não encontrada
     */
    public function getBenefitRuleById(int $id): ?SocioBenefitRule
    {
        $query = "SELECT id, value_per_point, max_points_concurrent, duration_point_months, analysis_window_months, active FROM socio_benefit_rule WHERE id = :id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rule) {
            return null;
        }

        return new SocioBenefitRule(
            $rule['value_per_point'],
            $rule['max_points_concurrent'],
            $rule['duration_point_months'],
            $rule['analysis_window_months'],
            $rule['active'],
            $rule['id']
        );
    }
}