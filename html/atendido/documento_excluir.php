<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.php");
    exit();
} else {
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 12, 7);

require_once "../../dao/Conexao.php";
require_once "documento.php";

$id_doc = filter_input(INPUT_GET, 'id_doc', FILTER_SANITIZE_NUMBER_INT);
$idAtendido = filter_input(INPUT_GET, 'idatendido', FILTER_SANITIZE_NUMBER_INT);

if (!$id_doc || !$idAtendido || $id_doc < 1 || $idAtendido < 1) {
    http_response_code(400);
    exit("Erro ao tentar remover o arquivo selecionado, os id's fornecidos não são válidos");
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PessoaArquivo.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'PessoaArquivoMySQL.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

try {
    $pdo = Conexao::connect();

    if (!PessoaArquivo::deleteById($id_doc, new PessoaArquivoMySQL($pdo)))
        throw new RuntimeException('Erro ao excluir arquivo.', 500);

    $sql = "SELECT a.idatendido_documentacao, pa.`data`, ada.descricao, a.id_pessoa_arquivo FROM atendido_documentacao a JOIN atendido_docs_atendidos ada ON a.atendido_docs_atendidos_idatendido_docs_atendidos = ada.idatendido_docs_atendidos JOIN pessoa_arquivo pa ON a.id_pessoa_arquivo=pa.id WHERE a.atendido_idatendido=:idAtendido";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idAtendido', $idAtendido);
    $stmt->execute();
    $docfuncional = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $docfuncional = json_encode($docfuncional);

    echo $docfuncional;
} catch (Exception $e) {
    Util::tratarException($e);
}
