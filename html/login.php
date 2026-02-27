<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

date_default_timezone_set("America/Sao_Paulo");

require_once '../dao/Conexao.php';
require_once '../Functions/funcoes.php';
require_once './seguranca/sessionStart.php';
require_once '../classes/Util.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../index.php");
        exit;
    }

    //Validação de entrada
    $cpf = $_POST['cpf'] ?? null;
    $senha = $_POST['pwd'] ?? null;

    if (!$cpf || !$senha) {
        header("Location: ../index.php?erro=dados_invalidos");
        exit;
    }

    $pdo = Conexao::connect();

    $stmt = $pdo->prepare("
        SELECT id_pessoa, cpf, senha, nome, adm_configurado, nivel_acesso 
        FROM pessoa 
        WHERE cpf = :cpf
        LIMIT 1
    ");

    $stmt->bindValue(':cpf', $cpf);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    //Usuário não encontrado
    if (!$usuario) {
        header("Location: ../index.php?erro=erro");
        exit;
    }

    //Verificação de senha - Estudar uma migração para password_verify com retrocompatibilidade para usuários antigos
	$pwd = hash('sha256', $senha);

    if ($pwd != $usuario['senha']) {
        header("Location: ../index.php?erro=erro");
        exit;
    }

    //Proteção contra Session Fixation
    session_regenerate_id(true);

    //Sessão autenticada
    $_SESSION['usuario']   = $usuario['cpf'];
    $_SESSION['id_pessoa'] = $usuario['id_pessoa'];
    $_SESSION['nome']      = $usuario['nome'];
    $_SESSION['nivel']     = $usuario['nivel_acesso'];
    $_SESSION['expira']    = time() + (30 * 60); // 30 minutos

    //Redirecionamento
    if (
        $usuario['adm_configurado'] == 0 &&
        $usuario['cpf'] === 'admin' &&
        $usuario['nivel_acesso'] == 2
    ) {
        header("Location: ../html/alterar_senha.php");
        exit;
    }

    header("Location: ../html/home.php");
    exit;

} catch (Throwable $e) {
    Util::tratarException($e);
}