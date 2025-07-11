<?php
require_once '../Conexao.php';
require_once '../../html/permissao/permissao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

permissao($_SESSION['id_pessoa'], 6, 3);

try {
    $especie = filter_input(INPUT_POST, 'especie', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if(!$especie){
        throw new InvalidArgumentException('O nome da espécie informada não é válido.', 400);
    }

    $pdo = Conexao::connect();
    $sql = "INSERT INTO pet_especie(descricao) VALUES (:especie)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':especie', $especie, PDO::PARAM_STR);
    $stmt->execute();
} catch (Exception $e) {
    error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
    http_response_code($e->getCode());
    if($e instanceof PDOException){
        echo json_encode(['erro' => 'Erro no servidor ao inserir a espécie do pet.']);
    }else{
        echo json_encode(['erro' => $e->getMessage()]);
    }
}
