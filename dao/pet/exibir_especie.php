<?php
require_once '../Conexao.php';

try {
    // Conectar ao banco de dados
    $pdo = Conexao::connect();

    // Consulta SQL
    $sql = 'SELECT id_pet_especie, descricao FROM pet_especie';
    
    // Preparar e executar a consulta
    $stmt = $pdo->query($sql);

    // Array para armazenar os resultados
    $resultado = array();

    // Fetch os resultados e sanitizar os dados
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id_especie = htmlspecialchars($row['id_pet_especie'], ENT_QUOTES, 'UTF-8');
        $especie = htmlspecialchars($row['descricao'], ENT_QUOTES, 'UTF-8');

        $resultado[] = array('id_especie' => $id_especie, 'especie' => $especie);
    }

    // Retornar os resultados em formato JSON
    header('Content-Type: application/json');
    echo json_encode($resultado);

} catch (PDOException $e) {
    // Tratamento de erros de conexão
    echo json_encode(array('error' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()));
}
?>
