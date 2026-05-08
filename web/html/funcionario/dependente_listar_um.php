<?php

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

$id_dependente = trim(filter_input(INPUT_POST, 'id_dependente', FILTER_SANITIZE_NUMBER_INT));

if (!$id_dependente || $id_dependente < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id de um dependente deve ser um inteiro positivo maior ou igual a 1.']);
    exit();
}

try {
    require_once "../../dao/Conexao.php";
    $pdo = Conexao::connect();

    $stmt = $pdo->prepare('SELECT *, par.descricao AS parentesco
    FROM funcionario_dependentes fdep
    LEFT JOIN pessoa p ON p.id_pessoa = fdep.id_pessoa
    LEFT JOIN funcionario_dependente_parentesco par ON par.id_parentesco = fdep.id_parentesco
    WHERE fdep.id_dependente = :idDependente');

    $stmt->bindParam(':idDependente', $id_dependente);
    $stmt->execute();

    $dependente = $stmt->fetch(PDO::FETCH_ASSOC);

    echo  json_encode($dependente);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor ao listar dependente.']);
    exit();
}
