<?php
define("DEBUG", false);
require "../../config.php";

function getBackupSigningKeyFilePath(): string
{
    if (defined('BACKUP_SIGNING_KEY_FILE') && is_string(BACKUP_SIGNING_KEY_FILE) && trim(BACKUP_SIGNING_KEY_FILE) !== '') {
        return BACKUP_SIGNING_KEY_FILE;
    }

    return rtrim(BKP_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.backup_signing_private.pem';
}

function getBackupSigningPrivateKey()
{
    if (!extension_loaded('openssl')) {
        throw new RuntimeException('Extensão OpenSSL não disponível no PHP.');
    }

    $backupDir = realpath(BKP_DIR);
    if ($backupDir === false || !is_dir($backupDir)) {
        throw new RuntimeException('Diretório de backup inválido.');
    }

    $keyFile = getBackupSigningKeyFilePath();

    if (!is_file($keyFile)) {
        throw new RuntimeException('Chave privada de assinatura não encontrada. Gere a chave manualmente e coloque em: ' . $keyFile);
    }

    $privateKeyPem = file_get_contents($keyFile);
    if ($privateKeyPem === false || trim($privateKeyPem) === '') {
        throw new RuntimeException('Arquivo de chave privada inválido.');
    }

    $privateKey = openssl_pkey_get_private($privateKeyPem);
    if ($privateKey === false) {
        throw new RuntimeException('Falha ao carregar chave privada de assinatura.');
    }

    return $privateKey;
}

//remover
function signSqlContent(string $sqlContent): string
{
    $privateKey = getBackupSigningPrivateKey();
    $signature = '';

    if (!openssl_sign($sqlContent, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
        throw new RuntimeException('Falha ao assinar conteúdo do backup.');
    }

    return base64_encode($signature);
}

function signSqlFile(string $filePath): string
{
    $privateKey = getBackupSigningPrivateKey();

    $context = hash_init('sha256');

    $handle = fopen($filePath, 'rb');
    if (!$handle) {
        throw new RuntimeException('Falha ao abrir SQL para assinatura.');
    }

    while (!feof($handle)) {
        $chunk = fread($handle, 1024 * 1024); // 1MB
        if ($chunk === false) {
            fclose($handle);
            throw new RuntimeException('Erro ao ler SQL.');
        }
        hash_update($context, $chunk);
    }

    fclose($handle);

    // hash final (binário!)
    $hash = hash_final($context, true);

    $signature = '';
    if (!openssl_sign($hash, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
        throw new RuntimeException('Falha ao assinar hash do backup.');
    }

    return base64_encode($signature);
}

function verifySqlSignatureFromFile(string $filePath, string $signatureBase64): void
{
    $signature = base64_decode(trim($signatureBase64), true);
    if ($signature === false || $signature === '') {
        throw new RuntimeException('Assinatura do backup inválida.');
    }

    $privateKey = getBackupSigningPrivateKey();
    $details = openssl_pkey_get_details($privateKey);

    if (!is_array($details) || empty($details['key'])) {
        throw new RuntimeException('Falha ao obter chave pública.');
    }

    $publicKey = openssl_pkey_get_public($details['key']);
    if ($publicKey === false) {
        throw new RuntimeException('Falha ao carregar chave pública.');
    }

    // 🔥 HASH STREAMING (igual ao backup)
    $context = hash_init('sha256');

    $handle = fopen($filePath, 'rb');
    if (!$handle) {
        throw new RuntimeException('Falha ao abrir SQL para verificação.');
    }

    while (!feof($handle)) {
        $chunk = fread($handle, 1024 * 1024);
        if ($chunk === false) {
            fclose($handle);
            throw new RuntimeException('Erro ao ler SQL.');
        }
        hash_update($context, $chunk);
    }

    fclose($handle);

    $hash = hash_final($context, true);

    // 🔐 Verifica assinatura do HASH
    $result = openssl_verify($hash, $signature, $publicKey, OPENSSL_ALGO_SHA256);

    if ($result !== 1) {
        throw new RuntimeException('Assinatura do backup não confere.');
    }
}

//remover
function verifySqlSignature(string $sqlContent, string $signatureBase64): void
{
    $signature = base64_decode(trim($signatureBase64), true);
    if ($signature === false || $signature === '') {
        throw new RuntimeException('Assinatura do backup inválida.');
    }

    $privateKey = getBackupSigningPrivateKey();
    $details = openssl_pkey_get_details($privateKey);

    if (!is_array($details) || empty($details['key'])) {
        throw new RuntimeException('Falha ao obter chave pública para validação.');
    }

    $publicKey = openssl_pkey_get_public($details['key']);
    if ($publicKey === false) {
        throw new RuntimeException('Falha ao carregar chave pública para validação.');
    }

    $result = openssl_verify($sqlContent, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    if ($result !== 1) {
        throw new RuntimeException('Assinatura do backup não confere.');
    }
}

function validateRestoreSqlFile(string $filePath): void
{
    $handle = fopen($filePath, 'rb');
    if (!$handle) {
        throw new RuntimeException('Falha ao abrir SQL para validação.');
    }

    $forbiddenPatterns = [
        '/\\bCREATE\\s+USER\\b/i',
        '/\\bALTER\\s+USER\\b/i',
        '/\\bDROP\\s+USER\\b/i',
        '/\\bGRANT\\b/i',
        '/\\bREVOKE\\b/i',
        '/\\bSET\\s+PASSWORD\\b/i',
        '/\\bCREATE\\s+DATABASE\\b/i',
        '/\\bDROP\\s+DATABASE\\b/i',
    ];

    while (!feof($handle)) {
        $chunk = fread($handle, 1024 * 1024);
        if ($chunk === false) {
            fclose($handle);
            throw new RuntimeException('Erro ao ler SQL.');
        }

        foreach ($forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $chunk)) {
                fclose($handle);
                throw new RuntimeException('SQL contém instruções não permitidas.');
            }
        }
    }

    fclose($handle);
}

//remover
function validateRestoreSqlContent(string $sqlContent): void
{
    if (trim($sqlContent) === '') {
        throw new RuntimeException('Arquivo SQL vazio.');
    }

    // Bloqueia instruções administrativas que não fazem parte do dump esperado.
    $forbiddenPatterns = [
        '/\\bCREATE\\s+USER\\b/i',
        '/\\bALTER\\s+USER\\b/i',
        '/\\bDROP\\s+USER\\b/i',
        '/\\bGRANT\\b/i',
        '/\\bREVOKE\\b/i',
        '/\\bSET\\s+PASSWORD\\b/i',
        '/\\bCREATE\\s+DATABASE\\b/i',
        '/\\bDROP\\s+DATABASE\\b/i',
    ];

    foreach ($forbiddenPatterns as $pattern) {
        if (preg_match($pattern, $sqlContent) === 1) {
            throw new RuntimeException('SQL de backup contém instruções não permitidas.');
        }
    }
}

function backupBD(): string
{
    $timestamp = date('YmdHis');
    $baseName  = $timestamp . '.dump';

    $tmpDir = sys_get_temp_dir() . '/db_backup_' . bin2hex(random_bytes(8));
    if (!mkdir($tmpDir, 0700, true)) {
        throw new RuntimeException('Falha ao criar diretório temporário.');
    }

    $sqlFile = $tmpDir . '/' . $baseName . '.sql';
    $sigFile = $tmpDir . '/' . $baseName . '.sig';
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

        $signature = signSqlFile($sqlFile);

        if (file_put_contents($sigFile, $signature, LOCK_EX) === false) {
            throw new RuntimeException('Falha ao salvar assinatura do backup.');
        }

        //Cria TAR
        $tar = new PharData($tarFile);
        $tar->addFile($sqlFile, basename($sqlFile));
        $tar->addFile($sigFile, basename($sigFile));

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

function autosaveBD() //está com erro, providenciar um conserto.
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
    if (!preg_match('/^[a-zA-Z0-9_-]+\.dump\.tar\.gz$/', $file)) {
        throw new RuntimeException('Nome de arquivo inválido.');
    }

    $backupDir  = realpath(BKP_DIR);
    $backupPath = realpath(BKP_DIR . DIRECTORY_SEPARATOR . $file);

    if ($backupPath === false || !str_starts_with($backupPath, $backupDir)) {
        throw new RuntimeException('Arquivo fora do diretório permitido.');
    }

    $tmpDir = sys_get_temp_dir() . '/db_restore_' . bin2hex(random_bytes(8));
    if (!mkdir($tmpDir, 0700, true)) {
        throw new RuntimeException('Falha ao criar diretório temporário.');
    }

    $tmpDirReal = realpath($tmpDir);
    if ($tmpDirReal === false) {
        throw new RuntimeException('Falha ao resolver diretório temporário.');
    }

    try {
        // Copia backup
        $tmpGz = $tmpDir . '/backup.tar.gz';
        if (!copy($backupPath, $tmpGz)) {
            throw new RuntimeException('Falha ao copiar backup.');
        }

        // Extrai
        $cmd = sprintf(
            'tar -xzf %s -C %s --no-same-owner --no-same-permissions',
            escapeshellarg($tmpGz),
            escapeshellarg($tmpDir)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException('Falha ao extrair o backup.');
        }

        // Validação de paths e symlinks
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmpDirReal, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $entry) {
            $entryPath = $entry->getPathname();

            if (is_link($entryPath)) {
                throw new RuntimeException('Backup inválido: contém link simbólico.');
            }

            $resolvedPath = realpath($entryPath);
            if ($resolvedPath === false) {
                throw new RuntimeException('Backup inválido: contém caminho não resolvível.');
            }

            if (!str_starts_with($resolvedPath, $tmpDirReal . DIRECTORY_SEPARATOR)) {
                throw new RuntimeException('Backup inválido: contém path fora do diretório.');
            }
        }

        // Localiza arquivos
        $sqlFiles = glob($tmpDir . '/*.sql');
        if (!$sqlFiles || count($sqlFiles) !== 1) {
            throw new RuntimeException('Backup deve conter exatamente um .sql.');
        }

        $sigFiles = glob($tmpDir . '/*.sig');
        if (!$sigFiles || count($sigFiles) !== 1) {
            throw new RuntimeException('Backup deve conter exatamente um .sig.');
        }

        $sqlFileReal = realpath($sqlFiles[0]);
        $sigFileReal = realpath($sigFiles[0]);

        if (
            $sqlFileReal === false ||
            $sigFileReal === false ||
            !str_starts_with($sqlFileReal, $tmpDirReal . DIRECTORY_SEPARATOR) ||
            !str_starts_with($sigFileReal, $tmpDirReal . DIRECTORY_SEPARATOR)
        ) {
            throw new RuntimeException('Arquivos do backup inválidos.');
        }

        if (is_link($sqlFileReal) || is_link($sigFileReal)) {
            throw new RuntimeException('Backup contém links simbólicos inválidos.');
        }

        // Lê apenas a assinatura (pequena)
        $signatureBase64 = file_get_contents($sigFileReal);
        if ($signatureBase64 === false) {
            throw new RuntimeException('Falha ao ler assinatura.');
        }

        // Verificação por streaming
        verifySqlSignatureFromFile($sqlFileReal, $signatureBase64);

        // Validação por streaming
        validateRestoreSqlFile($sqlFileReal);

        // Importação via stream
        $process = proc_open(
            ['mysql', '-u', DB_USER, DB_NAME],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null,
            ['MYSQL_PWD' => DB_PASSWORD]
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Falha ao iniciar mysql.');
        }

        $handle = fopen($sqlFileReal, 'rb');
        if (!$handle) {
            throw new RuntimeException('Falha ao abrir SQL.');
        }

        while (!feof($handle)) {
            $chunk = fread($handle, 1024 * 1024); // 1MB
            if ($chunk === false) {
                fclose($handle);
                throw new RuntimeException('Erro ao ler SQL.');
            }

            fwrite($pipes[0], $chunk);
        }

        fclose($handle);
        fclose($pipes[0]);

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException('Erro ao importar banco: ' . $error);
        }
    } finally {
        if (is_dir($tmpDir)) {
            $cleanupIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tmpDir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($cleanupIterator as $entry) {
                $entryPath = $entry->getPathname();
                if ($entry->isDir() && !$entry->isLink()) {
                    @rmdir($entryPath);
                } else {
                    @unlink($entryPath);
                }
            }
        }

        @rmdir($tmpDir);
    }

    return true;
}
