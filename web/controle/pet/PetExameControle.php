<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

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

$Pet_path = "classes/pet/PetExame.php";
if(file_exists($Pet_path)){
    require_once($Pet_path);
}else{
    while(true){
        $Pet_path = "../" . $Pet_path;
        if(file_exists($Pet_path)) break;
    }
    require_once($Pet_path);
}

//Recebendo dados pelo fetch
$post = json_decode(file_get_contents('php://input'));
$dado = [];
foreach( $post as $key => $valor){
    $dado[$key] = $valor;
}

class PetExameControle{
    private $id;

    public function __construct($id){
        $this->id = $id;
    }

    public function excluir(){
        $pdo = new PetDAO();
        $pdo->excluirExamePet($this->id);
        echo json_encode("Excluído com Sucesso");
    }

}

$petExameControle = new PetExameControle($dado['idExamePet']);
$metodo = $dado['metodo'];
$petExameControle->$metodo();


//require_once("");

//echo json_encode($dado['idExamePet']);