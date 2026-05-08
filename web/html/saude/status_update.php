<pre>
<?php

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

session_start();
extract($_REQUEST);
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
}

// if ($_POST){
    require_once "../../dao/Conexao.php";

    $saude_medicacao_status_idsaude_medicacao_status = $_POST["id_status"];
    // var_dump($saude_medicacao_status_idsaude_medicacao_status);

    $id_medicacao = $_POST['id_medicacao'];
    $id_fichamedica = $_POST['id_fichamedica'];
    try {
        $pdo = Conexao::connect();
        $prep = $pdo->prepare("UPDATE saude_medicacao SET saude_medicacao_status_idsaude_medicacao_status = :status WHERE id_medicacao = :id_medicacao");
        $prep->bindParam(':status', $saude_medicacao_status_idsaude_medicacao_status, PDO::PARAM_INT);
        $prep->bindParam(':id_medicacao', $id_medicacao, PDO::PARAM_INT);
        $prep->execute();
        // UPDATE saude_enfermidades SET status = 0 WHERE id_CID = ".$this->getid_CID()." ;"


        $prep->execute();
        
        header("Location: profile_paciente.php?id_fichamedica=$id_fichamedica");
    } catch (PDOException $e) {
        echo("Houve um erro ao realizar o upload do exame:<br><br>$e");
    }


// }else {
//     header("Location: profile_paciente.php");
// }


