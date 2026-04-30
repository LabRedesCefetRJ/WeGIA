<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . "/dao/Conexao.php";

try {
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($status)) {
        throw new Exception('Status não informado');
    }
    
    $pdo = Conexao::connect();
    $sql = "INSERT INTO projeto_status (descricao) VALUES (:descricao)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':descricao', $status);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>