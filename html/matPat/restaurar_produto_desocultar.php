<?php

require_once  "../dao/Conexao.php";
require_once "./permissao/permissao.php";

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
}

if (!isset($_SESSION['id_pessoa'])) {
    echo ("Não foi possível obter o id do usuário logado!<br/><a onclick='window.history.back()'>Voltar</a>");
    die();
}

permissao($_SESSION['id_pessoa'], 22, 3);

extract($_REQUEST);
$id_produto = intval($id_produto);
if ($id_produto < 1) {
    header("Location: ./restaurar_produto.php?id_produto=$id_produto&flag=error&msg=Id inválido: Deve ser maior do que 0");
}

extract($_REQUEST);

try {
    $sql1 = "SELECT * FROM produto WHERE id_produto=:idProduto";
    $sql2 = "UPDATE produto SET oculto=false WHERE id_produto=$id_produto;";
    $sql3 = "UPDATE isaida SET oculto=false WHERE id_produto=$id_produto;";
    $sql4 = "UPDATE ientrada SET oculto=false WHERE id_produto=$id_produto;";

    $pdo = Conexao::connect();

    $stmt1 = $pdo->prepare($sql1);
    $stmt1->bindParam(':idProduto', $id_produto);
    $stmt1->execute();


    if ($stmt1->rowCount() > 0) {
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindParam(':idProduto', $id_produto);
        $stmt2->execute();

        $stmt3 = $pdo->prepare($sql3);
        $stmt3->bindParam(':idProduto', $id_produto);
        $stmt3->execute();

        $stmt4 = $pdo->prepare($sql4);
        $stmt4->bindParam(':idProduto', $id_produto);
        $stmt4->execute();
    } else {
        header("Location: ./restaurar_produto.php?id_produto=$id_produto&flag=error&msg=Não foi possível localizar o produto com id = '$id_produto'!");
        exit();
    }

    header("Location: ./cadastro_produto.php");
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor ao desocultar um produto.']);
    exit();
}
