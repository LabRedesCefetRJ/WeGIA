<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

$id_doc = $_POST["id_doc"] ?? null;
$action = $_POST["action"] ?? null;
$g_id_doc = $_GET["id_doc"] ?? null;
$g_action = $_GET["action"] ?? null;

define("TYPEOF_EXTENSION", [
    'jpg' => 'image/jpg',
    'png' => 'image/png',
    'jpeg' => 'image/jpeg',
    'pdf' => 'application/pdf',
    'docx' => 'application/docx',
    'doc' => 'application/doc',
    'odp' => 'application/odp',
]);

require_once "../../dao/Conexao.php";

try {
    $pdo = Conexao::connect();

    if ($action == "download" || $g_action == "download") {
        $sql = "SELECT extensao_arquivo, nome_arquivo, UNCOMPRESS(arquivo) AS arquivo FROM funcionario_dependentes_docs WHERE id_doc=:idDoc";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':idDoc', $g_id_doc);

        $stmt->execute();

        $docdependente = $stmt->fetch(PDO::FETCH_ASSOC);
        header("Content-type: " . TYPEOF_EXTENSION[$docdependente["extensao_arquivo"]]);
        header("Content-Disposition: attachment; filename=" . $docdependente["nome_arquivo"]);
        ob_clean();
        flush();
        echo base64_decode($docdependente["arquivo"]);
    } else if ($action == "excluir" || $g_action == "excluir") {
        $sql1 = "DELETE FROM funcionario_dependentes_docs WHERE id_doc=:idDoc";

        $sql2 = "SELECT doc.nome_docdependente AS descricao, ddoc.data, ddoc.id_doc FROM funcionario_dependentes_docs ddoc LEFT JOIN funcionario_docdependentes doc ON doc.id_docdependentes = ddoc.id_docdependentes WHERE ddoc.id_dependente=:idDependente";

        $stmt1 = $pdo->prepare($sql1);
        $stmt1->bindParam(':idDoc', $id_doc);
        $stmt1->execute();

        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindParam(':idDependente', $id_dependente);
        $stmt2->execute();

        $docdependente = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $docdependente = json_encode($docdependente);
        echo $docdependente;
    } else if ($action == "adicionar" || $g_action == "adicionar") {
        $sql = [
            "INSERT INTO funcionario_docdependentes (nome_docdependente) VALUES (:n);",
            "SELECT * FROM funcionario_docdependentes;"
        ];
    }
} catch (Exception $e) {
    require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
    Util::tratarException($e);
}

die();
