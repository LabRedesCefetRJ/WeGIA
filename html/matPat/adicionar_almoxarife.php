<?php
session_start();
require_once './permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 91);
if (!isset($id_funcionario) && !isset($id_almoxarifado)) {
    header("Location: ./geral/editar_permissoes.php?msg=Preencha todos os campos antes de prosseguir!&flag=warn");
}
extract($_REQUEST);


if (!isset($_SESSION['id_pessoa'])) {
    header("Location: ../index.php");
}

require_once '../dao/Conexao.php';
$pdo = Conexao::connect();

$stmt = $pdo->prepare("SELECT * FROM almoxarife WHERE id_funcionario=:idFuncionario AND id_almoxarifado=:idAlmoxarifado");

$stmt->bindParam(':idFuncionario', $id_funcionario);
$stmt->bindParam(':idAlmoxarifado', $id_almoxarifado);

$stmt->execute();

if ($stmt->rowCount() > 0) {
    header("Location: ./geral/editar_permissoes.php?msg=Funcionário já cadastrado para o Almoxarifado escolhido!&flag=warn");
} else {
    try {
        $stmt2 = $pdo->prepare("INSERT INTO almoxarife (id_funcionario, id_almoxarifado) VALUES (:idFuncionario , :idAlmoxarifado)");

        $stmt2->bindParam(':idFuncionario', $id_funcionario);
        $stmt2->bindParam(':idAlmoxarifado', $id_almoxarifado);

        $stmt2->execute();

        header("Location: ./geral/editar_permissoes.php?msg=Funcionário cadastrado como almoxarife!&flag=success");
    } catch (PDOException $e) {
        header("Location: ./geral/editar_permissoes.php?msg=Erro: &flag=erro&log={$e->getMessage()}");
    }
}
