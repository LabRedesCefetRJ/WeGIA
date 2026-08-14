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

$sql = "DELETE FROM `registro_profissional_tipo` WHERE id_registro_profissional_tipo = ?";
$stmt = mysqli_prepare($conexao, $sql);
$stmt->bind_param('i', $id_tipoRegistroProfissional);
$stmt->execute();

if (mysqli_affected_rows($conexao)) {
    $_SESSION['msg'] = "Tipo de Registro Profissional deletado com sucesso.";
    $_SESSION['link'] = "./geral/tipoRegistroProfissional.php";
    $_SESSION['proxima'] = "Listar Tipos de Registros Profissionais";
    fecharConexao($stmt, $conexao);
    header("Location: ../sucesso.php");
} else {
    fecharConexao($stmt, $conexao);
    header("Location: ./tipoRegistroProfissional.php?msg_e=Erro ao modificar tipo de registro profissional.");
}
