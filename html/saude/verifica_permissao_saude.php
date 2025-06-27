<?php
require_once "../../dao/Conexao.php";

if (!isset($_SESSION["usuario"]) || !isset($_SESSION['id_pessoa'])) {
    header("Location: ../../index.php");
    exit();
}

try {
    $pdo = Conexao::connect();
    $id_pessoa = $_SESSION['id_pessoa'];

    $stmt = $pdo->prepare("SELECT id_cargo FROM funcionario WHERE id_pessoa = :id_pessoa");
    $stmt->bindParam(':id_pessoa', $id_pessoa, PDO::PARAM_INT);
    $stmt->execute();
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$funcionario) {
        throw new Exception("Funcionário não encontrado.");
    }

    $id_cargo = $funcionario['id_cargo'];

    $stmt = $pdo->prepare("
        SELECT p.id_acao 
        FROM permissao p 
        JOIN recurso r ON p.id_recurso = r.id_recurso 
        WHERE p.id_cargo = :id_cargo AND r.descricao = 'Módulo Saúde'
    ");
    $stmt->bindParam(':id_cargo', $id_cargo, PDO::PARAM_INT);
    $stmt->execute();
    $permissao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$permissao || $permissao['id_acao'] < 5) {
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ../home.php?msg_c=" . urlencode($msg));
        exit();
    }

    // Se quiser usar a variável $permissao['id_acao'] depois, pode deixá-la pública com:
    $_SESSION['permissao_saude'] = $permissao['id_acao'];
} catch (Exception $e) {
    $msg = "Erro ao verificar permissões: " . $e->getMessage();
    header("Location: ../home.php?msg_c=" . urlencode($msg));
    exit();
}
