<?php
//refatorar para POO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

$id_pessoa = filter_var($_SESSION['id_pessoa'] ?? null, FILTER_SANITIZE_NUMBER_INT);
if (!$id_pessoa || $id_pessoa < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id da pessoa informado não é válido.']);
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($id_pessoa, 4, 5);

require("../conexao.php");
$conexao->set_charset("utf8mb4");

// coletar parâmetros via GET
$input = $_GET;

// normalizar / extrair
$status      = isset($input['status']) ? trim($input['status']) : 'x';
$tag         = isset($input['tag']) ? trim($input['tag']) : 'x';
$valor       = isset($input['valor']) ? trim($input['valor']) : null;
$operador    = isset($input['operador']) ? trim($input['operador']) : null;
$tipo_pessoa = isset($input['tipo_pessoa']) ? trim($input['tipo_pessoa']) : null;
$tipo_socio  = isset($input['tipo_socio']) ? trim($input['tipo_socio']) : 'x';

//pegar informações de data de contribuição, data inicio e data fim
$data_contribuicao = isset($input['data-contribuicao']) ? trim($input['data-contribuicao']) : null;
$data_inicio = isset($input['data_inicio']) ? trim($input['data_inicio']) : null;
$data_fim = isset($input['data_fim']) ? trim($input['data_fim']) : null;

// validar datas no formato ISO (YYYY-MM-DD)
function validarData($d) {
    if (empty($d)) return false;
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}

$dataInicioValido = validarData($data_inicio);
$dataFimValido   = validarData($data_fim);

if ($data_contribuicao === 'partir' && !$dataInicioValido) {
    $data_inicio = null;
}
if ($data_contribuicao === 'ate' && !$dataFimValido) {
    $data_fim = null;
}
if ($data_contribuicao === 'entre') {
    if (!$dataInicioValido || !$dataFimValido) {
        $data_contribuicao = 'qualquer';
    }
}

// validação básica
if ($status !== 'x') {
    $status = filter_var($status, FILTER_VALIDATE_INT);
    if ($status === false) $status = 'x';
}
if ($tag !== 'x') {
    $tag = filter_var($tag, FILTER_VALIDATE_INT);
    if ($tag === false) $tag = 'x';
}

// operadores permitidos (mapeamento seguro)
$operatorMap = [
    'maior_q'  => '>',
    'maior_ia' => '>=',
    'igual_a'  => '=',
    'menor_ia' => '<=',
    'menor_q'  => '<'
];
$op = $operatorMap[$operador] ?? null;

// tratar valor numérico
if ($valor === null || $valor === '') {
    $valor = null;
    $op = null;
} else {
    $valor = str_replace(',', '.', $valor);
    if (!is_numeric($valor)) {
        $valor = null;
        $op = null;
    } else {
        $valor = (float)$valor;
    }
}

// tipo_pessoa (comprimento do CPF/CNPJ)
$tipoPessoaLen = null;
if ($tipo_pessoa === 'f') $tipoPessoaLen = 14;
if ($tipo_pessoa === 'j') $tipoPessoaLen = 18;

// tipo_socio -> ids
$tipoSocioMap = [
    'c' => [0,1, 20, 21, 40, 41],
    'b' => [6,7, 24, 25, 44, 45],
    't' => [8,9, 26, 27, 46, 47],
    's' => [10,11, 28, 29, 48, 49],
    'm' => [2,3, 22, 23, 42, 43]
];
$tipoSocioIds = $tipoSocioMap[$tipo_socio] ?? null;

// SQL base
$base = "SELECT p.nome, p.telefone, p.cpf, s.valor_periodo, s.email, st.tipo, ss.status, stag.tag
FROM pessoa p
JOIN socio s ON (p.id_pessoa = s.id_pessoa)
JOIN socio_tipo st ON (s.id_sociotipo = st.id_sociotipo)
JOIN socio_status ss ON (ss.id_sociostatus = s.id_sociostatus)
LEFT JOIN socio_tag stag ON stag.id_sociotag = s.id_sociotag";

$whereClauses = [];
$params = []; // valores para bind (posicionais)
$types = '';  // tipos para bind (i,d,s)

// montar where de forma segura
if ($status !== 'x') {
    $whereClauses[] = "s.id_sociostatus = ?";
    $params[] = (int)$status;
    $types .= 'i';
}
if ($tag !== 'x') {
    $whereClauses[] = "s.id_sociotag = ?";
    $params[] = (int)$tag;
    $types .= 'i';
}
if ($op !== null && $valor !== null) {
    // operador já validado a partir do map
    $whereClauses[] = "s.valor_periodo $op ?";
    $params[] = $valor;
    $types .= 'd';
}
if ($tipoPessoaLen !== null) {
    $whereClauses[] = "LENGTH(p.cpf) = ?";
    $params[] = (int)$tipoPessoaLen;
    $types .= 'i';
}
if (!empty($tipoSocioIds)) {
    $placeholders = implode(',', array_fill(0, count($tipoSocioIds), '?'));
    $whereClauses[] = "s.id_sociotipo IN ($placeholders)";
    foreach ($tipoSocioIds as $id) {
        $params[] = (int)$id;
        $types .= 'i';
    }
}

// filtro de data_referencia em socio conforme data_contribuicao
if ($data_contribuicao === 'partir' && $dataInicioValido) {
    $whereClauses[] = "s.data_referencia >= ?";
    $params[] = $data_inicio;
    $types .= 's';
} elseif ($data_contribuicao === 'ate' && $dataFimValido) {
    $whereClauses[] = "s.data_referencia <= ?";
    $params[] = $data_fim;
    $types .= 's';
} elseif ($data_contribuicao === 'entre' && $dataInicioValido && $dataFimValido) {
    $whereClauses[] = "s.data_referencia BETWEEN ? AND ?";
    $params[] = $data_inicio;
    $params[] = $data_fim;
    $types .= 'ss';
}

// montar SQL final
$sql = $base;
if (count($whereClauses) > 0) {
    $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
}
$sql .= ' ORDER BY p.nome';

$stmt = $conexao->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao preparar a consulta.', 'detalhe' => $conexao->error]);
    exit();
}

// bind dinâmico se houver parâmetros
if (count($params) > 0) {
    // construir array de referências para bind_param
    $bind_names = [];
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        // criar referência
        $bind_names[] = &$params[$i];
    }
    // chamar bind_param dinamicamente
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao executar a consulta.', 'detalhe' => $stmt->error]);
    $stmt->close();
    exit();
}

// obter resultado (requer mysqlnd para get_result)
$result = $stmt->get_result();
$dados = [];
while ($row = $result->fetch_assoc()) {
    $dados[] = $row;
}
$stmt->close();

echo json_encode(!empty($dados) ? $dados : null, JSON_UNESCAPED_UNICODE);
