<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['id_pessoa'])) {
    http_response_code(401);
    die(json_encode(['erro' => 'Operação negada: Cliente não autorizado']));
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 63, 7);

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
$metodo = $dado['metodo'] ?? null;

// Igual ControleHistorico.php/controleGetPet.php (refs #502): o nome do
// método vem direto do corpo da requisição -- esta classe só expõe excluir().
if ($metodo !== 'excluir') {
    http_response_code(400);
    die(json_encode(['erro' => 'Método inválido.']));
}

$petExameControle->$metodo();


//require_once("");

//echo json_encode($dado['idExamePet']);