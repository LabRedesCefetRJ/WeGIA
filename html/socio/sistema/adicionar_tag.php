<?php
if (session_status() === PHP_SESSION_NONE)
	session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: " . "../../index.php");
	exit(401);
} else {
	session_regenerate_id();
}

//verificação da permissão do usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 4, 3);

require_once('../conexao.php');
$tag = trim(filter_input(INPUT_POST, 'tag', FILTER_SANITIZE_SPECIAL_CHARS));

$sql = "INSERT into socio_tag(tag) values(?)";
$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
	http_response_code(500);
	exit('Erro ao preparar consulta');
}

$stmt->bind_param('s', $tag);
if (!$stmt->execute()) {
	http_response_code(500);
	exit('Erro ao realizar consulta');
}

http_response_code(200);
exit('Consulta realizada com sucesso');
