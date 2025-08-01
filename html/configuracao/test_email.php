<?php

//ARQUIVO PARA TESTE DE ENVIO DE EMAIL (remover funcionalidade depois)
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'])) {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
    exit;
}

$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}


require_once "../../dao/Conexao.php";
require_once "../../controle/EmailControle.php";

try {
    $pdo = Conexao::connect();
    
    $emailControle = new EmailControle($pdo);
    
    $assunto = "Teste de Configuração SMTP - WeGIA";
    $mensagem = "
    <h2>Teste de Email</h2>
    <p>Este é um email de teste para verificar se as configurações SMTP estão funcionando corretamente.</p>
    <p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>
    <p><strong>Sistema:</strong> WeGIA</p>
    <hr>
    <p><em>Se você recebeu este email, significa que as configurações SMTP estão funcionando perfeitamente!</em></p>
    ";
    
    $resultado = $emailControle->enviarEmail($email, $assunto, $mensagem);
    
    if ($resultado['success']) {
        echo json_encode([
            'success' => true, 
            'message' => 'Email de teste enviado com sucesso para ' . $email
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao enviar email: ' . $resultado['message']
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?>