<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . "/dao/Conexao.php";

try {
    $pdo = Conexao::connect();
    $sql = "SELECT * FROM projeto_local ORDER BY nome";
    $stmt = $pdo->query($sql);
    $locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($locais);
} catch (Exception $e) {
    echo json_encode([]);
}
?>