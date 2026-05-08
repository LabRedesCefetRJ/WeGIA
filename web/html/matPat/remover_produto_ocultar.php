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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . WWW . "html/matPat/listar_produto.php?flag=error&msg=" . urlencode("Método de requisição inválido"));
    exit();
}

function redirecionarRemocaoProduto($id_produto, $flag, $mensagem)
{
    header("Location: " . WWW . "html/matPat/remover_produto.php?id_produto=" . (int)$id_produto . "&flag=$flag&msg=" . urlencode($mensagem));
    exit();
}

function redirecionarListaProdutos($flag, $mensagem)
{
    header("Location: " . WWW . "html/matPat/listar_produto.php?flag=$flag&msg=" . urlencode($mensagem));
    exit();
}

function registrarSaida($id_produto, $total_total, $destino, $almoxarifado, $tipo_saida, $id_pessoa)
{
    $pdo = Conexao::connect();
    try {
        $stmtEstoque = $pdo->prepare("SELECT id_produto FROM estoque WHERE id_produto = :id_produto AND id_almoxarifado = :id_almoxarifado LIMIT 1");
        $stmtEstoque->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmtEstoque->bindValue(':id_almoxarifado', $almoxarifado, PDO::PARAM_INT);
        $stmtEstoque->execute();

        if (!$stmtEstoque->fetch(PDO::FETCH_ASSOC)) {
            redirecionarRemocaoProduto($id_produto, 'danger', 'Não há nenhum produto do tipo no almoxarifado selecionado');
        }

        $pdo->beginTransaction();

        $id_saida = getSaida($pdo, $destino, $almoxarifado, $tipo_saida, $id_pessoa);
        if ($id_saida < 1) {
            $id_saida = addSaida($pdo, $destino, $almoxarifado, $tipo_saida, $id_pessoa);
        }

        addISaida($pdo, $id_saida, $id_produto, $total_total);
        deleteEstoque($pdo, $id_produto);

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        redirecionarRemocaoProduto($id_produto, 'error', 'Houve um erro ao registrar a saída do item');
    }
}

function getSaida($pdo, $destino, $almoxarifado, $tipo_saida, $id_pessoa)
{
    $stmt = $pdo->prepare("SELECT id_saida FROM saida WHERE id_destino = :id_destino AND id_almoxarifado = :id_almoxarifado AND id_tipo = :id_tipo AND id_responsavel = :id_responsavel LIMIT 1");
    $stmt->bindValue(':id_destino', $destino, PDO::PARAM_INT);
    $stmt->bindValue(':id_almoxarifado', $almoxarifado, PDO::PARAM_INT);
    $stmt->bindValue(':id_tipo', $tipo_saida, PDO::PARAM_INT);
    $stmt->bindValue(':id_responsavel', $id_pessoa, PDO::PARAM_INT);
    $stmt->execute();

    $saida = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$saida) {
        return 0;
    }

    return (int) $saida['id_saida'];
}

function addSaida($pdo, $destino, $almoxarifado, $tipo_saida, $id_pessoa)
{
    $stmt = $pdo->prepare("INSERT INTO saida (id_saida, id_destino, id_almoxarifado, id_tipo, id_responsavel, `data`, hora) VALUES (default, :id_destino, :id_almoxarifado, :id_tipo, :id_responsavel, CURDATE(), CURRENT_TIME());");
    $stmt->bindValue(':id_destino', $destino, PDO::PARAM_INT);
    $stmt->bindValue(':id_almoxarifado', $almoxarifado, PDO::PARAM_INT);
    $stmt->bindValue(':id_tipo', $tipo_saida, PDO::PARAM_INT);
    $stmt->bindValue(':id_responsavel', $id_pessoa, PDO::PARAM_INT);
    $stmt->execute();

    return (int) $pdo->lastInsertId();
}

function addISaida($pdo, $id_saida, $id_produto, $total_total)
{
    $stmt = $pdo->prepare("INSERT INTO isaida (id_isaida, id_saida, id_produto, qtd) VALUES (default, :id_saida, :id_produto, :qtd)");
    $stmt->bindValue(':id_saida', $id_saida, PDO::PARAM_INT);
    $stmt->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
    $stmt->bindValue(':qtd', $total_total, PDO::PARAM_INT);
    $stmt->execute();
}

function deleteEstoque($pdo, $id_produto)
{
    $stmt = $pdo->prepare("DELETE FROM estoque WHERE id_produto = :id_produto");
    $stmt->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
    $stmt->execute();
}

function ocultarProduto($id_produto)
{
    try {
        $pdo = Conexao::connect();

        $stmt = $pdo->prepare("UPDATE produto SET oculto=true WHERE id_produto = :id_produto");
        $stmt->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $pdo->prepare("UPDATE ientrada SET oculto=true WHERE id_produto = :id_produto");
        $stmt->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $pdo->prepare("UPDATE isaida SET oculto=true WHERE id_produto = :id_produto");
        $stmt->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt->execute();

        redirecionarRemocaoProduto($id_produto, 'success', 'Produto ocultado com sucesso');
    } catch (PDOException $e) {
        redirecionarRemocaoProduto($id_produto, 'error', 'Erro ao ocultar produto.');
    }
}

$id_produto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);

if (!$id_produto || $id_produto < 1) {
    http_response_code(400);
    redirecionarListaProdutos('error', 'Erro ao ocultar produto, o id informado não é válido');
}

$total_total = filter_input(INPUT_POST, 'total_total', FILTER_VALIDATE_INT);
if ($total_total === false || $total_total === null) {
    $total_total = 0;
}

if ($total_total < 0) {
    http_response_code(400);
    redirecionarRemocaoProduto($id_produto, 'error', 'Erro ao ocultar produto, a quantidade informada não é válida');
}

if ($total_total > 0) {
    $destino = filter_input(INPUT_POST, 'destino', FILTER_VALIDATE_INT);
    $almoxarifado = filter_input(INPUT_POST, 'almoxarifado', FILTER_VALIDATE_INT);
    $tipo_saida = filter_input(INPUT_POST, 'tipo_saida', FILTER_VALIDATE_INT);

    if (!$destino || !$almoxarifado || !$tipo_saida) {
        http_response_code(400);
        redirecionarRemocaoProduto($id_produto, 'error', 'Preencha tipo de saída, destino e almoxarifado para registrar a saída');
    }

    registrarSaida($id_produto, $total_total, $destino, $almoxarifado, $tipo_saida, (int) $_SESSION['id_pessoa']);
}

ocultarProduto($id_produto);
