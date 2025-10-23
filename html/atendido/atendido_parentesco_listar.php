<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
}else{
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 12, 7);

require_once "../../dao/Conexao.php";

try {
    $pdo = Conexao::connect();
    $parentescos = $pdo->query("SELECT * FROM atendido_parentesco ORDER BY parentesco ASC;")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($parentescos as $index => $parentesco) {
        $parentescos[$index]['parentesco'] = htmlspecialchars($parentescos[$index]['parentesco']);
    }

    echo json_encode($parentescos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => "Erro no servidor ao buscar parentescos: {$e->getMessage()}"]);
}
