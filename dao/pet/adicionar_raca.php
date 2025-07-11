<?php
require_once '../Conexao.php';
require_once '../../html/permissao/permissao.php';

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

permissao($_SESSION['id_pessoa'], 6, 3);

$raca = filter_input(INPUT_POST, 'raca', FILTER_SANITIZE_SPECIAL_CHARS);

try {

    if(!$raca || strlen($raca) < 1){
        throw new InvalidArgumentException('O nome da raça informado não é válido.', 400);
    }

    $pdo = Conexao::connect();
    $sql = "INSERT INTO pet_raca(descricao) VALUES (:raca)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':raca', $raca, PDO::PARAM_STR);
    $stmt->execute(); // Executa a inserção
} catch (Exception $e) {
    error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
    http_response_code($e->getCode());
    if($e instanceof PDOException){
        echo json_encode(['erro' => 'Erro no servidor ao inserir a raça do pet.']);
    }else{
        echo json_encode(['erro' => $e->getMessage()]);
    }
}