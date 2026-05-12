<?php
require_once dirname(__DIR__, 3) . '/config.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'FusoHorarioSistema.php';

FusoHorarioSistema::definir();

$inputJson = json_decode(file_get_contents('php://input'), true);

if (json_last_error() === JSON_ERROR_NONE && isset($inputJson['nomeClasse'], $inputJson['metodo'])) {
    $controller = trim($inputJson['nomeClasse']);
    $function = trim($inputJson['metodo']);
} else {
    $controller = trim($_REQUEST['nomeClasse'] ?? '');
    $function = trim($_REQUEST['metodo'] ?? '');
}

try {
    if (!$controller || !$function) {
        throw new InvalidArgumentException('Operação inválida, controladora e função não definidas');
    }

    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $controller)) {
        throw new InvalidArgumentException('Controladora inválida');
    }

    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $function)) {
        throw new InvalidArgumentException('Método inválido');
    }

    $rotasPublicas = [
        'SocioController' => [
            'criarSocio'
        ],
        'RegraPagamentoController' => [
            'buscaConjuntoRegrasPagamentoPorNomeMeioPagamento'
        ]
    ];

    $rotasPrivadas = [
        'SocioController' => [
            'buscarPorDocumento',
            'atualizarSocio',
            'exibirBoletosPorCpf',
            'sincronizarStatusSocios'
        ],
        'ReciboController' => [
            'gerarRecibo',
            'download'
        ],
        'RecorrenciaController' => [
            'criarAssinatura'
        ],
        'ContribuicaoLogController' => [
            'criarBoleto',
            'criarCarne',
            'criarQRCode',
            'processarCartaoCredito',
            'pagarPorId',
            'sincronizarStatus',
            'getContribuicoesLogJSON',
            'getRelatorio',
            'registrarFaturas'
        ],
        'GatewayPagamentoController' => [
            'cadastrar',
            'buscaTodos',
            'excluirPorId',
            'editarPorId',
            'alterarStatus'
        ],
        'MeioPagamentoController' => [
            'cadastrar',
            'buscaTodos',
            'excluirPorId',
            'editarPorId',
            'alterarStatus'
        ],
        'RegraPagamentoController' => [
            'buscaRegrasContribuicao',
            'buscaConjuntoRegrasPagamento',
            'cadastrar',
            'excluirPorId',
            'editarPorId',
            'alterarStatus'
        ]
    ];

    $isPublica = isset($rotasPublicas[$controller]) &&
        in_array($function, $rotasPublicas[$controller], true);

    $isPrivada = isset($rotasPrivadas[$controller]) &&
        in_array($function, $rotasPrivadas[$controller], true);

    if (!$isPublica && !$isPrivada) {
        throw new InvalidArgumentException('Rota não permitida');
    }

    if ($isPrivada) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_pessoa'])) {
            throw new Exception('Violação de acesso');
        }

        require_once '../../permissao/permissao.php';
        permissao($_SESSION['id_pessoa'], 9, 3);
    }

    $baseDir = realpath(__DIR__);
    $controllerPath = realpath($baseDir . DIRECTORY_SEPARATOR . $controller . '.php');

    if ($controllerPath === false || strpos($controllerPath, $baseDir . DIRECTORY_SEPARATOR) !== 0) {
        throw new InvalidArgumentException('Controladora inválida');
    }

    require_once $controllerPath;

    if (!class_exists($controller)) {
        throw new InvalidArgumentException('Controladora inexistente');
    }

    $controllerObject = new $controller();

    if (!method_exists($controllerObject, $function)) {
        throw new InvalidArgumentException('Método inexistente');
    }

    $reflection = new ReflectionMethod($controllerObject, $function);

    if (!$reflection->isPublic()) {
        throw new InvalidArgumentException('Método não acessível');
    }

    $controllerObject->$function();

} catch (Throwable $e) {
    http_response_code(400);
    exit('Erro: ' . $e->getMessage());
}