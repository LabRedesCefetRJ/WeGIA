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

$tipoRegistro = filter_var($value, FILTER_SANITIZE_STRING);
$id_tipoRegistro = filter_var($id_tipoRegistro, FILTER_SANITIZE_NUMBER_INT);

if (!$id_tipoRegistro || $id_tipoRegistro < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'Id de tipo de registro profissional inválido']);
    exit();
}

if (!$tipoRegistro) {
    http_response_code(400);
    echo json_encode(['erro' => 'A descrição de um tipo de registro profissional não pode ser vazia!']);
    exit();
}

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$sql = "UPDATE registro_profissional_tipo SET descricao =? WHERE id_registro_profissional_tipo =?";
$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
    http_response_code(500);
    exit('Erro ao preparar consulta');
}

$stmt->bind_param('si', $tipoRegistro, $id_tipoRegistro);
$stmt->execute();

if (mysqli_affected_rows($conexao)) {
    $_SESSION['msg'] = "Tipo de registro profissional salvo com sucesso.";
    $_SESSION['link'] = "./geral/tipoRegistroProfissional.php";
    $_SESSION['proxima'] = "Tipos de Registros Profissionais";

    fecharConexao($stmt, $conexao);
    header("Location: ../sucesso.php");
} else {
    fecharConexao($stmt, $conexao);
    header("Location: ./tipoRegistroProfissional.php?msg_e=Erro ao modificar tipoRegistro.");
}
