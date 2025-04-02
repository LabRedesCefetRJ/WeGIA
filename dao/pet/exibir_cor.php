<?php
require_once '../Conexao.php';

try {
    $pdo = Conexao::connect();
    $sql = 'SELECT * FROM pet_cor';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resultado = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $resultado[] = [
            'id_cor' => htmlspecialchars($row['id_pet_cor'], ENT_QUOTES, 'UTF-8'),
            'cor' => htmlspecialchars($row['descricao'], ENT_QUOTES, 'UTF-8')
        ];
    }
   
    // Define o tipo de conteúdo como JSON
    header('Content-Type: application/json');
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
   
} catch (PDOException $e) {
    // Tratamento de erro
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao recuperar cores']);
}
?>