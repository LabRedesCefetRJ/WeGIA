<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . "/dao/Conexao.php";

try {
    $pdo = Conexao::connect();
    $sql = "SELECT * FROM projeto_tipo ORDER BY descricao";
    $stmt = $pdo->query($sql);
    $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tipos);
} catch (Exception $e) {
    echo json_encode([]);
}
?>