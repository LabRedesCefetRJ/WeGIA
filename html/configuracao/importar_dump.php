<?php
if(session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}else{
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 9);

require_once "../../config.php";

define("REDIRECT", $_REQUEST["redirect"] ?? "./configuracao_geral.php");

$newFileName = $_FILES["import"]["name"];
$fileTmpPath = $_FILES["import"]["tmp_name"];
$fileExtension = pathinfo($newFileName, PATHINFO_EXTENSION);
$allowedMimeTypes = ['application/x-gzip', 'application/gzip', 'application/x-tar'];

// Verifica se a extensão é "dump.tar.gz"
if (!preg_match('/^gz$/i', $fileExtension) && !preg_match('/dump\.tar\.gz$/i', $newFileName)) {
    header("Location: ./configuracao_geral.php?msg=error&err=Apenas arquivos dump.tar.gz são permitidos.");
    exit();
}

// Verifica o MIME type do arquivo
$fileMimeType = mime_content_type($fileTmpPath);
if (!in_array($fileMimeType, $allowedMimeTypes)) {
    header("Location: ./configuracao_geral.php?msg=error&err=O tipo de arquivo não é válido para um arquivo DUMP.TAR.GZ.");
    exit();
}

$log = shell_exec(("mv " . escapeshellarg($_FILES["import"]["tmp_name"]) . " " . BKP_DIR . escapeshellarg($_FILES["import"]["name"])));
if ($log) {
    header("Location: ./configuracao_geral.php?msg=error&err=Houve um erro na importação!&log=" . base64_encode($log));
}
header("Location: ./configuracao_geral.php?msg=success&sccs=Importação realizada com sucesso!");
