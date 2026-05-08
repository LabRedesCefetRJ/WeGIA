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
permissao($_SESSION['id_pessoa'],91,3);

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

$sql = "DELETE FROM `permissao` WHERE id_cargo=? and id_acao = ? and id_recurso = ?";

$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
    http_response_code(500);
    exit('Erro ao preparar consulta');
}

$stmt->bind_param('iii', $c, $a, $r);
$stmt->execute();

if (mysqli_affected_rows($conexao)) {
    $_SESSION['msg'] = "Permissão deletada com sucesso.";
    $_SESSION['link'] = "./geral/listar_permissoes.php";
    $_SESSION['proxima'] = "Listar permissões";

    fecharConexao($stmt, $conexao);
    header("Location: ../sucesso.php");
} else {
    fecharConexao($stmt, $conexao);
    header("Location: ./listar_permissoes.php?msg_e=Erro ao deletar permissão.");
}
