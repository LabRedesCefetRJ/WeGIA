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
    $cep = trim(filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_SPECIAL_CHARS));
    $estado = trim(filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_SPECIAL_CHARS));
    $cidade = trim(filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_SPECIAL_CHARS));
    $bairro = trim(filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_SPECIAL_CHARS));
    $rua = trim(filter_input(INPUT_POST, 'rua', FILTER_SANITIZE_SPECIAL_CHARS));
    $numero = filter_input(INPUT_POST, 'numero_residencia', FILTER_SANITIZE_NUMBER_INT);
    $complemento = filter_input(INPUT_POST, 'complemento', FILTER_SANITIZE_SPECIAL_CHARS);
    $ibge = filter_input(INPUT_POST, 'ibge', FILTER_SANITIZE_NUMBER_INT);
    $idatendido_familiares = filter_input(INPUT_GET, 'idatendido_familiares', FILTER_SANITIZE_NUMBER_INT);

    if (!preg_match('/^\d{5}-?\d{3}$/', $cep)) {
        throw new InvalidArgumentException('O CEP informado não está em um formato válido.', 400);
    }

    if (!is_numeric($numero)) {
        $numero = '';
    }

    if (empty($estado) || empty($cidade) || empty($bairro) || empty($rua)) {
        throw new InvalidArgumentException('Preencha todos os campos obrigatórios antes de prosseguir.', 400);
    }

    if(!$idatendido_familiares || $idatendido_familiares < 1){
        throw new InvalidArgumentException('O id do familiar informado não é válido.', 400);
    }

    $sql = "UPDATE pessoa SET cep=:cep, estado=:estado, cidade=:cidade, bairro=:bairro, logradouro=:rua, numero_endereco=:numero, complemento=:complemento, ibge=:ibge WHERE id_pessoa = :id";

    $pessoa = $pdo->prepare($sql);
    $pessoa->bindValue(":id", $id, PDO::PARAM_INT);
    $pessoa->bindValue(":cep", $cep, PDO::PARAM_STR);
    $pessoa->bindValue(":estado", $estado, PDO::PARAM_STR);
    $pessoa->bindValue(":cidade", $cidade, PDO::PARAM_STR);
    $pessoa->bindValue(":bairro", $bairro, PDO::PARAM_STR);
    $pessoa->bindValue(":rua", $rua, PDO::PARAM_STR);
    $pessoa->bindValue(":numero", $numero);
    $pessoa->bindValue(":complemento", $complemento, PDO::PARAM_STR);
    $pessoa->bindValue(":ibge", $ibge, PDO::PARAM_INT);

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
