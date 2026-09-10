<?php
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

// Os valores são passados como parâmetros vinculados (prepared statement),
// então não devem ser envolvidos em aspas manualmente — isso só fazia o
// banco gravar as aspas como parte do próprio valor. Campos vazios viram
// NULL de verdade (antes viravam a string literal "NULL").
$cep = $cep ? $cep : null;
$uf = $uf ? $uf : null;
$cidade = $cidade ? $cidade : null;
$bairro = $bairro ? $bairro : null;
$rua = $rua ? $rua : null;
$complemento = $complemento ? $complemento : null;
$ibge = $ibge ? $ibge : null;
$numero_residencia = $numero_residencia ? $numero_residencia : 'Não possui';

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

echo json_encode(['sucesso' => true]);

?>
