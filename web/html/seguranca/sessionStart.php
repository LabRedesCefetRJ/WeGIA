<?php
/**
 * Protege o sistema contra entradas excessivamente longas (ataques DoS).
 */

define('LIMITE_PADRAO_PARAMETRO', 200);
define('TEMPO_BLOQUEIO_SEGUNDOS', 300); // 5 minutos
define('CAMINHO_ARQUIVO_BLOQUEIO', __DIR__ . '/ips_bloqueados.json');

/**
 * Verifica os parâmetros de entrada GET/POST.
 * Se houver entradas muito longas, bloqueia temporariamente o IP.
 */
function verificarParametrosEntrada(int $limite = LIMITE_PADRAO_PARAMETRO): void
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $entradas = array_merge($_GET, $_POST);

    // Verifica se o IP já está bloqueado
    $bloqueios = carregarIpsBloqueados();

    if (isset($bloqueios[$ip]) && time() < $bloqueios[$ip]) {
        http_response_code(429);
        die("Acesso temporariamente bloqueado por comportamento suspeito.");
    }

    // Verifica tamanho dos parâmetros
    foreach ($entradas as $chave => $valor) {
        if (is_array($valor)) continue;

        if (is_string($valor) && strlen($valor) > $limite) {
            registrarBloqueioIp($ip);
            http_response_code(414);
            die("Parâmetro '$chave' excedeu o limite permitido.");
        }
    }
}

/**
 * Registra o IP com bloqueio temporário.
 */
function registrarBloqueioIp(string $ip): void
{
    $bloqueios = carregarIpsBloqueados();
    $bloqueios[$ip] = time() + TEMPO_BLOQUEIO_SEGUNDOS;
    file_put_contents(CAMINHO_ARQUIVO_BLOQUEIO, json_encode($bloqueios));
}

/**
 * Carrega lista de IPs bloqueados.
 */
function carregarIpsBloqueados(): array
{
    if (!file_exists(CAMINHO_ARQUIVO_BLOQUEIO)) {
        return [];
    }

    $conteudo = file_get_contents(CAMINHO_ARQUIVO_BLOQUEIO);
    return json_decode($conteudo, true) ?? [];
}

verificarParametrosEntrada();

$config_path = "config.php";
$loopLimit = 2000;
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $loopLimit--;
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
        if ($loopLimit < 0) {
            // Caso config.php não seja encontrado
            header("Location: instalador/index.php");
            break;
        }
    }
    require_once($config_path);
}
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

require_once dirname(__FILE__, 3) . "/dao/Conexao.php";

if (isset($_SESSION['id_pessoa'])) {
    try {
        $pdo = Conexao::connect();
        
        $stmt = $pdo->prepare("SELECT id_situacao FROM funcionario WHERE id_pessoa = :id_pessoa");
        $stmt->bindValue(':id_pessoa', $_SESSION['id_pessoa'], PDO::PARAM_INT);
        $stmt->execute();
        $situacao = $stmt->fetchColumn();

        if ($situacao !== false && (int)$situacao === 2) {
            session_destroy();
            header("Location: " . WWW . "index.php?erro=acesso_revogado");
            exit;
        }
    } catch (Exception $e) {
        error_log("Erro na verificação de segurança da sessão: " . $e->getMessage());
    }
} 