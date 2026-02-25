<?php


define("DEBUG", false);
require "../../config.php";

function backupBD(): string
{
    $timestamp = date('YmdHis');
    $baseName  = $timestamp . '.dump';

    $tmpDir = sys_get_temp_dir() . '/db_backup_' . bin2hex(random_bytes(8));
    if (!mkdir($tmpDir, 0700, true)) {
        throw new RuntimeException('Falha ao criar diretório temporário.');
    }

    $sqlFile = $tmpDir . '/' . $baseName . '.sql';
    $tarFile = $tmpDir . '/' . $baseName . '.tar';
    $gzFile  = BKP_DIR . '/' . $baseName . '.tar.gz';

    try {
        //Gera o dump
        $process = proc_open(
            [
                'mysqldump',
                '-u',
                DB_USER,
                '--single-transaction',
                '--quick',
                DB_NAME
            ],
            [
                0 => ['pipe', 'r'],
                1 => ['file', $sqlFile, 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null,
            ['MYSQL_PWD' => DB_PASSWORD]
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Falha ao iniciar mysqldump.');
        }

        fclose($pipes[0]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            throw new RuntimeException('Erro no mysqldump: ' . $stderr);
        }

        //Valida se o SQL NÃO está vazio
        if (!file_exists($sqlFile) || filesize($sqlFile) === 0) {
            throw new RuntimeException('Dump SQL vazio.');
        }

        //Cria TAR
        $tar = new PharData($tarFile);
        $tar->addFile($sqlFile, basename($sqlFile));

        //Compacta para TAR.GZ
        $tar->compress(Phar::GZ);

        if (!file_exists($tarFile . '.gz')) {
            throw new RuntimeException('Falha ao gerar TAR.GZ.');
        }

        //Move para BKP_DIR
        if (!rename($tarFile . '.gz', $gzFile)) {
            throw new RuntimeException('Falha ao mover backup final.');
        }

        return basename($gzFile);
    } finally {
        //Limpeza garantida
        foreach (glob($tmpDir . '/*') as $file) {
            unlink($file);
        }
        rmdir($tmpDir);
    }
}

function rmBackupBD($file)
{
    $rmDump = ("cd " . (BKP_DIR) . " && rm " . escapeshellarg($file));
    if (DEBUG) {
        var_dump($rmDump);
        die();
    }
    return shell_exec($rmDump);
}

function autosaveBD()//está com erro, providenciar um conserto.
{
    // Executando Backup do Banco de Dados

    // Define nome do arquivo (sem o path)
    define("AUTOSAVE_DUMP_NAME", date("YmdHis") . "-autosave");
    define("AUTOSAVE_ERROR_FATAL", true);

    // Define o comando para exportar o banco de dados para a pasta de backup com o nome definido acima
    $dbDump = "cd " . BKP_DIR . " && mysqldump -u " . DB_USER . "  " . DB_NAME . " -p" . DB_PASSWORD . " --no-create-db --no-create-info --skip-triggers > " . BKP_DIR . AUTOSAVE_DUMP_NAME . ".bd.sql";

    // Compacta o dump gerado em um .dump.tar.gz
    $dbComp = "tar -czf " . AUTOSAVE_DUMP_NAME . ".dump.tar.gz " . AUTOSAVE_DUMP_NAME . ".bd.sql";

    // Remove o arquivo não compactado
    $dbRemv = "rm " . BKP_DIR . AUTOSAVE_DUMP_NAME . ".bd.sql";

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

function backupSite()
{
    // Executando Backup do Diretório do site

    return shell_exec("tar -czf " . BKP_DIR . date("YmdHis") . ".site.tar.gz " . ROOT);
}

function loadBackupDB(string $file): bool
{
    // 1. Validação extremamente restrita
    if (!preg_match('/^[a-zA-Z0-9_-]+\.dump\.tar\.gz$/', $file)) {
        throw new RuntimeException('Nome de arquivo inválido.');
    }

    // 2. Resolve caminho real
    $backupDir  = realpath(BKP_DIR);
    $backupPath = realpath(BKP_DIR . DIRECTORY_SEPARATOR . $file);

    if ($backupPath === false || !str_starts_with($backupPath, $backupDir)) {
        throw new RuntimeException('Arquivo fora do diretório permitido.');
    }

    // 3. Diretório temporário
    $tmpDir = sys_get_temp_dir() . '/db_restore_' . bin2hex(random_bytes(8));
    if (!mkdir($tmpDir, 0700, true)) {
        throw new RuntimeException('Falha ao criar diretório temporário.');
    }

    try {
        // 4. Copia backup para /tmp
        $tmpGz = $tmpDir . '/backup.tar.gz';
        if (!copy($backupPath, $tmpGz)) {
            throw new RuntimeException('Falha ao copiar backup.');
        }

        // 5. Extrai diretamente (SEM PharData)
        $cmd = sprintf(
            'tar -xzf %s -C %s --no-same-owner --no-same-permissions',
            escapeshellarg($tmpGz),
            escapeshellarg($tmpDir)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException('Falha ao extrair o backup.');
        }

        // 6. Procura o SQL
        $sqlFiles = glob($tmpDir . '/*.sql');
        if (!$sqlFiles || count($sqlFiles) !== 1) {
            throw new RuntimeException('Backup deve conter exatamente um arquivo .sql.');
        }

        $sqlFile = $sqlFiles[0];

        // 7. Importa SQL com proc_open (seguro)
        $process = proc_open(
            ['mysql', '-u', DB_USER, DB_NAME],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null,
            [
                'MYSQL_PWD' => DB_PASSWORD
            ]
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Falha ao iniciar o mysql.');
        }

        fwrite($pipes[0], file_get_contents($sqlFile));
        fclose($pipes[0]);

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException('Erro ao importar banco: ' . $error);
        }
    } finally {
        // 8. Limpeza do /tmp
        foreach (glob($tmpDir . '/*') as $f) {
            @unlink($f);
        }
        @rmdir($tmpDir);
    }

    return true;
}
