<?php
ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$config_path = '../../config.php';
require_once $config_path;

require_once ROOT . "/dao/Conexao.php";

header('Content-Type: application/json; charset=utf-8');

$id_almoxarifado = filter_input(INPUT_GET, 'id_almoxarifado', FILTER_VALIDATE_INT);

if (!$id_almoxarifado || $id_almoxarifado < 1) {
    ob_clean();
    echo json_encode(["error" => "id_almoxarifado inválido ou não fornecido"]);
    exit;
}

try {
    $pdo = Conexao::connect();

    $sql = "
        SELECT DISTINCT 
            p.id_produto, 
            p.descricao
        FROM produto p
        INNER JOIN estoque e 
            ON p.id_produto = e.id_produto
        WHERE e.id_almoxarifado = :id_almoxarifado
          AND p.oculto = false
          AND p.ativo = 1
          AND EXISTS (
              SELECT 1
              FROM ientrada ie
              INNER JOIN entrada en 
                  ON en.id_entrada = ie.id_entrada
              WHERE ie.id_produto = p.id_produto
                AND en.id_almoxarifado = e.id_almoxarifado
                AND ie.oculto = false
                AND en.ativo = 1
          )
        ORDER BY p.descricao
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_almoxarifado', $id_almoxarifado, PDO::PARAM_INT);
    $stmt->execute();

    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode($produtos);
    exit;
} catch (PDOException $e) {
    ob_clean();
    echo json_encode(["error" => "Erro ao consultar o banco de dados: " . $e->getMessage()]);
    exit;
}
?>