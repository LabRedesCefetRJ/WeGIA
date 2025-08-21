<?php
//realizar as sugestões de alteração da issue #311
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
} else {
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

$idDoc = filter_var($_REQUEST["id_doc"], FILTER_SANITIZE_NUMBER_INT);
$action = filter_var($_REQUEST["action"], FILTER_SANITIZE_SPECIAL_CHARS);

$actions = ['download', 'excluir', 'adicionar'];

if (!$action || !in_array($action, $actions)) {
    http_response_code(400);
    echo json_encode(['erro' => 'A ação informada não é válida.']);
    exit();
}

define("TYPEOF_EXTENSION", [
    'jpg' => 'image/jpg',
    'png' => 'image/png',
    'jpeg' => 'image/jpeg',
    'pdf' => 'application/pdf',
    'docx' => 'application/docx',
    'doc' => 'application/doc',
    'odp' => 'application/odp',
]);

switch ($action) {
    case 'download':
        download($pdo, $idDoc);
        break;
    case 'excluir':
        excluir($pdo, $idDoc);
        break;
    case 'adicionar':
        adicionar($pdo);
}

function validarIdDoc(int $id): bool
{
    if (!$id || $id < 1)
        return false;
    return true;
}

function download(PDO $pdo, int $idDoc)
{
    if (!validarIdDoc($idDoc)) {
        http_response_code(400);
        echo json_encode(['erro' => 'O id fornecido para o documento não é válido.']);
        exit();
    }

    $sql = "SELECT extensao_arquivo, nome_arquivo, UNCOMPRESS(arquivo) AS arquivo FROM funcionario_dependentes_docs WHERE id_doc=:idDoc";
    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':idDoc', $idDoc);

        $stmt->execute();

        $docdependente = $stmt->fetch(PDO::FETCH_ASSOC);
        header("Content-type: " . TYPEOF_EXTENSION[$docdependente["extensao_arquivo"]]);
        header("Content-Disposition: attachment; filename=" . $docdependente["nome_arquivo"]);
        ob_clean();
        flush();
        echo base64_decode($docdependente["arquivo"]);
    } catch (PDOException $e) {
        error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
        http_response_code($e->getCode());
        echo json_encode(['erro' => 'Erro no servidor ao baixar uma documentação.']);
    }
}

function excluir(PDO $pdo, int $idDoc)
{
    if (!validarIdDoc($idDoc)) {
        http_response_code(400);
        echo json_encode(['erro' => 'O id fornecido para o documento não é válido.']);
        exit();
    }

    //buscar $id_dependente de $_REQUEST
    $idDependente = filter_var($_REQUEST['id_dependente'], FILTER_VALIDATE_INT);

    if(!$idDependente || $idDependente < 1){
        http_response_code(400);
        echo json_encode(['erro' => 'O id do dependente fornecido não é válido']);
        exit();
    }

    $sql1 = "DELETE FROM funcionario_dependentes_docs WHERE id_doc=:idDoc";

    $sql2 = "SELECT doc.nome_docdependente AS descricao, ddoc.data, ddoc.id_doc FROM funcionario_dependentes_docs ddoc LEFT JOIN funcionario_docdependentes doc ON doc.id_docdependentes = ddoc.id_docdependentes WHERE ddoc.id_dependente=:idDependente";

    try {
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->bindParam(':idDoc', $idDoc);
        $stmt1->execute();

        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindParam(':idDependente', $idDependente);
        $stmt2->execute();

        $docdependente = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $docdependente = json_encode($docdependente);
        echo $docdependente;
    } catch (PDOException $e) {
        error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
        http_response_code($e->getCode());
        echo json_encode(['erro' => 'Erro no servidor ao excluir uma documentação']);
    }
}

function adicionar(PDO $pdo)
{
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$nome || strlen($nome) < 1) {
        http_response_code(400);
        echo json_encode(['erro' => 'O nome do documento fornecido é inválido.']);
        exit();
    }

    $sql = [
        "INSERT INTO funcionario_docdependentes (nome_docdependente) VALUES (:n);",
        "SELECT * FROM funcionario_docdependentes;"
    ];
    try {
        $prep = $pdo->prepare($sql[0]);
        $prep->bindValue(":n", $nome);
        $prep->execute();
        $query = $pdo->query($sql[1]);
        $docdependente = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($docdependente as $key => $value) {
            $docdependente[$key]['nome_docdependente'] = htmlspecialchars($value['nome_docdependente']);
        }

        $docdependente = json_encode($docdependente);
        echo $docdependente;
    } catch (PDOException $e) {
        error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
        http_response_code($e->getCode());
        echo json_encode(['erro' => 'Erro no servidor ao adicionar uma documentação.']);
    }
}
