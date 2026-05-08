<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit();
}

require_once "./config_funcoes.php";
require_once "../../config.php";
// Permissão
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 9);

// Restrição de SO
if (PHP_OS !== 'Linux') {
    header("Location: ./listar_backup.php?msg=error&err=" . urlencode(
        "Função de backup compatível apenas com Linux. Seu Sistema Operacional: " . PHP_OS
    ));
    exit();
}

$file   = $_REQUEST['file']   ?? null;
$action = $_REQUEST['action'] ?? null;

if (!$file || !$action) {
    header("Location: ./listar_backup.php?msg=warning&warn=" . urlencode("Parâmetros inválidos."));
    exit();
}

try {
    //Validação do nome
    if (!preg_match('/^[a-zA-Z0-9_-]+\.dump\.tar\.gz$/', $file)) {
        throw new RuntimeException('Nome de arquivo inválido.');
    }

    $backupDir = realpath(BKP_DIR);
    if ($backupDir === false) {
        throw new RuntimeException('Diretório de backup inválido.');
    }

    $filePath = realpath($backupDir . DIRECTORY_SEPARATOR . $file);

    if (
        $filePath === false ||
        !str_starts_with($filePath, $backupDir . DIRECTORY_SEPARATOR) ||
        !is_file($filePath)
    ) {
        throw new RuntimeException('Arquivo não encontrado.');
    }

    if (is_link($filePath)) {
        throw new RuntimeException('Arquivo inválido.');
    }

    // =========================
    // AÇÕES
    // =========================

    if ($action === "remove") {

        rmBackupBD($file);

        header("Location: ./listar_backup.php?msg=success&sccs=" . urlencode("Backup removido com sucesso!"));
        exit();
    }

    if ($action === "restore") {

        //autosave antes de restaurar
        try {
            autosaveBD();
        } catch (Throwable $e) {
            header("Location: ./listar_backup.php?msg=error&err=" . urlencode(
                "Falha ao gerar autosave antes da restauração: " . $e->getMessage()
            ));
            exit();
        }

        //restore
        try {
            if (!loadBackupDB($file)) {
                throw new RuntimeException("Falha ao carregar backup.");
            }
        } catch (Throwable $e) {
            header("Location: ./listar_backup.php?msg=error&err=" . urlencode(
                "Falha ao restaurar backup: " . $e->getMessage()
            ));
            exit();
        }

        //destrói sessão após restore
        session_destroy();

        header("Location: ../../index.php");
        exit();
    }

    // ação inválida
    header("Location: ./listar_backup.php?msg=warning&warn=" . urlencode("Ação inválida."));
    exit();
} catch (Throwable $e) {

    header("Location: ./listar_backup.php?msg=error&err=" . urlencode($e->getMessage()));
    exit();
}
