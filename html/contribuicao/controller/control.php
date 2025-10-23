<?php
// Tenta decodificar o JSON do corpo da requisição
$inputJson = json_decode(file_get_contents('php://input'), true);

// Se o JSON for válido, use os valores de lá; caso contrário, use $_REQUEST
if (json_last_error() === JSON_ERROR_NONE && isset($inputJson['nomeClasse']) && isset($inputJson['metodo'])) {
    $controller = trim(htmlspecialchars($inputJson['nomeClasse']));
    $function = trim(htmlspecialchars($inputJson['metodo']));
} else {
    $controller = trim(htmlspecialchars($_REQUEST['nomeClasse']));
    $function = trim(htmlspecialchars($_REQUEST['metodo']));
}

try {
    if (!$controller || !$function) {
        throw new InvalidArgumentException('Operação Inválida, controladora e função não definidas');
    }

    $whiteList = 
    [
        'exibirBoletosPorCpf',
        'buscarPorDocumento',
        'criarSocio',
        'atualizarSocio',
        'buscaConjuntoRegrasPagamentoPorNomeMeioPagamento',
        'gerarRecibo',
        'download',
        'criarAssinatura',
    ];

    //Blocks execution for unauthorized access
    if(($controller !='ContribuicaoLogController' && !in_array($function, $whiteList)) || $function == 'pagarPorId'){
        session_start();
        if(!isset($_SESSION['id_pessoa'])){
            throw new Exception('Violação de acesso: '.$function);
        }

        require_once '../../permissao/permissao.php';
        permissao($_SESSION['id_pessoa'], 9, 3);
    }

    $controllerPath = $controller . '.php'; //Prepare the require path

    if (!file_exists($controllerPath)) {
        throw new InvalidArgumentException('Operação Inválida, arquivo da controladora inexistente');
    }

    require_once($controllerPath);

    if (!class_exists($controller)) {
        throw new InvalidArgumentException('Operação Inválida, controladora inexistente');
    }

    $controllerObject = new $controller(); //Instance the controller type object

    if (!method_exists($controllerObject, $function)) {
        throw new InvalidArgumentException('Operação Inválida, método inexistente');
    }

    $controllerObject->$function(); //Calls the controller function
} catch (Exception $e) {
    http_response_code(400);
    exit("Erro: {$e->getMessage()}");
}