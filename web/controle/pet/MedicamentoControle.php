<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'pet' . DIRECTORY_SEPARATOR . 'SaudePetDAO.php';

class MedicamentoControle
{
    public function adicionarMedicamento()
    {
        $nomeMedicamento = filter_input(INPUT_POST, 'nomeMedicamento', FILTER_SANITIZE_SPECIAL_CHARS);
        $descricaoMedicamento = filter_input(INPUT_POST, 'descricaoMedicamento', FILTER_SANITIZE_SPECIAL_CHARS);
        $aplicacaoMedicamento = filter_input(INPUT_POST, 'aplicacaoMedicamento', FILTER_SANITIZE_SPECIAL_CHARS);
      
        try {
       

            if (!$nomeMedicamento || strlen($nomeMedicamento) <= 0) {
                throw new InvalidArgumentException('O nome do medicamento deve ser informado.', 400);
            }            

            if(!$descricaoMedicamento || strlen($descricaoMedicamento) <= 0){
                throw new InvalidArgumentException('A descrição de um medicamento deve ser informada.', 400);
            }

            if(!$aplicacaoMedicamento || strlen($aplicacaoMedicamento) <= 0){
                throw new InvalidArgumentException('A forma de aplicação de um medicamento deve ser informada.', 400);
            }

            $saudePetDao = new SaudePetDAO();
            $saudePetDao->adicionarMedicamento($nomeMedicamento, $descricaoMedicamento, $aplicacaoMedicamento);

            if ($id) {
                header("Location: ../html/pet/informacao_medicamento.php");
            } else {
                header("Location: ../html/pet/informacao_medicamento.php");
            }
        } catch (Exception $e) {
            error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
            http_response_code($e->getCode());
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }

    public function listarMedicamento()
    {
        $c = new SaudePetDAO();
        return $c->listarMedicamento();
    }
}
