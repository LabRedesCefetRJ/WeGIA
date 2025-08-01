<?php 

$PetDAO_path = "dao/pet/SaudePetDAO.php";
if(file_exists($PetDAO_path)){
    require_once($PetDAO_path);
}else{
    while(true){
        $PetDAO_path = "../" . $PetDAO_path;
        if(file_exists($PetDAO_path)) break;
    }
    require_once($PetDAO_path);
}

class AtendimentoControle{
    public function registrarAtendimento() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        if (
            empty($data['dataAtendimento']) ||
            empty($data['descricaoAtendimento']) || 
            empty($data['idpet'])
        ) {
            echo json_encode(['erro' => 'Preencha todos os campos obrigatórios.']);
            http_response_code(400);
            return;
        }
    
        if (!array_key_exists('medicamentos', $data) || !is_array($data['medicamentos'])) {
            echo json_encode(['erro' => 'Campo "medicamentos" deve ser um array.']);
            http_response_code(400);
            return;
        }
    
        $idPet = (int)$data['idpet'];
        $dataAtendimento = trim($data['dataAtendimento']);
        $descricaoAtendimento = trim($data['descricaoAtendimento']);
        $medicamentos = $data['medicamentos']; // pode ser vazio
    
        $dao = new SaudePetDAO();
    
        try {
            $dao->registrar_atendimento_pet($idPet, $dataAtendimento, $descricaoAtendimento, $medicamentos);
            echo json_encode(['sucesso' => 'Atendimento registrado com sucesso.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao registrar atendimento: ' . $e->getMessage()]);
        }
    }
    
    public function obterMedicamentoPet() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        if (!isset($data['id']) || empty($data['id'])) {
            http_response_code(400);
            die(json_encode(['erro' => 'campo id não preenchido']));
        }
    
        $id = $data['id'];
    
        $dao = new SaudePetDAO();
        $resultado = $dao->obterMedicamento($id);
    
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
    }
}    