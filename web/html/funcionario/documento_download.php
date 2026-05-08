<?php

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once "../../dao/Conexao.php";
require_once "./Documento.php";

define("TYPEOF_EXTENSION", [
    'jpg' => 'image/jpg',
    'png' => 'image/png',
    'jpeg' => 'image/jpeg',
    'pdf' => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'doc' => 'application/msword',
    'odp' => 'application/vnd.oasis.opendocument.presentation',
]);

$id_doc = filter_var($_GET["id_doc"], FILTER_VALIDATE_INT);

if ($id_doc !== false && $id_doc > 0) {
    $arquivo = new DocumentoFuncionario($id_doc);


    if (!$arquivo->getException()) {
        header("Content-Type: " . TYPEOF_EXTENSION[$arquivo->getExtensao()]);
        header("Content-Disposition: attachment; filename=" . $arquivo->getNome());

        ob_clean();
        flush();

        echo $arquivo->getDocumento();
    } else {
        echo $arquivo->getException();
    }
} else {
    echo "ID de documento inválido.";
}

die();
