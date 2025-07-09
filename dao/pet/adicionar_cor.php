<?php
require_once '../Conexao.php';
require_once '../../html/permissao/permissao.php';
session_start();
permissao($_SESSION['id_pessoa'], 6, 3);

$entrada = trim($entrada);
$entrada = htmlspecialchars($entrada, ENT_QUOTES, 'UTF-8');
$entrada = strip_tags($entrada);

function validarEntrada($entrada) {
    if (!preg_match('/^[a-zA-Z\s-]+$/', $entrada)) {
        throw new InvalidArgumentException("Caracteres inválidos na cor");
    }
    return true;
}

try {
    $cor = filter_input(INPUT_POST, 'cor', FILTER_SANITIZE_STRING);
    validarEntrada($cor);
    $pdo = Conexao::connect();
    $sql = "INSERT INTO pet_cor(descricao) VALUES (:cor)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cor', $cor, PDO::PARAM_STR);
    $stmt->execute();
    
} 

catch (PDOException $e) {
    // Erro de banco de dados
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor ao inserir a cor do pet.']);
} 
?>