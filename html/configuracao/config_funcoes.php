<?php


define("DEBUG", false);
require "../../config.php";

function backupBD(){
    // Executando Backup do Banco de Dados
    
    // Define nome do arquivo (sem o path)
    define("DUMP_NAME", date("YmdHis"));

    // Define o comando para exportar o banco de dados para a pasta de backup com o nome definido acima
    $dbDump = "cd ".BKP_DIR." && mysqldump -u ".DB_USER."  ".DB_NAME." -p".DB_PASSWORD." --no-create-db --no-create-info --skip-triggers > ".BKP_DIR.DUMP_NAME.".bd.sql";

    // Compacta o dump gerado em um .dump.tar.gz
    $dbComp = "tar -czf ".DUMP_NAME.".dump.tar.gz ".DUMP_NAME.".bd.sql";

    // Remove o arquivo não compactado
    $dbRemv = "rm ".BKP_DIR.DUMP_NAME.".bd.sql";

    // Faz os 3 comandos acima serem executados na mesma linha
    $cmdStream = $dbDump . " && " . $dbComp . " && " . $dbRemv;

    // var_dump(
    //     DUMP_NAME, 
    //     $dbDump,
    //     $dbComp,
    //     $dbRemv,
    //     $cmdStream
    // );
    // die();
    
    // Executa os comandos
    return shell_exec($cmdStream);
}

function rmBackupBD($file){
    $rmDump = ("cd ".(BKP_DIR)." && rm ". escapeshellarg($file));
    if (DEBUG){
        var_dump($rmDump);
        die();
    }
    return shell_exec($rmDump);
}

function autosaveBD(){
    // Executando Backup do Banco de Dados
    
    // Define nome do arquivo (sem o path)
    define("AUTOSAVE_DUMP_NAME", date("YmdHis")."-autosave");
    define("AUTOSAVE_ERROR_FATAL", true);

    // Define o comando para exportar o banco de dados para a pasta de backup com o nome definido acima
    $dbDump = "cd ".BKP_DIR." && mysqldump -u ".DB_USER."  ".DB_NAME." -p".DB_PASSWORD." --no-create-db --no-create-info --skip-triggers > ".BKP_DIR.AUTOSAVE_DUMP_NAME.".bd.sql";

    // Compacta o dump gerado em um .dump.tar.gz
    $dbComp = "tar -czf ".AUTOSAVE_DUMP_NAME.".dump.tar.gz ".AUTOSAVE_DUMP_NAME.".bd.sql";

    // Remove o arquivo não compactado
    $dbRemv = "rm ".BKP_DIR.AUTOSAVE_DUMP_NAME.".bd.sql";

    // Faz os 3 comandos acima serem executados na mesma linha
    $cmdStream = $dbDump . " && " . $dbComp . " && " . $dbRemv;

    // var_dump(
    //     AUTOSAVE_DUMP_NAME, 
    //     $dbDump,
    //     $dbComp,
    //     $dbRemv,
    //     $cmdStream
    // );
    // die();
    
    // Executa os comandos
    return shell_exec($cmdStream);
}

function backupSite(){
    // Executando Backup do Diretório do site
    
    return shell_exec("tar -czf ".BKP_DIR.date("YmdHis").".site.tar.gz ".ROOT);
}

function loadBackupDB($file){
    $importStruct = "cd ".ROOT."/BD && mysql  -u ".DB_USER."  ".DB_NAME." -p".DB_PASSWORD." < wegia001.sql";
    $extract = "cd ".BKP_DIR." && tar -xf ".$file;
    $import = "mysql  -u ".DB_USER."  ".DB_NAME." -p".DB_PASSWORD." < ".explode('.', $file)[0].".bd.sql";
    $rmDump = "rm ".explode('.', $file)[0].".bd.sql";
    if (DEBUG){
        var_dump($extract, $import, $rmDump, $extract . " && " . $import . " && " . $rmDump);
        die();
    }
    return shell_exec(escapeshellarg($importStruct) . " && " . escapeshellarg($extract) . " && " . escapeshellarg($import) . " && " . escapeshellarg($rmDump));
}