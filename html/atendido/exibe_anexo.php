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
}

require_once ROOT . "/controle/Atendido_ocorrenciaControle.php";

$id_ocorrencia = $_GET['idatendido_ocorrencias'];
$extensao = trim($_GET['extensao']);
$nome = trim($_GET['nome']);
$id_anexo = $_GET['idatendido_ocorrencia_doc'];


if (!$id_ocorrencia || !is_numeric($id_ocorrencia)) {
    http_response_code(400);
    exit("Erro ao exibir anexo, o id fornecido não é um número válido para essa operação");
}

if (!$extensao || empty($extensao) || !$nome || empty($nome)) {
    http_response_code(400);
    exit("Erro ao exibir anexo, a combinação do nome com o formato do arquivo não é válida.");
}

$AnexoControle = new Atendido_ocorrenciaControle;
$AnexoControle->listarAnexo($id_ocorrencia);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $nome . '.' . $extensao . '"');

echo $_SESSION['arq'][$id_anexo];
