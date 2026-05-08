<?php
require_once dirname(__FILE__, 5) . '/classes/Util.php';
Util::definirFusoHorario();
require_once dirname(__FILE__, 4) . '/seguranca/security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => 'Usuário não autenticado.']);
    exit();
}

require_once dirname(__FILE__, 4) . '/permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 4, 7);

require_once dirname(__FILE__, 5) . '/html/contribuicao/dao/SocioDAO.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $socioDao = new SocioDAO();

    if (!$socioDao->sincronizarStatusSocios()) {
        throw new RuntimeException('Não foi possível sincronizar o status dos sócios.');
    }

    echo json_encode(['mensagem' => 'Status dos sócios sincronizados com sucesso.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}
