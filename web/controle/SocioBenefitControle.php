<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SocioBenefitDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';

class SocioBenefitControle
{
    private SocioBenefitDAO $socioBenefitDao;

    public function __construct()
    {
        $this->socioBenefitDao = new SocioBenefitDAO(Conexao::connect());
    }

    /**
     * Retorna um JSON com as regras de benefícios para sócios
     */
    public function getBenefitRules()
    {
        header('Content-Type: application/json');

        try {
            $benefitRules = $this->socioBenefitDao->getBenefitRules();

            if (empty($benefitRules))
                throw new Exception('Nenhuma regra de benefício encontrada.', 404);

            echo json_encode($benefitRules);
        } catch (Exception $e) {
            $logMessage = 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();

            if ($e instanceof PDOException) {
                http_response_code(500);
                $message = 'Erro ao buscar regras de benefício, por favor, tente novamente mais tarde.';
            } else {
                http_response_code($e->getCode() ?: 500);
                $message = $e->getMessage();
            }

            error_log($logMessage);
            echo json_encode(['error' => $message]);
        }
    }

    public function createBenefitRule()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                throw new Exception('Dados de entrada inválidos.', 400);
            }

            if (!isset($data['valuePerPoint'], $data['maxPointsConcurrent'], $data['durationPointMonths'], $data['analysisWindowMonths'], $data['active'])) {
                throw new Exception('Campos obrigatórios ausentes.', 400);
            }

            $socioBenefitRule = new SocioBenefitRule(
                0,
                (float)$data['valuePerPoint'],
                (int)$data['maxPointsConcurrent'],
                (int)$data['durationPointMonths'],
                (int)$data['analysisWindowMonths'],
                (bool)$data['active']
            );


            if ($this->socioBenefitDao->createBenefitRule($socioBenefitRule) === false) {
                throw new Exception('Erro ao criar regra de benefício.', 500);
            }

            echo json_encode(['message' => 'Regra de benefício criada com sucesso!', 'data' => $socioBenefitRule]);
        } catch (Exception $e) {
            $logMessage = 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();

            if ($e instanceof PDOException) {
                http_response_code(500);
                $message = 'Erro ao criar regras de benefício, por favor, tente novamente mais tarde.';
            } else {
                http_response_code($e->getCode() ?: 500);
                $message = $e->getMessage();
            }

            error_log($logMessage);
            echo json_encode(['error' => $message]);
        }
    }

    public function updateBenefitRule()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                throw new Exception('Dados de entrada inválidos.', 400);
            }

            if (!isset($data['id'], $data['valuePerPoint'], $data['maxPointsConcurrent'], $data['durationPointMonths'], $data['analysisWindowMonths'], $data['active'])) {
                throw new Exception('Campos obrigatórios ausentes.', 400);
            }

            $socioBenefitRule = new SocioBenefitRule(
                (float)$data['valuePerPoint'],
                (int)$data['maxPointsConcurrent'],
                (int)$data['durationPointMonths'],
                (int)$data['analysisWindowMonths'],
                (bool)$data['active'],
                (int)$data['id']
            );

            if (!$this->socioBenefitDao->updateBenefitRule($socioBenefitRule)) {
                throw new Exception('Erro ao atualizar regra de benefício.', 500);
            }

            echo json_encode(['message' => 'Regra de benefício atualizada com sucesso!', 'data' => $socioBenefitRule]);
        } catch (Exception $e) {
            $logMessage = 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();

            if ($e instanceof PDOException) {
                http_response_code(500);
                $message = 'Erro ao atualizar regras de benefício, por favor, tente novamente mais tarde.';
            } else {
                http_response_code($e->getCode() ?: 500);
                $message = $e->getMessage();
            }

            error_log($logMessage);
            echo json_encode(['error' => $message]);
        }
    }

    public function deleteBenefitRule()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['id'])) {
                throw new Exception('ID de benefício não fornecido.', 400);
            }

            $id = (int)$data['id'];

            if (!$this->socioBenefitDao->deleteBenefitRule($id)) {
                throw new Exception('Erro ao deletar regra de benefício.', 500);
            }

            echo json_encode(['message' => 'Regra de benefício deletada com sucesso!']);
        } catch (Exception $e) {
            $logMessage = 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();

            if ($e instanceof PDOException) {
                http_response_code(500);
                $message = 'Erro ao deletar regra de benefício, por favor, tente novamente mais tarde.';
            } else {
                http_response_code($e->getCode() ?: 500);
                $message = $e->getMessage();
            }

            error_log($logMessage);
            echo json_encode(['error' => $message]);
        }
    }

    public function activateBenefitRule()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['id'])) {
                throw new Exception('ID de benefício não fornecido.', 400);
            }

            $id = (int)$data['id'];

            if (!$this->socioBenefitDao->activateBenefitRule($id)) {
                throw new Exception('Erro ao ativar regra de benefício.', 500);
            }

            echo json_encode(['message' => 'Regra de benefício ativada com sucesso!']);
        } catch (Exception $e) {
            $logMessage = 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();

            if ($e instanceof PDOException) {
                http_response_code(500);
                $message = 'Erro ao ativar regra de benefício, por favor, tente novamente mais tarde.';
            } else {
                http_response_code($e->getCode() ?: 500);
                $message = $e->getMessage();
            }

            error_log($logMessage);
            echo json_encode(['error' => $message]);
        }
    }

    public function deactivateBenefitRule()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['id'])) {
                throw new Exception('ID de benefício não fornecido.', 400);
            }

            $id = (int)$data['id'];

            if (!$this->socioBenefitDao->deactivateBenefitRule($id)) {
                throw new Exception('Erro ao desativar regra de benefício.', 500);
            }

            echo json_encode(['message' => 'Regra de benefício desativada com sucesso!']);
        } catch (Exception $e) {
            $logMessage = 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();

            if ($e instanceof PDOException) {
                http_response_code(500);
                $message = 'Erro ao desativar regra de benefício, por favor, tente novamente mais tarde.';
            } else {
                http_response_code($e->getCode() ?: 500);
                $message = $e->getMessage();
            }

            error_log($logMessage);
            echo json_encode(['error' => $message]);
        }
    }
}
