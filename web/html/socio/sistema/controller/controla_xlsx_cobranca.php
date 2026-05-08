<?php
//verificar privilégios do solicitante
session_start();
require_once(dirname(__DIR__, 3) . '/permissao/permissao.php');

if (!isset($_SESSION['id_pessoa'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Acesso não autorizado, usuário não está logado.']);
    exit();
}

permissao($_SESSION['id_pessoa'], 4, 3);

$r = array(
    "resultado" => false,
    "url" => null
);

if (!is_dir("../tabelas/")) {
    if (mkdir("../tabelas")) {
        mkdir("../tabelas/cobrancas/");
    }
}

if (!empty($_FILES['arquivo']['name'])) {
    $permitidos = array('xlsx');
    $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));

    if (in_array($extensao, $permitidos)) {
        $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($_FILES['arquivo']['name']));

        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], "../tabelas/cobrancas/" . $file_name)) {
            $r['resultado'] = true;
            $r['url'] = "./tabelas/cobrancas/" . htmlspecialchars($file_name);
        } else {
            $r['url'] = "Erro ao mover o arquivo.";
        }
    } else {
        $r['url'] = "Tipo de arquivo não permitido.";
    }
}

echo json_encode($r);
