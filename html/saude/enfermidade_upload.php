<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

session_start();
extract($_REQUEST);
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
}

require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 54, 7);

if ($_POST) {
    require_once "../../dao/Conexao.php";

    try {
        $pdo = Conexao::connect();
        $prep = $pdo->prepare("INSERT INTO saude_enfermidades(id_fichamedica, id_CID, data_diagnostico, status) VALUES (:id_fichamedica, :id_CID, :data_diagnostico, :status)");

        $prep->bindValue(":id_fichamedica", $id_fichamedica);
        $prep->bindValue(":id_CID", $id_CID);
        $prep->bindValue(":data_diagnostico", $data_diagnostico);
        $prep->bindValue(":status", $intStatus);

        $prep->execute();

        echo json_encode(['sucesso' => 'Comorbidade cadastrada com sucesso']);
    } catch (PDOException $e) {
        // Exibe mensagem de erro de forma segura
        http_response_code(500);
        echo json_encode(['erro' => 'Problema no servidor ao cadastrar comorbidade: ' . $e->getMessage()]);
    }
} else {
    header("Location: profile_paciente.php");
}
