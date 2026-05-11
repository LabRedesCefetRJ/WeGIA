<?php
require_once '../Conexao.php';
try {
    $pdo = Conexao::connect();
    
    // Prevenção contra SQL Injection e segurança
    $sql = 'SELECT * FROM pet_raca';
    $stmt = $pdo->query($sql);

    if (!$stmt) {
        throw new Exception('Erro ao executar a consulta SQL.');
    }

    $resultado = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $resultado[] = array('id_raca' => $row['id_pet_raca'], 'raca' => htmlspecialchars($row['descricao']));
    }

    echo json_encode($resultado);
} catch (PDOException $e) {
    // Tratamento de erros específicos do PDO
    echo json_encode(array('error' => 'Erro de banco de dados: ' . $e->getMessage()));
} catch (Exception $e) {
    // Tratamento de erros genéricos
    echo json_encode(array('error' => 'Erro: ' . $e->getMessage()));
}
?>
