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
permissao($_SESSION['id_pessoa'],11,3);

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

$sql = "DELETE FROM `funcionario_docfuncional` WHERE id_docfuncional=?";

//$resultado = mysqli_query($conexao, "DELETE FROM `funcionario_docfuncional` WHERE id_docfuncional=$id_cargo");

$stmt = mysqli_prepare($conexao, $sql);

$stmt->bind_param('i', $id_cargo);

$stmt->execute();
if (mysqli_affected_rows($conexao) == 1) {
    $_SESSION['msg'] = "Documento deletado com sucesso.";
    $_SESSION['link'] = "./geral/documentos_funcionario.php";
    $_SESSION['proxima'] = "Listar documentos";

    fecharConexao($stmt, $conexao);
    header("Location: ../sucesso.php");
} else {
    fecharConexao($stmt, $conexao);
    header("Location: ./documentos_funcionario.php?msg_e=Erro ao modificar documento, existem arquivos cadastrados com esse tipo de documento, exclua-os primeiro.");
}
