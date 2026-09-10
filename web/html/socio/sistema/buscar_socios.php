<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado.']);
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 4, 7);
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'conexao.php';

if (!isset($conexao) || $conexao === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao conectar ao banco de dados.']);
    exit();
}

mysqli_set_charset($conexao, 'utf8');

$termo = trim((string) filter_input(INPUT_GET, 'q', FILTER_UNSAFE_RAW));
if (mb_strlen($termo) < 2) {
    echo json_encode([]);
    exit();
}

$termoNome = '%' . $termo . '%';
$cpf = preg_replace('/\D+/', '', $termo);
$query = 'SELECT s.id_socio, p.nome, p.sobrenome, p.cpf
    FROM socio s
    INNER JOIN pessoa p ON p.id_pessoa = s.id_pessoa
    WHERE CONCAT(p.nome, " ", COALESCE(p.sobrenome, "")) LIKE ?';

if ($cpf !== '') {
    $query .= ' OR REPLACE(REPLACE(REPLACE(REPLACE(p.cpf, ".", ""), "-", ""), "/", ""), " ", "") LIKE ?';
}

$query .= '
    ORDER BY p.nome, p.sobrenome
    LIMIT 20';

$stmt = mysqli_prepare($conexao, $query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao preparar a busca de sócios.']);
    exit();
}

if ($cpf !== '') {
    $termoCpf = '%' . $cpf . '%';
    mysqli_stmt_bind_param($stmt, 'ss', $termoNome, $termoCpf);
} else {
    mysqli_stmt_bind_param($stmt, 's', $termoNome);
}
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$socios = [];
while ($socio = mysqli_fetch_assoc($resultado)) {
    $nome = trim($socio['nome'] . ' ' . ($socio['sobrenome'] ?? ''));
    $socios[] = [
        'id' => (int) $socio['id_socio'],
        'label' => $nome . ' - ' . $socio['cpf'],
        'value' => $nome . ' - ' . $socio['cpf']
    ];
}

mysqli_stmt_close($stmt);
echo json_encode($socios, JSON_UNESCAPED_UNICODE);
