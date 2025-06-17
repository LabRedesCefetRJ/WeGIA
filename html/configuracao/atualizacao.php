<?php
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location ../../index.php");
    }

    // Verifica Permissão do Usuário
	require_once '../permissao/permissao.php';
    permissao($_SESSION['id_pessoa'], 9);

    require_once "./config_funcoes.php";

    /*Buscando arquivo de configuração.. */
    $config_path = "config.php";
    if(file_exists($config_path)){
        require_once($config_path);
    } else {
        while(true){
            $config_path = "../" . $config_path;
            if(file_exists($config_path)) break;
        }
        require_once($config_path);
    }

    define("REDIRECT", [
        "./configuracao_geral.php",
        "./debug_info.php"
    ]);

    $redirect = $_GET["redirect"] ?? REDIRECT[1];

    function tempBackup() {
        if (PHP_OS != 'Linux'){
            return false;
        }
        $log[0] = autosaveBD();
        $log[1] = backupSite();
        return !$log[0] && !$log[1];
    }

    function gitPull() {
        $output = array();
        exec("git -C ".ROOT." pull 2>&1", $output);
        return $output;
    }

    
    $output = gitPull();
    if (DEBUG) {
        var_dump("git -C ".ROOT." pull 2>&1", $output);
        die();
    }
    if ($output) {
        if (sizeof($output) == 1){
            if ($output[0] == 'Already up to date.'){
                header("Location: $redirect?msg=success&sccs=O sistema já está atualizado!");
            }else{
                header("Location: $redirect?msg=error&err=Houve um erro ao executar o comando git -C ".ROOT." pull");
            }
        }elseif (sizeof($output) != 0) {
            $log = "Status da atualização: \n";
            foreach ($output as $value){
                $log = $log . $value . "\n";
            }
            $_SESSION['log'] = $log;
            if (tempBackup()){

                // Ultima release disponível
                $last_release = intval(file_get_contents("https://www.wegia.org/software/release"));
                // Release instalada
                $local_release = intval(file_get_contents("../../.release"));

                $outdated = "";

                if ($local_release < $last_release){
                    require "./geral/msg.php";
                    setSessionMsg("O sistema possui atualizações disponíveis", "warn");
                    $outdated = " (desatualizado)";
                }
                setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
                date_default_timezone_set('America/Sao_Paulo');
                $_SESSION['local_release'] = strftime('%A, %d de %B de %Y, às %H:%M', intval(file_get_contents("../.release"))) . $outdated;

                


                // header("Location: ./configuracao_geral.php?msg=success&sccs=Backup realizado e Atualização concluída!&log=".base64_encode($log));
                header("Location: $redirect?tipo=success&mensagem=Backup realizado e Atualização concluída!");
                
            }else{
                // header("Location: ./configuracao_geral.php?msg=warning&warn=Atualização concluída, mas houve um erro ao realizar o backup (Sistema compatível: Linux, Seu Sistema: ".PHP_OS.")!&log=".base64_encode($log));
                header("Location: $redirect?tipo=warning&mensagem=Atualização concluída, mas houve um erro ao realizar os backups!");
            }
        }
    } else {
        // header("Location: ./configuracao_geral.php?msg=error&err=Houve um erro ao executar o comando git -C ".ROOT." pull");
        header("Location: $redirect?tipo=error&mensagem=Houve um erro ao executar o comando git -C ".ROOT." pull");
    }

?>