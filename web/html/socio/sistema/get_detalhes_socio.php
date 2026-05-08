<?php
session_start();
if (!isset($_SESSION['usuario'])) die("Você não está logado(a).");

require_once '../../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 4, 3);


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

// Segundo statement
$sql2 = "SELECT p.nome, p.cpf, p.data_nascimento, p.cep, p.logradouro, p.numero_endereco, p.complemento, p.bairro, p.estado, p.cidade, s.email, p.telefone, st.tipo, ss.status, s.data_referencia, s.valor_periodo, stg.tag FROM pessoa as p JOIN socio s ON(p.id_pessoa=s.id_pessoa) JOIN socio_tipo st ON(st.id_sociotipo=s.id_sociotipo) JOIN socio_status ss ON(ss.id_sociostatus=s.id_sociostatus) JOIN socio_tag stg ON(stg.id_sociotag=s.id_sociotag) WHERE s.id_socio=?";

$stmt2 = mysqli_prepare($conexao, $sql2);

mysqli_stmt_bind_param($stmt2, 'i', $id_socio);

mysqli_stmt_execute($stmt2);

// Obter o resultado do statement
$result2 = mysqli_stmt_get_result($stmt2);

$resultado = mysqli_fetch_assoc($result2);

// Fechar o segundo statement
mysqli_stmt_close($stmt2);

// Fechar a conexão
mysqli_close($conexao);

echo json_encode($resultado);
