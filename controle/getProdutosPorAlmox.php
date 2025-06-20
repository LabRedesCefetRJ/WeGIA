<?php
require_once('../dao/Conexao.php');
require_once('../classes/Produto.php');
require_once('../config.php');

header('Content-Type: application/json');

$almox = $_REQUEST['almox'] ?? null;
$pdo = Conexao::connect();

if ($pdo && $almox !== null) {
    $query = "SELECT produto.id_produto, produto.codigo, produto.descricao, estoque.qtd, produto.preco 
              FROM produto 
              JOIN estoque ON produto.id_produto = estoque.id_produto 
              WHERE estoque.qtd > 0 AND estoque.id_almoxarifado = :almox";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':almox', $almox);
    $stmt->execute();

    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($produtos);
} else {
    echo json_encode([]);
}
