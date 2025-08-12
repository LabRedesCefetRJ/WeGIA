<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
extract($_REQUEST);
session_start();

if (!isset($_SESSION["usuario"])){
    header("Location: ../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once "../../dao/Conexao.php";
require_once "enfermidade.php";

extract($_GET);
$id_fichamedica = isset($_GET['id_fichamedica']) ? $_GET['id_fichamedica'] : null;

$en = new EnfermidadeSaude($id_doc);
if (!$en->getException()){
    $en->delete();
    
    try{
        $sql = "SELECT se.id_CID, se.data_diagnostico, se.status, stc.descricao FROM saude_enfermidades se JOIN saude_tabelacid stc ON se.id_CID = stc.id_CID WHERE status = 1  AND stc.CID NOT LIKE 'T78.4%' AND id_fichamedica = ? ;";
        
        $pdo = Conexao::connect();
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $id_fichamedica, PDO::PARAM_INT); // Bind do parâmetro
        $stmt->execute();

        $enfermidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $enfermidades = json_encode($enfermidades);
        echo $enfermidades;
    }catch(PDOException $e){
        echo "Houve um erro ao realizar a exclusão da enfermidade:<br><br>" . htmlspecialchars($e->getMessage());
    }
}else{
    echo $en->getException();
}

die();

