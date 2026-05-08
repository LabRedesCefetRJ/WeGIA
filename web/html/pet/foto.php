<?php
$dao = 'dao/Conexao.php';
if( file_exists($dao)){
    require $dao;
}else{
    while(true){
        $dao = '../'.$dao;
        if( file_exists($dao)){
            break;
        }
    }
    require_once $dao;
}

// Recebe o JSON e decodifica
$post = json_decode(file_get_contents("php://input"), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    // Se houver um erro na decodificação JSON
    http_response_code(400);
    echo json_encode(["error" => "Dados JSON inválidos"]);
    exit;
}

// Valida e sanitiza o ID recebido
if (!isset($post['id']) || !is_numeric($post['id']) || intval($post['id']) <= 0) {
    // Se o ID não estiver presente ou não for um número positivo
    http_response_code(400);
    echo json_encode(["error" => "ID inválido"]);
    exit;
}

$id = intval($post['id']); // Sanitiza o ID convertendo para inteiro

// Conecta ao banco de dados usando PDO
try {
    $pdo = new PDO('mysql:host='.DB_HOST.'; dbname='.DB_NAME.'; charset=utf8', DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepara a consulta com um parâmetro
    $stmt = $pdo->prepare("
        SELECT p.id_pet_foto AS id_foto, pf.arquivo_foto_pet AS imagem 
        FROM pet p 
        JOIN pet_foto pf ON p.id_pet_foto = pf.id_pet_foto 
        WHERE p.id_pet = :id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Busca o resultado
    $petImagem = $stmt->fetch(PDO::FETCH_ASSOC);

    // Retorna o resultado como JSON
    echo json_encode($petImagem);

} catch (PDOException $e) {
    // Se houver um erro na consulta ou na conexão
    http_response_code(500);
    echo json_encode(["error" => "Erro no banco de dados: " . $e->getMessage()]);
}
