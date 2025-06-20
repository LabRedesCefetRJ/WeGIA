<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["status" => "erro", "mensagem" => "Usuário não autenticado"]);
    exit;
}

require_once "../../dao/Conexao.php";

$pdo = Conexao::connect();
$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados) {
    echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos"]);
    exit;
}

$id_medicacao = $dados['id_medicacao'] ?? null;
$pessoa_id_pessoa = $dados['id_pessoa'] ?? null;
$aplicacao = $dados['dataHora'] ?? null;
$id_fichamedica = $_SESSION['id_upload_med'] ?? null;

if (!$id_medicacao || !$pessoa_id_pessoa || !$aplicacao) {
    echo json_encode(["status" => "erro", "mensagem" => "Campos obrigatórios ausentes"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id_funcionario 
        FROM pessoa p 
        JOIN funcionario f ON (p.id_pessoa = f.id_pessoa) 
        WHERE f.id_pessoa = ?
    ");
    $stmt->execute([$_SESSION['id_pessoa']]);
    $func = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$func) {
        echo json_encode(["status" => "erro", "mensagem" => "Funcionário não encontrado"]);
        exit;
    }

    $funcionario_id_funcionario = $func['id_funcionario'];

    date_default_timezone_set('America/Sao_Paulo');
    $registro = date('Y-m-d H:i:s');

    $prep = $pdo->prepare("
        INSERT INTO saude_medicamento_administracao (
            aplicacao, registro, saude_medicacao_id_medicacao, 
            pessoa_id_pessoa, funcionario_id_funcionario
        ) VALUES (
            :aplicacao, :registro, :saude_medicacao_id_medicacao, 
            :pessoa_id_pessoa, :funcionario_id_funcionario
        )
    ");

    $prep->execute([
        ':aplicacao' => $aplicacao,
        ':registro' => $registro,
        ':saude_medicacao_id_medicacao' => $id_medicacao,
        ':pessoa_id_pessoa' => $pessoa_id_pessoa,
        ':funcionario_id_funcionario' => $funcionario_id_funcionario
    ]);

    echo json_encode([
        "status" => "sucesso",
        "mensagem" => "Aplicação registrada com sucesso"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro ao registrar aplicação: " . $e->getMessage()
    ]);
}
