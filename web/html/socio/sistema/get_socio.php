<?php
session_start();
require_once("../conexao.php");
require_once("../../permissao/permissao.php");
$idSocio = intval($_GET['id']);

permissao($_SESSION['id_pessoa'], 4, 3);

$query = 'SELECT * FROM socio s JOIN pessoa p ON p.id_pessoa = s.id_pessoa WHERE s.id_socio =?';
$conexao->set_charset("utf8");

$stmt = mysqli_prepare($conexao, $query);
$stmt->bind_param("i", $idSocio);
$stmt->execute();

$resultado = $stmt->get_result();
$dados;

while ($linha = $resultado->fetch_assoc()) {
    $dados[] = $linha;
}

$stmt->close();
echo json_encode($dados);
