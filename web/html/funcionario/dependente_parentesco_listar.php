<?php

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once "../../dao/Conexao.php";

try{
    $pdo = Conexao::connect();
    $resultado = $pdo->query("SELECT * FROM funcionario_dependente_parentesco ORDER BY descricao ASC;");
    $parentescos = $resultado->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($parentescos as $index => $parentesco){
        $parentescos[$index]['descricao'] = htmlspecialchars($parentesco['descricao']);
    }

    echo json_encode($parentescos);
}catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor ao buscar parentescos.']);
    exit();
}