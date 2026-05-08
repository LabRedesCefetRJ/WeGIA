<?php

require_once('../model/Cobranca.php');
require_once('../dao/CobrancaDAO.php');

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo == 'POST') {
    cadastrarCobranca();
}

/**Extrai os dados do formulário, instancia os objetos necessários e chama o método de inserirCobranca() do CobrancaDAO */
function cadastrarCobranca()
{

    $socio_id = trim(filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT));
    $local_recepcao = trim(filter_input(INPUT_POST, 'local_recepcao', FILTER_SANITIZE_STRING));
    $valor_cobranca = trim(filter_input(INPUT_POST, 'valor_cobranca', FILTER_SANITIZE_NUMBER_INT));
    $forma_doacao = trim(filter_input(INPUT_POST, 'forma_doacao', FILTER_SANITIZE_STRING));
    $data_doacao = trim(filter_input(INPUT_POST, 'data_doacao', FILTER_SANITIZE_STRING));
    $receptor = trim(filter_input(INPUT_POST, 'receptor', FILTER_SANITIZE_STRING));

    if (!$socio_id || $socio_id < 1) {
        http_response_code(400);
        echo json_encode(['erro' => 'O id de um sócio deve ser um inteiro positivo maior ou igual a 1.']);
        exit();
    }

    if (!$local_recepcao || strlen($local_recepcao) < 1) {
        http_response_code(400);
        echo json_encode(['erro' => 'Informe um local de recpção válido']);
        exit();
    }

    if (!$valor_cobranca || $valor_cobranca < 0) {
        http_response_code(400);
        echo json_encode(['erro' => 'O valor de uma cobrança não pode ser negativo']);
        exit();
    }

    if (!$forma_doacao || strlen($forma_doacao) < 1) {
        http_response_code(400);
        echo json_encode(['erro' => 'Informe uma forma de doação válida']);
        exit();
    }

    $dataArray = explode('-', $data_doacao);

    if (!$data_doacao || !checkdate(intval($dataArray[1]), intval(($dataArray[2])), intval($dataArray[0]))) {
        http_response_code(400);
        echo json_encode(['erro' => 'A data de doação informada não é válida']);
        exit();
    }

    if (!$receptor || strlen($receptor) < 1) {
        http_response_code(400);
        echo json_encode(['erro' => 'O receptor informado não possuí um documento válido']);
        exit();
    }

    $cobranca = new Cobranca();
    $cobranca->setIdSocio($socio_id);
    $cobranca->setLocalRecepcao($local_recepcao);
    $cobranca->setValorPagamento($valor_cobranca);
    $cobranca->setFormaPagamento($forma_doacao);
    $cobranca->setDataPagamento($data_doacao);
    $cobranca->setReceptor($receptor);

    $cobrancaDAO = new CobrancaDAO();
    try {
        $cobrancaDAO->inserirCobranca($cobranca);
        header('Location: ../cobrancas.php');
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro no servidor ao registrar cobrança']);
        exit();
    }
}
