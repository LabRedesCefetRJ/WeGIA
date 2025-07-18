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
    public function registrarAtendimento($data) {
        if (
            empty($data['dataAtendimento']) ||
            empty($data['descricaoAtendimento']) ||
            empty($data['medicamento']) || // Cuidado com o nome correto!
            empty($data['idpet'])
        ) {
            echo json_encode(['erro' => 'Preencha todos os campos obrigatórios.']);
            http_response_code(400);
            return;
        }
        $idFichaMedica = $data['idpet'];
        $dataAtendimento = $data['dataAtendimento'];
        $descricaoAtendimento = $data['descricaoAtendimento'];

        $dao = new SaudePetDAO();
        $dao->registrar_atendimento_pet($idFichaMedica, $dataAtendimento, $descricaoAtendimento); // ou use os campos separadamente
    
        echo json_encode(['sucesso' => 'Atendimento registrado com sucesso.']);
    }
    public function obterMedicamentoPet($data){
        if(empty($data['id'] || !isset($data['id']))){
            http_response_code(400);
            die(json_encode(['erro'=>'campo id não preenchido']));
        }
        $id = $data['id'];

        $dao = new SaudePetDAO();
        
        

        $resultado = $dao->obterMedicamento($id);
        die(json_encode($resultado));
    }
    
}