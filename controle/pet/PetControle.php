<?php
$PetDAO_path = "dao/pet/PetDAO.php";
if(file_exists($PetDAO_path)){
    require_once($PetDAO_path);
}else{
    while(true){
        $PetDAO_path = "../" . $PetDAO_path;
        if(file_exists($PetDAO_path)) break;
    }
    require_once($PetDAO_path);
}
//require_once '../../dao/pet/PetDAO.php';

$Pet_path = "classes/pet/Pet.php";
if(file_exists($Pet_path)){
    require_once($Pet_path);
}else{
    while(true){
        $Pet_path = "../" . $Pet_path;
        if(file_exists($Pet_path)) break;
    }
    require_once($Pet_path);
}
//require_once '../../classes/pet/Pet.php';

class PetControle{
    private $petDAO;
    private $petClasse;

    public function __construct(){
       $this->petDAO = new PetDAO();
       $this->petClasse = new PetClasse();
    }

    public function incluir() {
        extract($_REQUEST);
        
        $sexo = strtoupper($gender);
    
        // Validações
        if (!isset($nome) || empty($nome)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Nome não informado!");
            return;
        }
    
        if (!isset($nascimento) || empty($nascimento)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Data de nascimento não informada!");
            return;
        }
    
        if (!isset($acolhimento) || empty($acolhimento)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Data de acolhimento não informada!");
            return;
        }
    
        if (!isset($sexo) || empty($sexo)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Sexo não informado!");
            return;
        }
    
        if (!isset($especie) || empty($especie)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Espécie não informada!");
            return;
        }
    
        if (!isset($raca) || empty($raca)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Raça não informada!");
            return;
        }
    
        if (!isset($cor) || empty($cor)) {
            header("Location: ../../html/pet/cadastro_pet.php?msg=Cor não informada!");
            return;
        }
    
        if (!isset($caracEsp)) {
            $caracEsp = '';
        }
    
        // Trata a imagem de perfil
        $imgperfil = '';
        $nomeImagem = ['', ''];
    
        if (isset($_FILES['imgperfil']) && $_FILES['imgperfil']['error'] == UPLOAD_ERR_OK) {
            $tmpName = $_FILES['imgperfil']['tmp_name'];
            $imgperfil = base64_encode(file_get_contents($tmpName));
    
            $nomeImagemCompleto = $_FILES['imgperfil']['name'];
            $nomeImagem = explode('.', $nomeImagemCompleto);
    
            if (count($nomeImagem) < 2) {
                $nomeImagem[1] = ''; // extensão vazia se não houver
            }
        }
    
        // Define dados no objeto
        $this->petClasse->setNome($nome);
        $this->petClasse->setNascimento($nascimento);
        $this->petClasse->setAcolhimento($acolhimento);
        $this->petClasse->setSexo($sexo);
        $this->petClasse->setCaracteristicasEspecificas($caracEsp);
        $this->petClasse->setEspecie($especie);
        $this->petClasse->setRaca($raca);
        $this->petClasse->setCor($cor);
        $this->petClasse->setImgPerfil($imgperfil);
        $this->petClasse->setNomeImagem($nomeImagem);
    
        // Salva no banco
        $this->petDAO->adicionarPet(
            $this->petClasse->getNome(),
            $this->petClasse->getNascimento(),
            $this->petClasse->getAcolhimento(),
            $this->petClasse->getSexo(),
            $this->petClasse->getCaracteristicasEspecificas(),
            $this->petClasse->getEspecie(),
            $this->petClasse->getRaca(),
            $this->petClasse->getCor(),
            $this->petClasse->getImgPerfil(),
            $this->petClasse->getNomeImagem()
        );
    
    }
    
    public function listarTodos(){
        extract($_REQUEST);
        $PetDAO= new PetDAO();
        $pets = $PetDAO->listarTodos();
        $_SESSION['pets']=$pets;
        //header('Location: '.$nextPage);
    }

    public function listarUmPet(){
        extract($_REQUEST);
        //try {
            $petDAO = new PetDAO();
            $pet=$petDAO->listarUm($idPet);
            die(json_encode($pet));
        //} catch (PDOException $e) {
        //    echo $e->getMessage();
        //}
    }
    public function listarUm(){
        extract($_REQUEST);
        //try {
            $petDAO = new PetDAO();
            $pet=$petDAO->listarUm($id_pet);
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['pet']=$pet;
            header('Location:'.$nextPage);
        //} catch (PDOException $e) {
        //    echo $e->getMessage();
        //}
    }

    public function alterarImagem(){
        extract($_REQUEST);
        $imgPet = base64_encode(file_get_contents($_FILES['imgperfil']['tmp_name']));
        $imgNome = $_FILES['imgperfil']['name'];
        $imgNome = explode('.', $imgNome);  
        
        try{
            $petDAO = new PetDAO();
            $petDAO->alterarFotoPet($imgPet, $imgNome[0], $imgNome[1], $id_foto, $id_pet);
            header('Location: ../../html/pet/profile_pet.php?id_pet='.$id_pet);            
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    public function alterarPetDados(){
        extract($_REQUEST);

        $sexo = strtoupper($gender);
        try{
            $petDAO = new PetDAO();
            $petDAO->alterarPet($nome, $nascimento, $acolhimento, $sexo, $especificas, $especie, $raca, $cor, $id_pet);
            header('Location: ../../html/pet/profile_pet.php?id_pet='.$id_pet);   
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    public function incluirExamePet(){
        extract($_REQUEST);
        $nameFile = explode(".", $_FILES['arquivo']['name']);
        $arquivoExame = base64_encode(file_get_contents($_FILES['arquivo']['tmp_name']));
        //$arquivoExame = base64_encode(gzcompress(file_get_contents($_FILES['arquivo']['tmp_name'])));
        $dataExame = date("y-m-d");
        var_dump($_POST, $_FILES);

        try{
            $petDAO = new PetDAO();
            $petDAO->incluirExamePet( $id_ficha_medica, $id_tipo_exame, $dataExame, $arquivoExame, $nameFile);
            header("location: ../../html/pet/profile_pet.php?id_pet=".$id_pet);
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    public function listarPets(){
        
        header("Content-Type: application/json; charset=utf-8");
        
        $petDao = new PetDAO();

        

        $registros = $petDao->listarPets();
        http_response_code(200);
        echo json_encode($registros, JSON_UNESCAPED_UNICODE);
    }
}

?>