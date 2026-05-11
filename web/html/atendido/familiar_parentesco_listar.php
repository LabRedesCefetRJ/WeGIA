<?php

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once "../../dao/Conexao.php";
try {
    $pdo = Conexao::connect();
    $parentesco = $pdo->query("SELECT * FROM atendido_parentesco ORDER BY parentesco ASC;");
    $parentesco = $parentesco->fetchAll(PDO::FETCH_ASSOC);
    $parentesco = json_encode($parentesco);
    echo $parentesco;
} catch (PDOException $e) {
    echo "Erro ao acessar os dados da tabela: " . $e->getMessage();
}