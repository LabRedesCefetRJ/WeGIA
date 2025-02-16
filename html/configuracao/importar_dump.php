<?php

session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
    exit();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 9);

require_once "../../config.php";

define("REDIRECT", $_REQUEST["redirect"] ?? "./configuracao_geral.php");

$newFileName = $_FILES["import"]["name"];

$log = shell_exec(("mv ". escapeshellarg($_FILES["import"]["tmp_name"]) . " " . BKP_DIR . escapeshellarg($_FILES["import"]["name"])));
if ($log){
    header("Location: ./configuracao_geral.php?msg=error&err=Houve um erro na importação!&log=".base64_encode($log));
}
header("Location: ./configuracao_geral.php?msg=success&sccs=Importação realizada com sucesso!");


