<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

try {
    require_once '../../dao/Conexao.php';
    $pdo = Conexao::connect();

    $id = filter_input(INPUT_GET, 'id_pessoa', FILTER_SANITIZE_NUMBER_INT);
    $rg = trim(filter_input(INPUT_POST, 'rg', FILTER_SANITIZE_SPECIAL_CHARS));
    $orgao_emissor = trim(filter_input(INPUT_POST, 'orgao_emissor', FILTER_SANITIZE_SPECIAL_CHARS));
    $cpf = trim(filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS));
    $data_expedicao = trim(filter_input(INPUT_POST, 'data_expedicao', FILTER_UNSAFE_RAW));
    $idatendido_familiares = filter_input(INPUT_GET, 'idatendido_familiares', FILTER_SANITIZE_NUMBER_INT);

    if (!$id || $id < 0) {
        throw new InvalidArgumentException('O id da pessoa informado é inválido.', 400);
    }

    if (!$orgao_emissor || strlen($orgao_emissor) < 1) {
        throw new InvalidArgumentException('O órgão emissor informado não é válido.', 400);
    }

    //verificar se o RG está em um formato válido
    $regexRg = '/^(\d{1,2}\.?\d{3}\.?\d{3}-?[0-9Xx])$/';

    if (!preg_match($regexRg, $rg)) {
        throw new InvalidArgumentException('O RG informado não está em um formato válido', 400);
    }

    //verificar se é um CPF válido
    require_once '../../classes/Util.php';
    $util = new Util();

    if (!$util->validarCPF($cpf)) {
        throw new InvalidArgumentException('O CPF informado não é válido.', 400);
    }

    //verificar se é uma data válida
    $regexData = '/^(19[1-9][0-9]|20[0-9]{2}|21[0-9]{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/';
    if (!preg_match($regexData, $data_expedicao)) {
        throw new InvalidArgumentException('A data informada não está em um formato válido.', 400);
    }

    list($ano, $mes, $dia) = explode('-', $data_expedicao);

    if (!checkdate(intval($mes), intval($dia), intval($ano))) {
        throw new InvalidArgumentException('A data informada não é válida.', 400);
    }

    if (!$idatendido_familiares || $idatendido_familiares < 0) {
        throw new InvalidArgumentException('O id do familiar informado é inválido.', 400);
    }

    $sql = "UPDATE pessoa SET orgao_emissor=:orgao_emissor, data_expedicao=:data_expedicao, registro_geral=:registro_geral, cpf=:cpf WHERE id_pessoa = :id";

    $pessoa = $pdo->prepare($sql);
    $pessoa->bindValue(":id", $id, PDO::PARAM_INT);
    $pessoa->bindValue(":orgao_emissor", $orgao_emissor, PDO::PARAM_STR);
    $pessoa->bindValue(":data_expedicao", $data_expedicao, PDO::PARAM_STR);
    $pessoa->bindValue(":cpf", $cpf, PDO::PARAM_STR);
    $pessoa->bindValue(":registro_geral", $rg, PDO::PARAM_STR);
    
    if (!$pessoa->execute()) {
        throw new PDOException('Falha ao executar a consulta', 500);
    }

    header("Location: profile_dependente.php?id_dependente=$idatendido_familiares");
} catch (Exception $e) {
    error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
    http_response_code($e->getCode());
    if ($e instanceof PDOException) {
        echo json_encode(['erro' => 'Erro no servidor ao editar os documentos de um dependente.']);
    } else {
        echo json_encode(['erro' => $e->getMessage()]);
    }
}
