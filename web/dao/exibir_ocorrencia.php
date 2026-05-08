<?php
//Requisições necessárias
require_once 'Conexao.php';
require_once '../html/permissao/permissao.php';

//Verifica se um usuário está logado e possui as permissões necessárias
session_start();
permissao($_SESSION['id_pessoa'], 11, 3);

$pdo = Conexao::connect();

try {
    $sql = 'SELECT * FROM atendido_ocorrencia_tipos';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $resultado = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $resultado[] = array('idatendido_ocorrencia_tipos' => $row['idatendido_ocorrencia_tipos'], 'descricao' => htmlspecialchars($row['descricao'], ENT_QUOTES, 'UTF-8'));
    }
    echo json_encode($resultado);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Ocorreu um erro ao processar a solicitação. Por favor, tente novamente mais tarde.";
}
