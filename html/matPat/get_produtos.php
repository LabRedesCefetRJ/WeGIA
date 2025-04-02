<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("../dao/Conexao.php");

if (isset($_GET['id_almoxarifado'])) {
    $id_almoxarifado = $_GET['id_almoxarifado'];

    try {
        // Conectar ao banco de dados
        $pdo = Conexao::connect();

        // Preparar e executar a consulta
        $sql = "
            SELECT p.id_produto, p.descricao
            FROM produto p
            JOIN estoque e ON p.id_produto = e.id_produto
            WHERE e.id_almoxarifado = :id_almoxarifado
            ORDER BY p.descricao
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_almoxarifado', $id_almoxarifado, PDO::PARAM_INT);
        $stmt->execute();

        // Obter resultados
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($produtos) {
            echo json_encode($produtos);
        } else {
            echo json_encode([]); // Nenhum produto encontrado
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erro ao consultar o banco de dados: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "id_almoxarifado não fornecido"]);
}
?>
