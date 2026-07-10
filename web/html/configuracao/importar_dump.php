<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
} else {
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 9);

require_once "../../config.php";
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    header("Location: ./configuracao_geral.php?msg=error&err=Token CSRF inválido. Por favor, tente novamente.");
    exit();
}

define("REDIRECT", $_REQUEST["redirect"] ?? "./configuracao_geral.php");

$newFileName = $_FILES["import"]["name"];
$fileTmpPath = $_FILES["import"]["tmp_name"];
$fileExtension = pathinfo($newFileName, PATHINFO_EXTENSION);
$allowedMimeTypes = ['application/x-gzip', 'application/gzip', 'application/x-tar'];

// valida extensão
if (!preg_match('/dump\.tar\.gz$/i', $newFileName)) {
    http_response_code(400);
    header("Location: ./configuracao_geral.php?msg=error&err=Apenas arquivos dump.tar.gz são permitidos.");
    exit();
}

// valida MIME
$fileMimeType = mime_content_type($fileTmpPath);
if (!in_array($fileMimeType, $allowedMimeTypes)) {
    http_response_code(400);
    header("Location: ./configuracao_geral.php?msg=error&err=Tipo inválido.");
    exit();
}

// nome seguro
$dataHora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
$safeName = $dataHora->format('YmdHis') . '-import' . '.dump.tar.gz';
$destination = BKP_DIR . DIRECTORY_SEPARATOR . $safeName;

// valida extensão
if (!in_array(strtolower($fileExtension), ['gz', 'gzip', 'tar'])) {
    http_response_code(400);
    header("Location: " . REDIRECT . "?msg=error&err=Extensão de arquivo inválida");
    exit();
}

// Validar tipo MIME
$fileMimeType = mime_content_type($fileTmpPath);
if (!in_array($fileMimeType, $allowedMimeTypes)) {
    http_response_code(400);
    header("Location: " . REDIRECT . "?msg=error&err=Tipo de arquivo inválido");
    exit();
}

//  Validar que o arquivo está no diretório correto
$uploadDir = realpath(BKP_DIR);
if ($uploadDir === false) {
    http_response_code(500);
    header("Location: " . REDIRECT . "?msg=error&err=Diretório de backup inválido");
    exit();
}
// move seguro
if (!move_uploaded_file($fileTmpPath, $destination)) {
    http_response_code(500);
    header("Location: ./configuracao_geral.php?msg=error&err=Erro ao mover arquivo.");
    exit();
}
// VALIDAR QUE O ARQUIVO FOI SALVO NO LOCAL CORRETO
$uploadedFile = realpath($destination);
if ($uploadedFile === false || strpos($uploadedFile, $uploadDir) !== 0) {
    unlink($destination); // Remove arquivo se estiver em local errado
    http_response_code(500);
    header("Location: " . REDIRECT . "?msg=error&err=Arquivo salvo em local inválido");
    exit();
}

header("Location: ./configuracao_geral.php?msg=success&sccs=Importação realizada com sucesso!");
exit();
