<?php

function fecharConexao(mysqli_stmt $stmt, mysqli $conexao)
{
    // Fechar o primeiro statement
    mysqli_stmt_close($stmt);

    // Fechar a conexão
    mysqli_close($conexao);
}

session_start();
if (!isset($_SESSION['usuario'])) die("Você não está logado(a).");

require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 3);

$config_path = "config.php";
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once($config_path);
}
extract($_REQUEST);

$cargo = filter_var($value, FILTER_SANITIZE_STRING);
$id_cargo = filter_var($id_cargo, FILTER_SANITIZE_NUMBER_INT);

if (!$id_cargo || $id_cargo < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'Id de cargo inválido']);
    exit();
}

if (!$cargo) {
    http_response_code(400);
    echo json_encode(['erro' => 'A descrição de um cargo não pode ser vazia']);
    exit();
}

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$sql = "UPDATE cargo SET cargo=? WHERE id_cargo=?";
$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
    http_response_code(500);
    exit('Erro ao preparar consulta');
}

$stmt->bind_param('si', $cargo, $id_cargo);
$stmt->execute();

if (mysqli_affected_rows($conexao)) {
    $_SESSION['msg'] = "Cargo salvo com sucesso.";
    $_SESSION['link'] = "./geral/cargos.php";
    $_SESSION['proxima'] = "Listar cargos";

    fecharConexao($stmt, $conexao);
    header("Location: ../sucesso.php");
} else {
    fecharConexao($stmt, $conexao);
    header("Location: ./cargos.php?msg_e=Erro ao modificar cargo.");
}
