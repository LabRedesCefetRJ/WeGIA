<?php
require_once '../Conexao.php';
require_once '../../html/permissao/permissao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

permissao($_SESSION['id_pessoa'], 6, 3);

function validarCor($cor)
{
    if (!preg_match('/^[a-zA-Z\s-]+$/', $cor)) {
        throw new InvalidArgumentException("Caracteres inválidos na cor", 400);
    }
    return true;
}

try {
    $cor = filter_input(INPUT_POST, 'cor', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    validarCor($cor);
    $pdo = Conexao::connect();
    $sql = "INSERT INTO pet_cor(descricao) VALUES (:cor)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cor', $cor, PDO::PARAM_STR);
    $stmt->execute();
} catch (Exception $e) {
    error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
    http_response_code($e->getCode());
    echo json_encode(['erro' => 'Erro ao inserir a cor do pet.']);
}
