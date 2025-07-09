<?php
require_once '../Conexao.php';

try {
    $pdo = Conexao::connect();
    $sql = 'SELECT * FROM pet_especie';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resultado = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $resultado[] = array(
            'id_especie' => htmlspecialchars($row['id_pet_especie'], ENT_QUOTES, 'UTF-8'),
            'especie' => htmlspecialchars($row['descricao'], ENT_QUOTES, 'UTF-8')
        );
    }

    header('Content-Type: application/json');
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
   
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao recuperar espécies']);
}
?>