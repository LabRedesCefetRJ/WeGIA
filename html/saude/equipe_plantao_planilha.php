<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

$arquivoServicoPlanilha = dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'SaudeEquipePlantaoPlanilhaService.php';
$arquivoServicoPlantao = dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'SaudeEquipePlantaoService.php';

// Garante que o endpoint use a versão mais recente do código de geração, mesmo com OPcache agressivo.
if (function_exists('opcache_invalidate')) {
    @opcache_invalidate(__FILE__, true);
    @opcache_invalidate($arquivoServicoPlanilha, true);
    @opcache_invalidate($arquivoServicoPlantao, true);
}

require_once $arquivoServicoPlanilha;

permissao($_SESSION['id_pessoa'], 5, 5);

$ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: (int) date('Y');
$mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT) ?: (int) date('m');

try {
    $servicePlanilha = new SaudeEquipePlantaoPlanilhaService();
    $arquivo = $servicePlanilha->gerarPlanilhaMensal($ano, $mes);

    if (!is_file($arquivo['caminho'])) {
        throw new RuntimeException('Arquivo de planilha não foi gerado.');
    }

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $arquivo['content_type']);
    header('Content-Disposition: attachment; filename="' . basename($arquivo['nome_arquivo']) . '"');
    header('Content-Length: ' . filesize($arquivo['caminho']));
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: public');
    readfile($arquivo['caminho']);
    @unlink($arquivo['caminho']);
    exit();
} catch (Throwable $erro) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Erro ao gerar a planilha: ' . $erro->getMessage();
}
