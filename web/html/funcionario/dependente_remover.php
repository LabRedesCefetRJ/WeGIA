<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
} else {
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once "../../dao/Conexao.php";

try {
    $idDependente = filter_input(INPUT_POST, 'id_dependente', FILTER_VALIDATE_INT);
    $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);

    if (!$idDependente || $idDependente < 1) {
        throw new InvalidArgumentException('O id do dependente não está dentro dos limites permitidos.');
    }

    if (!$idFuncionario || $idFuncionario < 1) {
        throw new InvalidArgumentException('O id do funcionário não está dentro dos limites permitidos.');
    }

    $pdo = Conexao::connect();

    $stmtDelete = $pdo->prepare("DELETE FROM funcionario_dependentes WHERE id_dependente =:idDependente;");

    $stmtDelete->bindValue(':idDependente', $idDependente, PDO::PARAM_INT);

    $stmtDelete->execute();

    $stmtResponse = $pdo->prepare("SELECT 
    fdep.id_dependente AS id_dependente, p.nome AS nome, p.cpf AS cpf, par.descricao AS parentesco
    FROM funcionario_dependentes fdep
    LEFT JOIN funcionario f ON f.id_funcionario = fdep.id_funcionario
    LEFT JOIN pessoa p ON p.id_pessoa = fdep.id_pessoa
    LEFT JOIN funcionario_dependente_parentesco par ON par.id_parentesco = fdep.id_parentesco
    WHERE fdep.id_funcionario =:idFuncionario");

    $stmtResponse->bindValue('idFuncionario', $idFuncionario, PDO::PARAM_INT);

    $stmtResponse->execute();
    
    echo json_encode($stmtResponse->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
    Util::tratarException($e);
}
