<?php
require("../conexao.php");
if (!isset($_POST) or empty($_POST)) {
    $data = file_get_contents("php://input");
    $data = json_decode($data, true);
    $_POST = $data;
} else if (is_string($_POST)) {
    $_POST = json_decode($_POST, true);
}
$conexao->set_charset("utf8");
extract($_REQUEST);

$sql = "SELECT `linha_digitavel` FROM `cobrancas` WHERE codigo = ?";

$stmt = mysqli_prepare($conexao, $sql);

mysqli_stmt_bind_param($stmt, 's', $codigo);

mysqli_stmt_execute($stmt);

// Obter o resultado do statement
$codigoBarras= mysqli_stmt_get_result($stmt);

$dados;
while ($resultado = mysqli_fetch_assoc($codigoBarras)) {
    $dados[] = $resultado;
}

// Fechar o primeiro statement
mysqli_stmt_close($stmt);

// Fechar a conexão
mysqli_close($conexao);

echo json_encode($dados);
