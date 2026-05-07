<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . "/dao/Conexao.php";

try {
    $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($tipo)) {
        throw new Exception('Tipo não informado');
    }
    
    $pdo = Conexao::connect();
    $sql = "INSERT INTO projeto_tipo (descricao) VALUES (:descricao)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':descricao', $tipo);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>