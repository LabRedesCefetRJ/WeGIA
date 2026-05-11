<?php
extract($_REQUEST);

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);


require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

if ($action == "adicionar_descricao") {
    $descricao = trim(filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING));

    if (!$descricao || strlen($descricao) == 0) {
        http_response_code(400);
        echo json_encode(['erro' => 'A descrição não pode ser vazia']);
        exit();
    }

    $sql = "INSERT INTO funcionario_listainfo (descricao) VALUES (:descricao)";
    $response_query = "SELECT * FROM funcionario_listainfo;";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':descricao', $descricao);

        $stmt->execute();

        $informacoes = $pdo->query($response_query)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($informacoes as $index => $informacao) {
            $informacoes[$index]['descricao'] = htmlspecialchars($informacao['descricao']);
        }

        echo json_encode($informacoes);
    } catch (PDOException $th) {
        echo json_encode($th);
    }
}

if ($action == "adicionar") {
    $sql = "INSERT INTO funcionario_outrasinfo VALUES (default , :idFuncionario, :idDescricao, :dados)";
    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':idFuncionario', $id_funcionario);
        $stmt->bindParam(':idDescricao', $id_descricao);
        $stmt->bindParam(':dados', $dados);

        $stmt->execute();

        listar($pdo);
    } catch (PDOException $th) {
        echo json_encode($th);
    }
}

if ($action == "remover") {
    $sql = "DELETE FROM funcionario_outrasinfo WHERE idfunncionario_outrasinfo =:idDescricao";
    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':idDescricao', $id_descricao);

        $stmt->execute();

        listar($pdo);
    } catch (PDOException $th) {
        echo json_encode($th);
    }
}

if ($action == "idInfoAdicional") {
    try {
        $result = $pdo->query("SELECT max(idfunncionario_outrasinfo) FROM  funcionario_outrasinfo;")->fetch(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (PDOException $th) {
        echo json_encode($th);
    }
}

if ($action == "selectDescricao") {
    try {
        echo json_encode($pdo->query("SELECT * FROM funcionario_listainfo;")->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $th) {
        echo json_encode($th);
    }
}

if ($action == "listar") {
    listar($pdo);
}

function listar(PDO $pdo)
{
    $response_query = "SELECT * FROM funcionario_outrasinfo o JOIN funcionario_listainfo l ON o.funcionario_listainfo_idfuncionario_listainfo = l.idfuncionario_listainfo;";
    try {
        $informacoes = $pdo->query($response_query)->fetchAll(PDO::FETCH_ASSOC);
        
        //evitar XSS
        foreach ($informacoes as $index => $informacao) {
            $informacoes[$index]['dados'] = htmlspecialchars($informacao['dados']);
            $informacoes[$index]['descricao'] = htmlspecialchars($informacao['descricao']);
        }
        
        echo json_encode($informacoes);
    } catch (PDOException $th) {
        echo json_encode($th);
    }
}

die();
