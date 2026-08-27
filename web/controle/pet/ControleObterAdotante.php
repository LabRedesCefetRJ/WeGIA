<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['id_pessoa'])) {
    http_response_code(401);
    die(json_encode(['erro' => 'Operação negada: Cliente não autorizado']));
}

require_once 'AdocaoControle.php';

$post = json_decode(file_get_contents("php://input"), true);

if (isset($post['comando']) && $post['comando'] === 'excluir' && isset($post['id_pet'])) {
    $resultado = $a->excluirAdocaoPet($post['id_pet']);
    echo json_encode(['status' => $resultado ? 'ok' : 'erro']);
    exit;
}

$id = $post['id'] ?? null;

if ($id) {
    $dados = $a->obterAdotante($id);
    echo json_encode($dados);
}
