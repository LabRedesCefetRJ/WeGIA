<pre>
<?php

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
}

require_once "./config_funcoes.php";

if (PHP_OS != 'Linux') {
    header("Location: ./listar_backup.php?msg=error&err=Função de backup compatível apenas com Linux. Seu Sistema Operacional: " . PHP_OS . "");
    exit();
} else {
    // Verifica Permissão do Usuário
    require_once '../permissao/permissao.php';
    permissao($_SESSION['id_pessoa'], 9);

    require_once "../../config.php";

    extract($_REQUEST);

    if (DEBUG) {
        $files = scandir(BKP_DIR);
        $ls = in_array($file, $files) ? $file : false;
        var_dump("cd " . BKP_DIR . " && ls $file", $file, $ls, $ls == $file . "\n");
    }
    if (in_array($file, scandir(BKP_DIR))) {
        // echo "File exists\n";
        if ($action == "remove") {

            $log = rmBackupBD($file);

            if ($log) {
                header("Location: ./listar_backup.php?msg=error&err=Houve um erro ao remover o arquivo!&log=" . base64_encode($log));
            } else {
                header("Location: ./listar_backup.php?msg=success&sccs=Backup removido com sucesso!");
            }
        } else if ($action == "restore") {
            $logAS = autosaveBD();
            if ($log && AUTOSAVE_ERROR_FATAL) {
                header("Location: ./listar_backup.php?msg=error&err=A ação não pode ser realizada devido a um erro no backup automático!&log=" . base64_encode($log));
            }
            $log = loadBackupDB($file);
            if ($log) {
                header("Location: ./listar_backup.php?msg=error&err=Houve um erro ao restaurar a Base de Dados!&log=" . base64_encode($log));
            } else {
                session_destroy();
                header("Location: ../../index.php");
            }
        } else {
            header("Location: ./listar_backup.php?msg=warning&warn=Nenhuma ação válida foi selecionada!");
        }
    }
}
?>
</pre>