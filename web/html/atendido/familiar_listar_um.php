<?php

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);


require_once "../../dao/Conexao.php";

$id_dependente = trim($_POST["id_dependente"]);

if(!$id_dependente || !is_numeric($id_dependente)){
    http_response_code(400);
    exit('Erro, o id fornecido para um familiar não é válido.');
}

try {
    $pdo = Conexao::connect();
    $dependente = $pdo->query("SELECT *, ap.parentesco AS parentesco
FROM atendido_familiares af
LEFT JOIN pessoa p ON p.id_pessoa = af.pessoa_id_pessoa
LEFT JOIN atendido_parentesco ap ON ap.idatendido_parentesco = af.atendido_parentesco_idatendido_parentesco
WHERE af.idatendido_familiares= $id_dependente;");
    $dependente = $dependente->fetchAll(PDO::FETCH_ASSOC)[0];
    $dependente = json_encode($dependente);
    http_response_code(200);
    echo $dependente;
} catch (PDOException $e) {
    http_response_code(500);
    $msgErro = 'Erro ao listar familiar: ' . $e->getMessage();
    echo json_encode(['erro' => $msgErro]);
}