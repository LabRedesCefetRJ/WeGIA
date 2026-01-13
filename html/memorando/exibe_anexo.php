<?php
//Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: " . WWW . "index.php");
    exit();
} else {
    session_regenerate_id();
}

$idPessoa = filter_var($_SESSION['id_pessoa'], FILTER_VALIDATE_INT);

if (!$idPessoa || $idPessoa < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id fornecido do usuário na sessão não é válido.']);
    exit();
}

//verificar permissão
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($idPessoa, 3, 5);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'controle' . DIRECTORY_SEPARATOR . 'memorando' . DIRECTORY_SEPARATOR . 'AnexoControle.php';

$id_anexo = filter_input(INPUT_GET, 'id_anexo', FILTER_VALIDATE_INT);
$extensao = filter_input(INPUT_GET, 'extensao', FILTER_SANITIZE_SPECIAL_CHARS);
$nome = filter_input(INPUT_GET, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$id_anexo || $id_anexo < 1) {
    throw new InvalidArgumentException('O id do anexo fornecido não é válido.', 400);
}

if (!$extensao) {
    throw new InvalidArgumentException('O nome da extensão informado não é válido.', 400);
}

if (!$nome || strlen($nome) < 1) {
    throw new InvalidArgumentException('O nome do anexo não é válido.', 400);
}

//Cria um novo objeto (Anexo de controle)
$AnexoControle = new AnexoControle;
$AnexoControle->listarAnexo($id_anexo);

header('Content-Type: application/force-download');
header('Content-Disposition: attachment; filename="' . $nome . '.' . $extensao . '"');

//Header('Content-Disposition: attachment; filename="'.$nome.'.'.$extensao);
echo $_SESSION['arq'][0]['anexo'];
