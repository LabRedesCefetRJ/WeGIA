<?php

require("../conexao.php");

if (empty($_POST)) {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
        $_POST = $data;
    }
}

$codigo = filter_var($_POST['codigo'] ?? null, FILTER_VALIDATE_INT);
$id_socio = filter_var($_POST['id_socio'] ?? null, FILTER_VALIDATE_INT);

$valor = filter_var($_POST['valor'] ?? null, FILTER_VALIDATE_FLOAT);
$valor_pago = filter_var($_POST['valor_pago'] ?? 0, FILTER_VALIDATE_FLOAT);

if ($codigo === false) {
    http_response_code(400);
    die(json_encode([
        'success' => false,
        'message' => 'Código inválido.'
    ]));
}

if ($id_socio === false) {
    http_response_code(400);
    die(json_encode([
        'success' => false,
        'message' => 'ID do sócio inválido.'
    ]));
}

if ($valor === false) {
    http_response_code(400);
    die(json_encode([
        'success' => false,
        'message' => 'Valor inválido.'
    ]));
}

if ($valor_pago === false) {
    http_response_code(400);
    die(json_encode([
        'success' => false,
        'message' => 'Valor pago inválido.'
    ]));
}

$descricao = trim($_POST['descricao'] ?? '');
$status = trim($_POST['status'] ?? '');

$link_cobranca = trim($_POST['link_cobranca'] ?? '');
$link_boleto = trim($_POST['link_boleto'] ?? '');
$linha_digitavel = trim($_POST['linha_digitavel'] ?? '');

$data_emissao = !empty($_POST['data_emissao'])
    ? $_POST['data_emissao']
    : null;

$data_vencimento = !empty($_POST['data_vencimento'])
    ? $_POST['data_vencimento']
    : null;

$data_pagamento = !empty($_POST['data_pagamento'])
    ? $_POST['data_pagamento']
    : null;

$sql = "
    INSERT INTO cobrancas
    (
        codigo,
        descricao,
        data_emissao,
        data_vencimento,
        data_pagamento,
        valor,
        valor_pago,
        status,
        link_cobranca,
        link_boleto,
        linha_digitavel,
        id_socio
    )
    VALUES
    (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )
";

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    http_response_code(500);

    die(json_encode([
        'success' => false,
        'message' => 'Erro ao preparar SQL.',
        'error' => $conexao->error
    ]));
}

$stmt->bind_param(
    "issssddssssi",
    $codigo,
    $descricao,
    $data_emissao,
    $data_vencimento,
    $data_pagamento,
    $valor,
    $valor_pago,
    $status,
    $link_cobranca,
    $link_boleto,
    $linha_digitavel,
    $id_socio
);

if (!$stmt->execute()) {

    http_response_code(500);

    die(json_encode([
        'success' => false,
        'message' => 'Erro ao inserir cobrança.',
        'error' => $stmt->error
    ]));
}

$stmt->close();

echo json_encode([
    'success' => true,
    'message' => 'Cobrança cadastrada com sucesso.'
]);