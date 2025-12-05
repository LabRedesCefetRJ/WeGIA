<?php
require_once '../../dao/Conexao.php';
require_once '../../dao/PaArquivoDAO.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo 'ID inválido.';
    exit;
}

$pdo = Conexao::connect();
$dao = new PaArquivoDAO($pdo);
$arquivo = $dao->buscarArquivo($id);

if (!$arquivo) {
    http_response_code(404);
    echo 'Arquivo não encontrado.';
    exit;
}

$nome = $arquivo['arquivo_nome'];
$ext  = strtolower($arquivo['arquivo_extensao']);
$blob = $arquivo['arquivo'];

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

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($nome) . '"');
header('Content-Length: ' . strlen($blob));

echo $blob;
exit;
