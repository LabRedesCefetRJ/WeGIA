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
$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$sql1 = "UPDATE `funcionario` SET `id_cargo`=2 WHERE id_cargo=?";

$stmt = mysqli_prepare($conexao, $sql1);

$stmt->bind_param('i', $id_cargo);

$stmt->execute();

// Fechar o primeiro statement
mysqli_stmt_close($stmt);

$sql2 = "DELETE FROM `cargo` WHERE id_cargo=?";

$stmt2 = mysqli_prepare($conexao, $sql2);

$stmt2->bind_param('i', $id_cargo);

$stmt2->execute();

if (mysqli_affected_rows($conexao)) {
    $_SESSION['msg'] = "Cargo deletado com sucesso.";
    $_SESSION['link'] = "./geral/cargos.php";
    $_SESSION['proxima'] = "Listar cargos";
    fecharConexao($stmt2, $conexao);
    header("Location: ../sucesso.php");
} else {
    fecharConexao($stmt2, $conexao);
    header("Location: ./cargos.php?msg_e=Erro ao modificar cargo.");
}
