<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . "/dao/Conexao.php";

try {
    $local = filter_input(INPUT_POST, 'local', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($local)) {
        throw new Exception('Local não informado');
    }
    
    $pdo = Conexao::connect();
    $sql = "INSERT INTO projeto_local (nome) VALUES (:nome)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':nome', $local);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>