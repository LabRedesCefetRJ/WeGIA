<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'sessaoCookie.php';
if(session_status() == PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 9);

// Gerar backup altera estado (grava arquivos no servidor): só por POST e com token CSRF.
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
$csrfToken = $_POST['csrf_token'] ?? null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($csrfToken) || !Csrf::validateToken($csrfToken)) {
    header("Location: ./configuracao_geral.php?msg=error&err=" . urlencode("Requisição inválida. Recarregue a página e tente novamente."));
    exit();
}

require_once "./config_funcoes.php";

/*Buscando arquivo de configuração.. */
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';

// Define os redirecionamentos padrão para cada tipo de backup.
define('REDIRECT_URLS', [
    "./configuracao_geral.php",
    "./listar_backup.php",
    "./debug_info.php"
]);

if (PHP_OS != 'Linux') {
    header("Location: ./configuracao_geral.php?msg=error&err=Função de backup compatível apenas com Linux. Seu Sistema Operacional: " . PHP_OS . "");
    exit();
} else {
    $dbBackupFile = null;
    $siteLog = "";
    $errors = [];

    /*
        Identifica o tipo de ação:
            false: Backup do banco de dados e do site
            bd: backup do banco de dados apenas
            site: backup do site apenas
            default: Backup do banco de dados e do site
    */
    $action = $_POST['action'] ?? false;
    if (!$action) {
        $redirect = REDIRECT_URLS[0];

        try {
            $dbBackupFile = backupBD();
        } catch (Throwable $e) {
            $errors[] = "Houve um erro ao realizar o Backup do Banco de Dados:\n" . $e->getMessage();
        }

        // Executando Backup do Diretório do site
        $siteLog = backupSite();
        if (!$siteLog) {
            $errors[] = "Houve um erro ao realizar o Backup do Sistema.";
        }
    } else {
        if ($action == "bd") {
            $redirect = REDIRECT_URLS[1];

            try {
                $dbBackupFile = backupBD();
            } catch (Throwable $e) {
                $errors[] = "Houve um erro ao realizar o Backup do Banco de Dados:\n" . $e->getMessage();
            }
        } else if ($action == "site") {
            $redirect = REDIRECT_URLS[0];

            // Executando Backup do Diretório do site
            $siteLog = backupSite();
            if (!$siteLog) {
                $errors[] = "Houve um erro ao realizar o Backup do Sistema.";
            }
        } else {
            $redirect = REDIRECT_URLS[0];

            try {
                $dbBackupFile = backupBD();
            } catch (Throwable $e) {
                $errors[] = "Houve um erro ao realizar o Backup do Banco de Dados:\n" . $e->getMessage();
            }

            // Executando Backup do Diretório do site
            $siteLog = backupSite();
            if (!$siteLog) {
                $errors[] = "Houve um erro ao realizar o Backup do Sistema.";
            }
        }
    }

    if (!empty($errors)) {
        $log = implode("\n", $errors);
        header("Location: $redirect?msg=error&err=Houve um erro no processo de execução dos Backups&log=" . base64_encode($log));
        exit();
    }

    header("Location: $redirect?msg=success&sccs=Backup realizado com sucesso!");
    exit();
}
