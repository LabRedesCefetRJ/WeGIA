<?php
session_start();
$config_path = '../../config.php';
require_once $config_path;

require_once  ROOT . "/dao/Conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ". WWW ."html/index.php");
    exit();
}

if (!isset($_SESSION['id_pessoa'])) {
    echo ("Não foi possível obter o id do usuário logado!<br/><a onclick='window.history.back()'>Voltar</a>");
    die();
}

require_once ROOT . '/html/permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 22, 3);

extract($_REQUEST);
if (isset($total_total)) {
    $qtd = intval($total_total);
}

function saida()
{
    extract($_REQUEST);
    if ($total_total < 1) {
        deleteEstoque();
        return false;
    }
    $pdo = Conexao::connect();
    $estoque = $pdo->query("SELECT * FROM estoque WHERE id_produto=$id_produto AND id_almoxarifado=$almoxarifado;");
    $estoque = $estoque->fetch(PDO::FETCH_ASSOC);
    if (!$estoque) {
        header("Location: ". WWW ."html/matPat/remover_produto.php?id_produto=$id_produto&flag=danger&msg=Não há nenhum produto do tipo no almoxarifado selecionado");
    }
    $saida = getSaida();
    if (!$saida) {
        $saida = addSaida();
    }
    addISaida($saida);
    deleteEstoque();
}

function getSaida()
{
    extract($_REQUEST);
    $id_pessoa = $_SESSION['id_pessoa'];
    $pdo = Conexao::connect();
    $saida = $pdo->query("SELECT * FROM saida WHERE id_destino=$destino AND id_almoxarifado=$almoxarifado AND id_tipo=$tipo_saida AND id_responsavel=$id_pessoa;");
    $saida = $saida->fetch(PDO::FETCH_ASSOC);
    return $saida;
}

function addSaida()
{
    extract($_REQUEST);
    $id_pessoa = $_SESSION['id_pessoa'];
    $pdo = Conexao::connect();
    $saida = $pdo->prepare("INSERT INTO saida (id_saida, id_destino, id_almoxarifado, id_tipo, id_responsavel, `data`, hora) VALUES (default, :d, :a, :t, :i, CURDATE(), CURRENT_TIME());") or header("Location: ". WWW ."html/matPat/remover_produto.php?id_produto=$id_produto&flag=error&msg=Houve um erro ao registrar a saída do item");
    $saida->bindValue(':d', $destino);
    $saida->bindValue(':a', $almoxarifado);
    $saida->bindValue(':t', $tipo_saida);
    $saida->bindValue(':i', $id_pessoa);
    $saida = $saida->execute();
    return $saida;
}

function addISaida($saida)
{
    extract($_REQUEST);
    $id_pessoa = $_SESSION['id_pessoa'];
    $id_saida = $saida['id_saida'];
    $pdo = Conexao::connect();
    $pdo->exec("INSERT INTO isaida (id_isaida, id_saida, id_produto, qtd) VALUES ( default , $id_saida , $id_produto , $total_total );") or header("Location: ". WWW ."html/matPat/remover_produto.php?id_produto=$id_produto&flag=error&msg=Houve um erro ao registrar a saída do item");
}

function deleteEstoque()
{
    extract($_REQUEST);
    $pdo = Conexao::connect();
    $pdo->exec("DELETE FROM estoque WHERE id_produto=$id_produto;") or header("Location: ". WWW ."html/matPat/remover_produto.php?id_produto=$id_produto&flag=error&msg=Houve um erro ao apagar registros de estoque do produto");
}

function ocultarProduto()
{
    $id_produto = trim(filter_input(INPUT_POST, 'id_produto', FILTER_SANITIZE_NUMBER_INT));

    if(!$id_produto || $id_produto < 1){
        http_response_code(400);
        header("Location: ". WWW ."html/matPat/listar_produto.php?flag=error&msg=Erro ao ocultar produto, o id informado não é válido");
        exit();
    }

    try {
        $pdo = Conexao::connect();

        $stmt = $pdo->prepare("UPDATE produto SET oculto=true WHERE id_produto = :id_produto");
        $stmt->execute(['id_produto' => $id_produto]);

        $stmt = $pdo->prepare("UPDATE ientrada SET oculto=true WHERE id_produto = :id_produto");
        $stmt->execute(['id_produto' => $id_produto]);

        $stmt = $pdo->prepare("UPDATE isaida SET oculto=true WHERE id_produto = :id_produto");
        $stmt->execute(['id_produto' => $id_produto]);

        header("Location: ". WWW ."html/matPat/remover_produto.php?id_produto=$id_produto&flag=success&msg=Produto ocultado com sucesso");
        exit();
    } catch (PDOException $e) {
        header("Location: ". WWW ."html/matPat/remover_produto.php?id_produto=$id_produto&flag=error&msg=Erro ao ocultar produto: " . $e->getMessage());
        exit();
    }
}

if ($qtd) {
    // Tem no estoque
    saida();
}

ocultarProduto();

header("Location: ". WWW ."html/matPat/listar_produto.php");