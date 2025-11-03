<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
} else {
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

extract($_REQUEST);
$action = trim($_REQUEST['action']);

try {
    require_once "../../dao/Conexao.php";
    $pdo = Conexao::connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao estabelecer conexão no banco de dados do servidor.']);
    exit();
}

if ($action == "tipo_adicionar") {
    $descricao = trim(filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS));
    $query = "SELECT * FROM funcionario_remuneracao_tipo WHERE descricao = :descricao";
    $sql = "INSERT INTO funcionario_remuneracao_tipo VALUES (default , :descricao)";
    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo '{"aviso": true, "msg": "Tipo de remuneração já existe!"}';
            die();
        }

        $prep = $pdo->prepare($sql);
        $prep->bindValue(":descricao", $descricao);
        $prep->execute();
        $response = $pdo->query("SELECT idfuncionario_remuneracao_tipo as id, descricao FROM funcionario_remuneracao_tipo;");
        echo json_encode($response->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao adicionar um tipo de remuneração no banco de dados do servidor.']);
        exit();
    }
    die();
}

if ($action == "remuneracao_adicionar") {
    $idFuncionario = trim(filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT));
    $idTipo = trim(filter_input(INPUT_POST, 'id_tipo', FILTER_VALIDATE_INT));
    $valor = trim(filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT));
    $inicio = trim(filter_input(INPUT_POST, 'inicio', FILTER_UNSAFE_RAW));
    $fim = trim(filter_input(INPUT_POST, 'fim', FILTER_UNSAFE_RAW));

    try {

        if (!$idFuncionario || !is_numeric($idFuncionario) || $idFuncionario < 1)
            throw new InvalidArgumentException('O id de um funcionário deve ser um inteiro positivo.', 400);

        if (!$idTipo || !is_numeric($idTipo) || $idTipo < 1)
            throw new InvalidArgumentException('O id de um tipo deve ser um inteiro positivo.', 400);

        if (!$valor || !is_numeric($valor) || $valor < 0)
            throw new InvalidArgumentException('O valor de uma remuneração não pode possuir um valor negativo.');

        $inicioDateArray = explode('-', $inicio);
        $fimDateArray = explode('-', $fim);

        if (!checkdate(intval($inicioDateArray[1]), intval($inicioDateArray[2]), intval($inicioDateArray[0])))
            throw new InvalidArgumentException('A data de início informada não é válida.', 400);

        if (!checkdate(intval($fimDateArray[1]), intval($fimDateArray[2]), intval($fimDateArray[0])))
            throw new InvalidArgumentException('A data de fim informada não é válida.', 400);

        $sql = "INSERT INTO funcionario_remuneracao VALUES (default , :idFuncionario, :idTipo, :valor, :inicio, :fim);";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':idFuncionario', $idFuncionario);
        $stmt->bindParam(':idTipo', $idTipo);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':inicio', $inicio);
        $stmt->bindParam(':fim', $fim);

        $stmt->execute();
    } catch (Exception $e) {
        Util::tratarException($e);
        exit();
    }

    $action = "listar";
}

if ($action == "remuneracao_editar") {
    $idRemuneracao = trim(filter_input(INPUT_POST, 'id_remuneracao', FILTER_VALIDATE_INT));
    $inicio = trim(filter_input(INPUT_POST, 'inicio', FILTER_UNSAFE_RAW));
    $fim = trim(filter_input(INPUT_POST, 'fim', FILTER_UNSAFE_RAW));
    $idFuncionarioRemuneracao = trim(filter_input(INPUT_POST, 'id_funcionario_remuneracao', FILTER_VALIDATE_INT));

    if (!$idRemuneracao || !is_numeric($idRemuneracao) || $idRemuneracao < 1) {
        http_response_code(400);
        echo json_encode(['erro' => 'O id de uma remuneração deve ser um inteiro positivo.']);
    }

    if (!$idFuncionarioRemuneracao || !is_numeric($idFuncionarioRemuneracao) || $idFuncionarioRemuneracao < 1) {
        http_response_code(400);
        echo json_encode(['erro' => 'O id de uma remuneração deve ser um inteiro positivo.']);
    }

    $inicioDateArray = explode('-', $inicio);
    $fimDateArray = explode('-', $fim);

    if (!checkdate(intval($inicioDateArray[1]), intval($inicioDateArray[2]), intval($inicioDateArray[0]))) {
        http_response_code(400);
        echo json_encode(['erro' => 'A data de início informada não é válida.']);
    }

    if (!checkdate(intval($fimDateArray[1]), intval($fimDateArray[2]), intval($fimDateArray[0]))) {
        http_response_code(400);
        echo json_encode(['erro' => 'A data de fim informada não é válida.']);
    }

    try {
        $sql = "UPDATE funcionario_remuneracao SET funcionario_remuneracao_tipo_idfuncionario_remuneracao_tipo=:idRemuneracao, valor=:valor, inicio=:inicio, fim=:fim WHERE idfuncionario_remuneracao=:idFuncionarioRemuneracao";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':idRemuneracao', $idRemuneracao);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':inicio', $inicio);
        $stmt->bindParam(':fim', $fim);
        $stmt->bindParam(':idFuncionarioRemuneracao', $idFuncionarioRemuneracao);

        $stmt->execute();
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao editar uma remuneração no banco de dados.']);
        exit();
    }
}

if ($action == "remover") {
    try {

        if (!$id_remuneracao || !is_numeric($id_remuneracao) || $id_remuneracao < 1) {
            http_response_code(400);
            echo json_encode(['erro' => 'O id de uma remuneração deve ser um inteiro positivo.']);
            exit();
        }

        $sqlRemover = "DELETE FROM funcionario_remuneracao WHERE idfuncionario_remuneracao =:idRemuneracao";

        $stmt = $pdo->prepare($sqlRemover);

        $stmt->bindParam(':idRemuneracao', $id_remuneracao);

        $stmt->execute();

        $action = "listar";
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao remover uma remuneração no banco de dados.']);
        exit();
    }
}

if ($action == "listar") {

    if (!$id_funcionario || !is_numeric($id_funcionario) || $id_funcionario < 1) {
        http_response_code(400);
        echo json_encode(['erro' => 'O id de uma remuneração deve ser um inteiro positivo.']);
        exit();
    }

    try {
        $sql = "SELECT 
        rem.idfuncionario_remuneracao as id_remuneracao, rem.funcionario_remuneracao_tipo_idfuncionario_remuneracao_tipo as id_tipo, tipo.descricao, rem.inicio, rem.fim, rem.valor
        FROM funcionario_remuneracao rem 
        JOIN funcionario_remuneracao_tipo tipo ON tipo.idfuncionario_remuneracao_tipo = rem.funcionario_remuneracao_tipo_idfuncionario_remuneracao_tipo 
        WHERE rem.funcionario_id_funcionario =:idFuncionario";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idFuncionario', $id_funcionario);
        $stmt->execute();

        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //Formata data para o formato brasileiro
        foreach ($lista as $num => $remuneracao) {
            $dataInicio = new DateTime($remuneracao['inicio']);
            $dataFim = new DateTime($remuneracao['fim']);

            $lista[$num]['inicio'] = $dataInicio->format('d/m/Y');
            $lista[$num]['fim'] = $dataFim->format('d/m/Y');
        }
        echo json_encode($lista);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao listar as remunerações do banco de dados.']);
        exit();
    }
}
