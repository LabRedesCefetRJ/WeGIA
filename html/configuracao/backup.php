<pre>
<?php
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: ../../index.php");
    }

    // Verifica Permissão do Usuário
	require_once '../permissao/permissao.php';
    permissao($_SESSION['id_pessoa'], 9);

    require_once "./config_funcoes.php";
    
    /*Buscando arquivo de configuração.. */
    $config_path = "config.php";
    if(file_exists($config_path)){
        require_once($config_path);
    }
    else{
        while(true){
            $config_path = "../" . $config_path;
            if(file_exists($config_path)) break;
        }
    require_once($config_path);
    }


    // require_once("../../config.php");

    // Define os redirecionamentos padrão para cada tipo de backup.
    define('REDIRECT_URLS', [
        "./configuracao_geral.php",
        "./listar_backup.php",
        "./debug_info.php"
    ]);

    if (PHP_OS != 'Linux'){
        header("Location: ./configuracao_geral.php?msg=error&err=Função de backup compatível apenas com Linux. Seu Sistema Operacional: ".PHP_OS."");
    }else{
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
        $action = $_GET['action'] ?? false;
        if(!$action){
            $redirect = REDIRECT_URLS[0];

            try {
                $dbBackupFile = backupBD();
            } catch (Throwable $e) {
                $errors[] = "Houve um erro ao realizar o Backup do Banco de Dados:\n" . $e->getMessage();
            }
    
            // Executando Backup do Diretório do site
            $siteLog = backupSite();
            if ($siteLog) {
                $errors[] = "Houve um erro ao realizar o Backup do Sistema:\n" . $siteLog;
            }

        }else{
            if ($action == "bd"){
                $redirect = REDIRECT_URLS[1];

                try {
                    $dbBackupFile = backupBD();
                } catch (Throwable $e) {
                    $errors[] = "Houve um erro ao realizar o Backup do Banco de Dados:\n" . $e->getMessage();
                }

            }else if ($action == "site"){
                $redirect = REDIRECT_URLS[0];
    
                // Executando Backup do Diretório do site
                $siteLog = backupSite();
                if ($siteLog) {
                    $errors[] = "Houve um erro ao realizar o Backup do Sistema:\n" . $siteLog;
                }

            }
            else {
                $redirect = REDIRECT_URLS[0];

                try {
                    $dbBackupFile = backupBD();
                } catch (Throwable $e) {
                    $errors[] = "Houve um erro ao realizar o Backup do Banco de Dados:\n" . $e->getMessage();
                }
    
                // Executando Backup do Diretório do site
                $siteLog = backupSite();
                if ($siteLog) {
                    $errors[] = "Houve um erro ao realizar o Backup do Sistema:\n" . $siteLog;
                }

            }
        }

        // Caso exista um redirect definido, ele terá prioridade sobre o padrão
        $redirect = $_GET['redirect'] ?? $redirect;
    
        if (!empty($errors)){
            $log = implode("\n", $errors);
            header("Location: $redirect?msg=error&err=Houve um erro no processo de execução dos Backups&log=".base64_encode($log));
            exit();
        }

        header("Location: $redirect?msg=success&sccs=Backup realizado com sucesso!");
        exit();
    }
?>