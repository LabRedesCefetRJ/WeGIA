<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (file_exists("../config.php")) {
    header("Location: ../html/home.php");
    exit();
}

function exibirVoltar(){
    echo '<a href="JavaScript: window.history.back();">Voltar</a><br><a href="../">Inicio</a>';
}

function verificarConexao($erro)
{
    if ($erro == 0) return;

    $msg = "Erro $erro: ";

    switch ($erro) {
        case 1045:
            $msg .= "Usuário/senha incorretos.";
            break;
        case 2002:
            $msg .= "Servidor MySQL não encontrado.";
            break;
    }

    echo "<p style='color:red;'>$msg</p>";
    exibirVoltar();
    die();
}

function validSqlFiles($files)
{
    $out = [];

    foreach ($files as $f) {
        if (pathinfo($f, PATHINFO_EXTENSION) !== "sql") continue;
        if (stripos($f, "test") !== false) continue;
        $out[] = $f;
    }

    return $out;
}

/* =========================
   INPUTS
========================= */

$nomeDB  = $_POST["nomebd"] ?? null;
$local   = $_POST["local"] ?? null;
$user    = $_POST["usuario"] ?? null;
$senha   = $_POST["senha"] ?? null;
$backup  = $_POST["backup"] ?? "";
$www     = $_POST["www"] ?? "";
$reinstalar = isset($_POST["reinstalar"]);

if (!$nomeDB || !$local || !$user) {
    die("Dados obrigatórios ausentes");
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $nomeDB)) {
    die("Nome de banco de dados inválido.");
}

$nomeDB = str_replace(' ', '_', $nomeDB);

/* =========================
   CONEXÃO MYSQL
========================= */

$conn = new mysqli($local, $user, $senha);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

verificarConexao($conn->connect_errno);

/* =========================
   CREATE DATABASE
========================= */

$createDbSql = "CREATE DATABASE IF NOT EXISTS `$nomeDB`";

if (!$conn->query($createDbSql)) {
    die("Erro ao criar banco: " . $conn->error);
}

$conn->select_db($nomeDB);

/* =========================
   SQL FILES
========================= */

$sqlFiles = validSqlFiles(scandir("../BD/"));
$baseDir = realpath("../BD");

$backupDir = $backup ?: null;

if ($backupDir && !is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

/* =============
   IMPORT SQL
================ */

foreach ($sqlFiles as $sqlFile) {

    $filePath = realpath($baseDir . "/" . $sqlFile);

    if (!$filePath || strpos($filePath, $baseDir) !== 0) {
        die("Arquivo inválido: $sqlFile");
    }

    $sqlContent = file_get_contents($filePath);

    $hasDelimiter = stripos($sqlContent, "DELIMITER") !== false;

    if ($hasDelimiter) {

        $cmd = sprintf(
            "mysql --default-character-set=utf8 -u %s -p%s %s < %s 2>&1",
            escapeshellarg($user),
            escapeshellarg($senha),
            escapeshellarg($nomeDB),
            escapeshellarg($filePath)
        );

        $log = shell_exec($cmd);

        if ($log !== null && stripos($log, "error") !== false) {
            die("<p style='color:red;'>Erro no SQL CLI ($sqlFile)<pre>$log</pre></p>");
        }

        echo "<p style='color:green;'>$sqlFile importado via CLI</p>";

    } else {

        if (!$conn->multi_query($sqlContent)) {
            die("Erro ao importar $sqlFile: " . $conn->error);
        }

        while ($conn->more_results() && $conn->next_result()) {}
        echo "<p style='color:green;'>$sqlFile importado via PHP</p>";
    }
}

/* =========================
   CONFIG FILE
========================= */

$configPath = realpath("../") . "/config.php";

$file = fopen($configPath, "w");

if (!$file) {
    die("Falha ao criar config.php");
}

$config = "<?php
define('DB_NAME', " . var_export($nomeDB, true) . ");
define('DB_USER', " . var_export($user, true) . ");
define('DB_PASSWORD', " . var_export($senha, true) . ");
define('DB_HOST', " . var_export($local, true) . ");
define('DB_CHARSET', 'utf8');

define('ROOT', dirname(__FILE__));
define('BKP_DIR', " . var_export($backupDir, true) . ");

define('APP_TIMEZONE', 'America/Sao_Paulo');
define('WWW', " . var_export($www, true) . ");
";

fwrite($file, $config);
fclose($file);

echo "<p style='color:green;'>config.php criado com sucesso!</p>";

/* =========================
   FINALIZAÇÃO
========================= */

$conn->close();

echo "<p style='color:green;'>Instalação concluída com sucesso!</p>";

exibirVoltar();
?>