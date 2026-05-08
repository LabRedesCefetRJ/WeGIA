<?php

session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);


require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();
$id_funcionario = $_POST["id_funcionario"];

$stmt = $pdo->prepare("SELECT 
p.nome AS nome, p.cpf AS cpf, par.descricao AS parentesco
FROM funcionario_dependentes fdep
LEFT JOIN funcionario f ON f.id_funcionario = fdep.id_funcionario
LEFT JOIN pessoa p ON p.id_pessoa = fdep.id_pessoa
LEFT JOIN funcionario_dependente_parentesco par ON par.id_parentesco = fdep.id_parentesco
WHERE fdep.id_funcionario = :id_funcionario");

$stmt->bindParam(':id_funcionario', $id_funcionario, PDO::PARAM_INT);

$stmt->execute();

$dependentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($dependentes as &$dependente) {
    $dependente['cpf'] = substr($dependente['cpf'], 0, 3) . '.***.***-' . substr($dependente['cpf'], -2); 
}

echo json_encode($dependentes);

exit();