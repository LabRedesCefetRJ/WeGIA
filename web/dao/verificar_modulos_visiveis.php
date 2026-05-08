<?php
//Verifica se o usuário está autenticado no sistema
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    $uriBase = dirname($_SERVER['PHP_SELF'], 2);
    header("Location: {$uriBase}/index.php");
    exit();
}

try {
    //Estabelece comunicação com o banco de dados
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';
    $pdo = Conexao::connect();

    //Realiza a consulta
    $sql = "SELECT `id_recurso` FROM `modulos_visiveis` WHERE `visivel` = 1";
    $modulos = $pdo->query($sql)->fetchAll(PDO::FETCH_NUM);

    //Envia a resposta
    echo json_encode($modulos);
} catch (Exception $e) {
    require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
    Util::tratarException($e);
}
