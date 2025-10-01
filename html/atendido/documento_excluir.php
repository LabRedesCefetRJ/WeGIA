<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.php");
    exit();
}else{
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

$arquivo = new DocumentoAtendido($id_doc);
if (!$arquivo->getException()) {
    $arquivo->delete();
    try {
        $sql = "SELECT a.idatendido_documentacao, a.`data`, ada.descricao FROM atendido_documentacao a JOIN atendido_docs_atendidos ada ON a.atendido_docs_atendidos_idatendido_docs_atendidos = ada.idatendido_docs_atendidos WHERE atendido_idatendido =:idAtendido";
        $pdo = Conexao::connect();
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idAtendido', $idAtendido);
        $stmt->execute();
        $docfuncional = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $docfuncional = json_encode($docfuncional);
        echo $docfuncional;
    } catch (PDOException $e) {
        echo 'Erro ao tentar remover o arquivo selecionado: ' . $e->getMessage();
    }
} else {
    echo $arquivo->getException();
}
