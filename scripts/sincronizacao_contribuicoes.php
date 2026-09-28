<?php
/**
 * Sincroniza faturas e status de contribuições para execução via CLI.
 *
 * Crontab:
 * CRON_TZ=America/Sao_Paulo
 * 0 2 * * * /usr/bin/php /home/gabriel/public_html/WeGIA/scripts/sincronizacao_contribuicoes.php >> /var/log/wegia/contribuicoes-cron.log 2>&1
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script deve ser executado via PHP CLI.\n");
    exit(1);
}

$webDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'web';
$controllerDirectory = $webDirectory . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'contribuicao' . DIRECTORY_SEPARATOR . 'controller';

require_once $webDirectory . DIRECTORY_SEPARATOR . 'config.php';
require_once $webDirectory . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'FusoHorarioSistema.php';

$timezone = FusoHorarioSistema::definir();

$log = static function (string $message) use ($timezone): void {
    $timestamp = (new DateTimeImmutable('now', new DateTimeZone($timezone)))->format('Y-m-d H:i:sP');
    fwrite(STDERR, sprintf('[%s] %s%s', $timestamp, $message, PHP_EOL));
};

$technicalPersonId = defined('CONTRIBUICOES_CRON_ID_PESSOA')
    ? filter_var(CONTRIBUICOES_CRON_ID_PESSOA, FILTER_VALIDATE_INT)
    : false;

if ($technicalPersonId === false || $technicalPersonId < 1) {
    $log('Configuração inválida: CONTRIBUICOES_CRON_ID_PESSOA deve ser um id_pessoa existente e positivo.');
    exit(1);
}

if (!is_dir($controllerDirectory) || !chdir($controllerDirectory)) {
    $log('Não foi possível preparar o diretório do controlador.');
    exit(1);
}

if (session_status() === PHP_SESSION_NONE && !session_start()) {
    $log('Não foi possível iniciar a sessão técnica.');
    exit(1);
}

$_SESSION['id_pessoa'] = $technicalPersonId;

require_once $controllerDirectory . DIRECTORY_SEPARATOR . 'ContribuicaoLogController.php';

$startedAt = microtime(true);
$log('Iniciando sincronização de contribuições.');

try {
    $controller = new ContribuicaoLogController();
} catch (Throwable $exception) {
    $log('Falha ao inicializar o controlador: ' . $exception->getMessage());
    exit(1);
}

$failed = false;

foreach (['registrarFaturas', 'sincronizarStatus'] as $operation) {
    $operationStartedAt = microtime(true);
    $log('Iniciando etapa: ' . $operation . '.');

    try {
        $controller->{$operation}();
        $duration = microtime(true) - $operationStartedAt;
        $log(sprintf('Etapa concluída: %s (%.3f s).', $operation, $duration));
    } catch (Throwable $exception) {
        $failed = true;
        $duration = microtime(true) - $operationStartedAt;
        $log(sprintf('Etapa falhou: %s (%.3f s): %s', $operation, $duration, $exception->getMessage()));
    }
}

$duration = microtime(true) - $startedAt;
$log(sprintf('Sincronização finalizada em %.3f s%s.', $duration, $failed ? ' com falhas' : ' com sucesso'));

exit($failed ? 1 : 0);
