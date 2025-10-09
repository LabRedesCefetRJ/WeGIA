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

//Verifica Permissão do Usuário
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

try {
    $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);

    if (!$idFuncionario || $idFuncionario < 1) {
        throw new InvalidArgumentException('O id do funcionário informado não é válido.', 400);
    }

    require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
    $pdo = Conexao::connect();

    $stmtDependente = $pdo->prepare(
        "SELECT 
        p.nome AS nome, p.cpf AS cpf, par.descricao AS parentesco
        FROM funcionario_dependentes fdep
        LEFT JOIN funcionario f ON f.id_funcionario = fdep.id_funcionario
        LEFT JOIN pessoa p ON p.id_pessoa = fdep.id_pessoa
        LEFT JOIN funcionario_dependente_parentesco par ON par.id_parentesco = fdep.id_parentesco
        WHERE fdep.id_funcionario =:idFuncionario"
    );

    $stmtDependente->bindValue(':idFuncionario', $idFuncionario, FILTER_VALIDATE_INT);
    $stmtDependente->execute();

    echo json_encode($stmtDependente->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
    Util::tratarException($e);
}
