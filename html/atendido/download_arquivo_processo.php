<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: " . "../../index.php");
    exit(401);
} else {
    session_regenerate_id();
}

require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 14);

require_once '../../dao/Conexao.php';
require_once '../../dao/PaArquivoDAO.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('ID inválido.');
}

$pdo = Conexao::connect();
$dao = new PaArquivoDAO($pdo);
$arquivo = $dao->buscarArquivo($id);

if (!$arquivo) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

$nome = $arquivo['arquivo_nome'];
$ext  = strtolower($arquivo['arquivo_extensao']);
$blob = $arquivo['arquivo'];

$decompressed = @gzuncompress($blob);
if ($decompressed !== false) {
    $blob = base64_decode($decompressed);
}


$tipos = [
    'pdf'  => 'application/pdf',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'odp'  => 'application/vnd.oasis.opendocument.presentation',
];

$mime = $tipos[$ext] ?? 'application/octet-stream';

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($nome) . '"');
header('Content-Length: ' . strlen($blob));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

echo $blob;
exit;
