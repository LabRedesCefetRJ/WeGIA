<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
} else {
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once "../../dao/Conexao.php";

$id_dependente = trim($_POST["id_dependente"]);
$id_atendido = trim($_POST['idatendido']);

if (!$id_dependente || !is_numeric($id_dependente)) {
    http_response_code(400);
    exit('Erro, o valor do id do familiar informado não é válido.');
}

if (!$id_atendido || !is_numeric($id_atendido)) {
    http_response_code(400);
    exit('Erro, o valor do id do paciente informado não é válido.');
}

try {
    $pdo = Conexao::connect();
    $sqlDelete = "DELETE FROM atendido_familiares WHERE idatendido_familiares =:idDependente";

    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->bindParam(':idDependente', $id_dependente);
    $stmtDelete->execute();

    $sqlSelect = "SELECT 
    af.idatendido_familiares AS id_dependente, p.nome AS nome, p.cpf AS cpf, ap.parentesco AS parentesco
    FROM atendido_familiares af
    LEFT JOIN atendido a ON a.idatendido = af.atendido_idatendido
    LEFT JOIN pessoa p ON p.id_pessoa = af.pessoa_id_pessoa
    LEFT JOIN atendido_parentesco ap ON ap.idatendido_parentesco = af.atendido_parentesco_idatendido_parentesco
    WHERE af.atendido_idatendido =:idAtendido";

    $response = $pdo->prepare($sqlSelect);
    $response->bindParam(':idAtendido', $id_atendido);
    $response->execute();
    $response = $response->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($response);
} catch (PDOException $th) {
    echo json_encode($th);
}
