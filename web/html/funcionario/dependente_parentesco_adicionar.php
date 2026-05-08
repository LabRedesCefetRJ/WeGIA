<?php

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit(); 
}


require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);
require_once '../../dao/Conexao.php';

try {
    $pdo = Conexao::connect();

    $descricao = trim(filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING));
    
    if (empty($descricao)) {
        echo "Descrição não pode estar vazia.";
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO funcionario_dependente_parentesco (descricao) VALUES (:descricao)");
    $stmt->bindParam(':descricao', $descricao); 
    $stmt->execute();

    echo "Dependente adicionado com sucesso.";
} catch (PDOException $e) {
    
    error_log($e->getMessage()); 
    echo "Ocorreu um erro ao adicionar o dependente. Tente novamente mais tarde."; 
}


if (isset($_SESSION['id_pessoa'])) {
    permissao($_SESSION['id_pessoa'], 11, 7);
} else {
    echo "Permissão inválida.";
    exit();
}

die();
