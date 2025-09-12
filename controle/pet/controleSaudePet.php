<?php
$config_path = "config.php";
if(file_exists($config_path)){
    require_once($config_path);
}else{
    while(true){
        $config_path = "../" . $config_path;
        if(file_exists($config_path)) break;
    }
    require_once($config_path);
}

$SaudePetDAO_path = "dao/pet/SaudePetDAO.php";
if(file_exists($SaudePetDAO_path)){
    require_once($SaudePetDAO_path);
}else{
    while(true){
        $SaudePetDAO_path = "../" . $SaudePetDAO_path;
        if(file_exists($SaudePetDAO_path)) break;
    }
    require_once($SaudePetDAO_path);
}

$SaudePet_path = "classes/pet/SaudePet.php";
if(file_exists($SaudePet_path)){
    require_once($SaudePet_path);
}else{
    while(true){
        $SaudePet_path = "../" . $SaudePet_path;
        if(file_exists($SaudePet_path)) break;
    }
    require_once($SaudePet_path);
}

include_once ROOT."/dao/Conexao.php";

class controleSaudePet
{
    public function verificar(){
        extract($_REQUEST);
            
        if((!isset($nome)) || (empty($nome))){
            $msg = "Nome não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=".$msg);
            return;
        }
        
        if((!isset($castrado)) || (empty($castrado))){
            $msg = "Estado da castração não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=".$msg);
            return;
        }

        $saudePet = new SaudePet($nome,$texto, $castrado);
        
        if((!isset($vacinado)) || (empty($vacinado))){
            $msg = "Estado da vacinação não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=".$msg);
            return;
        }else if( $vacinado == 's' && (!isset($dVacinado) || empty($dVacinado)) ){
            $msg = "Data da vacinação não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=".$msg);
            return;            
        }else if( $vacinado == 's' ){
            $saudePet->setDataVacinado($dVacinado);
        }
        
        if((!isset($vermifugado)) || (empty($vermifugado))){
            $msg = "Estado da vermifugação não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=".$msg);
            return;
        }else if( $vermifugado == 's' && (!isset($dVermifugado) || empty($dVermifugado)) ){
            $msg = "Data da vermifugação não informado!";
            header("Location: ../../html/pet/cadastro_ficha_medica_pet.php?msg=".$msg);
            return;            
        }else if( $vermifugado == 's'){
            $saudePet->setDataVermifugado($dVermifugado);            
        }        

        $senha='null';

        $saudePet->setNome($nome);
        $saudePet->setTexto($texto);
        $saudePet->setCastrado($castrado);
        $saudePet->setVacinado($vacinado);
        $saudePet->setVermifugado($vermifugado);
        
        return $saudePet;
    }

    public function cadastroVacinacao(){
        // Pega os dados enviados via JSON
        $input = json_decode(file_get_contents('php://input'), true);

        $idVacina = $input['idVacina'] ?? null;
        $idFichaMedica = $input['idFichaMedica'] ?? null;
        $dataVacinacao = $input['dataVacinacao'] ?? null;

        // Validação básica
        if (!isset($idVacina) || !isset($idFichaMedica) || !isset($dataVacinacao)) {
            http_response_code(400);
            die(json_encode([
                'status' => 'erro',
                'mensagem' => 'Alguma informação não foi enviada'
            ]));
        }

        $saudePetDao = new SaudePetDao();

        // Chama o método DAO para inserir no banco
        $resultado = $saudePetDao->cadastroVacinacao($idVacina, $idFichaMedica, $dataVacinacao);

        if ($resultado > 0) {
            http_response_code(200);
            die(json_encode([
                'status' => 'sucesso',
                'mensagem' => 'Vacinação registrada com sucesso'
            ]));
        } else {
            http_response_code(500);
            die(json_encode([
                'status' => 'erro',
                'mensagem' => 'Erro ao registrar vacinação no banco'
            ]));
        }
    }


    public function cadastroVacina(){
        $input = json_decode(file_get_contents('php://input'), true);
        $nome = $input['nomeVacina'];
        $marca = $input['marcaVacina'];
        
       if (!isset($nome) || !isset($marca)) {
        http_response_code(400);
        die(json_encode([
            'status' => 'erro',
            'mensagem' => 'Alguma informação não foi enviada'
        ]));
    }

    $saudePetDao = new SaudePetDao();
    if ($saudePetDao->cadastroVacina($nome, $marca) > 0) {
        http_response_code(200);
        die(json_encode([
            'status' => 'sucesso',
            'mensagem' => 'Informação Inserida com Sucesso'
        ]));
    } else {
        http_response_code(500);
        die(json_encode([
            'status' => 'erro',
            'mensagem' => 'Erro ao inserir no banco'
        ]));
    }

    }

    public function listarVacina(){
        
        
        header("Content-Type: application/json; charset=utf-8");
        
        $saudePetDao = new SaudePetDAO();

        

        $registros = $saudePetDao->listarVacina();
        http_response_code(200);
        echo json_encode($registros, JSON_UNESCAPED_UNICODE);
    
    }

    public function cadastroVermifugacao(){
    $input = json_decode(file_get_contents('php://input'), true);

    $idVermifugo = $input['idVermifugo'] ?? null;
    $idFichaMedica = $input['idFichaMedica'] ?? null;
    $dataVermifugacao = $input['dataVermifugacao'] ?? null;

    if (!isset($idVermifugo) || !isset($idFichaMedica) || !isset($dataVermifugacao)) {
        http_response_code(400);
        die(json_encode([
            'status' => 'erro',
            'mensagem' => 'Alguma informação não foi enviada'
        ]));
    }

    $saudePetDao = new SaudePetDao();
    $resultado = $saudePetDao->cadastroVermifugacao($idVermifugo, $idFichaMedica, $dataVermifugacao);

    if ($resultado > 0) {
        http_response_code(200);
        die(json_encode([
            'status' => 'sucesso',
            'mensagem' => 'Vermifugação registrada com sucesso'
        ]));
    } else {
        http_response_code(500);
        die(json_encode([
            'status' => 'erro',
            'mensagem' => 'Erro ao registrar vermifugacao no banco'
        ]));
    }
}

