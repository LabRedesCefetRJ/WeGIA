<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
} else {
    session_regenerate_id();
}

$idPessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

if (!$idPessoa || $idPessoa < 1) {
    http_response_code(400);
    header('Location: ' . WWW . 'html/home.php?msg_c=O id de usuário fornecido é inválido.');
    exit();
}

// Verifica Permissão do Usuário
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'MiddlewareDAO.php';
$middlewareDao = new MiddlewareDAO();
$access = $middlewareDao->verificarPermissao($idPessoa, 'whitelist', ['whitelist' => [12, 52]]);

if (!$access) {
    http_response_code(403);
    header('Location: ' . WWW . 'html/home.php?msg_c=Você não tem as permissões necessárias para essa página.');
    exit();
}

require_once "../../dao/Conexao.php";
require_once "documento.php";

define("TYPEOF_EXTENSION", [
    'jpg' => 'image/jpg',
    'png' => 'image/png',
    'jpeg' => 'image/jpeg',
    'pdf' => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'doc' => 'application/doc',
    'odp' => 'application/odp',
]);

$idDoc = $_GET['id_doc'];
if (!is_numeric($idDoc) || $idDoc < 1) {
    http_response_code(400);
    exit('Não foi possível baixar o documento solicitado.');
}

$arquivo = new DocumentoAtendido($idDoc);

if (!$arquivo->getException()) {
    header("Content-type: " . TYPEOF_EXTENSION[$arquivo->getExtensao()]);
    header("Content-Disposition: attachment; filename=" . $arquivo->getNome());
    ob_clean();
    flush();

    echo $arquivo->getDocumento();
} else {
    echo $arquivo->getException();
}
