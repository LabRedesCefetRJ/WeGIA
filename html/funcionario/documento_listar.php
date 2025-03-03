
<?php

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once "../../dao/Conexao.php";
try {
    $pdo = Conexao::connect();
    $docfuncional = $pdo->query("SELECT * FROM funcionario_docfuncional ORDER BY nome_docfuncional ASC;");
    $docfuncional = $docfuncional->fetchAll(PDO::FETCH_ASSOC);

    foreach ($docfuncional as $index => $doc) {
        $docfuncional[$index]['nome_docfuncional'] = htmlspecialchars($doc['nome_docfuncional']);
    }

    echo json_encode($docfuncional);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar os tipos de documentos']);
}
?>