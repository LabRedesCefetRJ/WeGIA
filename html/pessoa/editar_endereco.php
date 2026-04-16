<?php
error_log("Entrou no editar_endereco.php com id_pessoa: " . $_POST['id_pessoa']);
session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");

    exit();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 12, 3);



require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

extract($_POST);

$cep = ($cep ? "'$cep'" : "NULL");
$uf = ($uf ? "'$uf'" : "NULL");
$cidade = ($cidade ? "'$cidade'" : "NULL");
$bairro = ($bairro ? "'$bairro'" : "NULL");
$rua = ($rua ? "'$rua'" : "NULL");
$complemento = ($complemento ? "'$complemento'" : "NULL");
$ibge = ($ibge ? "'$ibge'" : "NULL");
$numero_residencia = ($numero_residencia ? "'$numero_residencia'" : "'Não possui'");

$stmt = $pdo->prepare("UPDATE pessoa SET cep=:cep, estado=:uf, cidade=:cidade, bairro=:bairro, logradouro=:rua, complemento=:complemento, ibge=:ibge, numero_endereco=:numero_residencia WHERE id_pessoa=:id_pessoa");

$stmt->bindParam(':cep', $cep);
$stmt->bindParam(':uf', $uf);
$stmt->bindParam(':cidade', $cidade);
$stmt->bindParam(':bairro', $bairro);
$stmt->bindParam(':rua', $rua);
$stmt->bindParam(':complemento', $complemento);
$stmt->bindParam(':ibge', $ibge);
$stmt->bindParam(':numero_residencia', $numero_residencia);
$stmt->bindParam(':id_pessoa', $id_pessoa);

$stmt->execute();

$_GET['sql'] = $sql;
echo(json_encode($_GET));

?>
