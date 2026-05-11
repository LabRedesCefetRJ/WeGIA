<?php

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);


require_once "../../dao/Conexao.php";

$id_dependente = $_POST["id_dependente"];

if(!$id_dependente || !is_numeric($id_dependente)){
    http_response_code(400);
    exit('O valor informado para o id do dependente não é válido.');
}

try {
    $sql = "SELECT doc.nome_docdependente AS descricao, ddoc.data, ddoc.id_doc FROM funcionario_dependentes_docs ddoc LEFT JOIN funcionario_docdependentes doc ON doc.id_docdependentes = ddoc.id_docdependentes WHERE ddoc.id_dependente=:idDependente";
    $pdo = Conexao::connect();
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idDependente', $id_dependente);
    $stmt->execute();

    $dependente = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dependente = json_encode($dependente);

    echo $dependente;
} catch (PDOException $e) {
    echo 'Erro na hora de buscar os documentos do familiar: ' . $e->getMessage();
}