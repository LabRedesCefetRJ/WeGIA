<?php
$config_path = "config.php";
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once($config_path);
}

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: " . WWW . "index.php");
    exit;
}

require_once ROOT . "/controle/Atendido_ocorrenciaControle.php";

$id_ocorrencia = filter_input(INPUT_GET, 'idatendido_ocorrencias', FILTER_VALIDATE_INT);
$extensao = strtolower(trim($_GET['extensao']));
$nome = preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($_GET['nome']));
$id_anexo = filter_input(INPUT_GET, 'idatendido_ocorrencia_doc', FILTER_VALIDATE_INT);

if (!$id_ocorrencia || !$extensao || !$nome || !$id_anexo) {
    http_response_code(400);
    exit("Erro nos parâmetros fornecidos.");
}

$AnexoControle = new Atendido_ocorrenciaControle();
$AnexoControle->listarAnexo($id_ocorrencia);

// Mapeia extensões comuns para tipos MIME
$mime_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'txt' => 'text/plain',
    'zip' => 'application/zip',
    'rar' => 'application/vnd.rar',
    // Adicione mais conforme necessário
];

$content_type = $mime_types[$extensao] ?? 'application/octet-stream';

header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $nome . '.' . $extensao . '"');

if (!isset($_SESSION['arq'][$id_anexo])) {
    http_response_code(404);
    exit("Arquivo não encontrado na sessão.");
}

echo $_SESSION['arq'][$id_anexo];