public function cadastroVermifugo(){
    $input = json_decode(file_get_contents('php://input'), true);
    $nome = $input['nomeVermifugo'] ?? null;
    $marca = $input['marcaVermifugo'] ?? null;

    if (!isset($nome) || !isset($marca)) {
        http_response_code(400);
        die(json_encode([
            'status' => 'erro',
            'mensagem' => 'Alguma informação não foi enviada'
        ]));
    }

    $saudePetDao = new SaudePetDao();
    if ($saudePetDao->cadastroVermifugo($nome, $marca) > 0) {
        http_response_code(200);
        die(json_encode([
            'status' => 'sucesso',
            'mensagem' => 'Informação inserida com sucesso'
        ]));
    } else {
        http_response_code(500);
        die(json_encode([
            'status' => 'erro',
            'mensagem' => 'Erro ao inserir no banco'
        ]));
    }
}

public function listarVermifugo(){
    header("Content-Type: application/json; charset=utf-8");
    
    $saudePetDao = new SaudePetDAO();
    $registros = $saudePetDao->listarVermifugo();

    http_response_code(200);
    echo json_encode($registros, JSON_UNESCAPED_UNICODE);
}

    
    public function listarTodos(){
        extract($_REQUEST);
        $SaudePetDAO = new SaudePetDAO();
        $pets = $SaudePetDAO->listarTodos();
        session_start();
        $_SESSION['saudepet']=$pets;
        header('Location: '.$nextPage);
    }

    public function incluir(){
        $pet = $this->verificar();
        $intDAO = new SaudePetDAO();
        try{
            $idSaudePet=$intDAO->incluir($pet);
            $_SESSION['msg']="Ficha médica cadastrada com sucesso!";
            $_SESSION['proxima']="Cadastrar outra ficha.";
            $_SESSION['link']="../html/saude/cadastro_ficha_medica_pet.php";
        } catch (PDOException $e){
            $msg= "Não foi possível registrar o paciente <form> <input type='button' value='Voltar' onClick='history.go(-1)'> </form>"."<br>".$e->getMessage();
            echo $msg;
        }
    }

    public function getPet($id){
        $saudePetDAO = new SaudePetDAO();
        return $saudePetDAO->getPet($id);
    }
    public function getFichaMedicaPet() {
        extract($_REQUEST);
        $idPet = $_REQUEST['idPet'];
        $saudePetDAO = new SaudePetDAO();
    
        header('Content-Type: application/json; charset=utf-8');
    
        $dados = $saudePetDAO->getFichaMedicaPet($idPet);
    
        if (!$dados) {
            // sempre retorna um objeto JSON válido
            $dados = "";
                
        }
    
        echo json_encode($dados);
    }
  
    

    public function fichaMedicaPetExiste($id){
        $saudePetDAO = new SaudePetDAO();
        return $saudePetDAO->fichaMedicaPetExiste($id);
    }
    public function modificarFichaMedicaPet() {
        $input = json_decode(file_get_contents('php://input'), true);
    
        $id_pet = $input['id_pet'] ?? null;
        $descricao = $input['necessidadesEspeciais'] ?? null;
        $castrado = $input['castrado'] ?? null;
    
        $saudePetDAO = new SaudePetDAO();
        $ok = $saudePetDAO->modificarFichaMedicaPet($id_pet, $descricao, $castrado);
    
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "status"   => $ok ? "sucesso" : "erro",
            "redirect" => "../../html/pet/profile_pet.php?id_pet=".$id_pet
        ]);
        exit;
    }
    
    
    public function getHistoricoPet(){
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        $saudePetDAO = new SaudePetDAO();
    
        $resultado = $saudePetDAO->getHistoricoPet($data['idpet']);
    
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado);
        exit;
    }
    
    public function getAtendimentoPet($id){
        $saudePetDAO = new SaudePetDAO();
        return $saudePetDAO->getAtendimentoPet($id);
    }

    public function dataAplicacao($dados){
        //data|id
        $saudePetDAO = new SaudePetDAO();
        return $saudePetDAO->dataAplicacao($dados);
    }

    public function getHistoricoVacinacao(){
        $input = json_decode(file_get_contents('php://input'), true);
        $saudePetDAO = new SaudePetDAO();
        echo json_encode($saudePetDAO->getHistoricoVacinacao($input['idpet']), JSON_UNESCAPED_UNICODE);

        
    }
    public function getHistoricoVermifugacao(){
    $input = json_decode(file_get_contents('php://input'), true);
    $saudePetDAO = new SaudePetDAO();
    echo json_encode($saudePetDAO->getHistoricoVermifugacao($input['idpet']), JSON_UNESCAPED_UNICODE);
}

}