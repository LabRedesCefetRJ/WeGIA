<?php

session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);
require_once '../../dao/Conexao.php';

$nome_docfuncional = $_POST["nome_docfuncional"];
$nome_docfuncional = trim($nome_docfuncional);

if(!$nome_docfuncional){
    echo 'O documento informado não possuí um nome.';
    die();
}

try{
    $sql = "INSERT INTO funcionario_docfuncional (nome_docfuncional) VALUES (:nome_docfuncional)";
    $pdo = Conexao::connect();
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome_docfuncional', $nome_docfuncional);
    $stmt->execute();
}catch(PDOException $e){
    echo 'Não foi possível adicionar esse documento: '.$e->getMessage();
}