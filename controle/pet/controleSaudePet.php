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

    public function getFichaMedicaPet($id){
        $saudePetDAO = new SaudePetDAO();
        return $saudePetDAO->getFichaMedicaPet($id);
    }

    public function fichaMedicaPetExiste($id){
        $saudePetDAO = new SaudePetDAO();
        return $saudePetDAO->fichaMedicaPetExiste($id);
    }

    public function modificarFichaMedicaPet(){
        $saudePetDAO = new SaudePetDAO();
        echo $saudePetDAO->modificarFichaMedicaPet($_REQUEST);
        
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
}