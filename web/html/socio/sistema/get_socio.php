<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../../permissao/permissao.php");
require_once("../conexao.php");

header('Content-Type: application/json; charset=utf-8');

permissao($_SESSION['id_pessoa'], 4, 3);

$idSocio = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($idSocio === false || $idSocio === null || $idSocio < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de sócio inválido.']);
    exit();
}

if (!isset($conexao) || $conexao === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao conectar ao banco de dados.']);
    exit();
}

$conexao->set_charset("utf8");

$query = '
    SELECT
        s.id_socio,
        s.id_sociotipo,
        s.valor_periodo,
        p.nome
    FROM socio s
    INNER JOIN pessoa p ON p.id_pessoa = s.id_pessoa
    WHERE s.id_socio = ?
    LIMIT 1
';

$stmt = mysqli_prepare($conexao, $query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao preparar a consulta do sócio.']);
    exit();
}

$stmt->bind_param("i", $idSocio);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao executar a consulta do sócio.']);
    $stmt->close();
    exit();
}

$resultado = $stmt->get_result();
if (!$resultado || $resultado->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Sócio não encontrado.']);
    $stmt->close();
    exit();
}

$dados = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();

http_response_code(200);
echo json_encode($dados, JSON_UNESCAPED_UNICODE);
