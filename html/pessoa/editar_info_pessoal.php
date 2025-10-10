<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
} else {
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 1, 3);

require_once "../../dao/Conexao.php";
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Atendido.php';

$id_pessoa = filter_input(INPUT_POST, 'id_pessoa', FILTER_SANITIZE_NUMBER_INT);
$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
$sobrenome = filter_input(INPUT_POST, 'sobrenome', FILTER_SANITIZE_SPECIAL_CHARS);
$telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
$sexo = filter_input(INPUT_POST, 'sexo', FILTER_SANITIZE_SPECIAL_CHARS);
$data_nascimento = filter_input(INPUT_POST, 'data_nascimento', FILTER_SANITIZE_SPECIAL_CHARS);
$nome_pai = filter_input(INPUT_POST, 'nome_pai', FILTER_SANITIZE_SPECIAL_CHARS);
$nome_mae = filter_input(INPUT_POST, 'nome_mae', FILTER_SANITIZE_SPECIAL_CHARS);

try {
    if(!$id_pessoa || $id_pessoa < 1)
        throw new InvalidArgumentException('O id da pessoa fornecido não é válido.', 422);
    
    if(!$nome || strlen($nome) < 3)
        throw new InvalidArgumentException('O nome informado não contém a quantidade mínima de caracteres necessária.', 422);

    if(!$sobrenome || strlen($sobrenome) < 3)
        throw new InvalidArgumentException('O sobrenome informado não contém a quantidade mínima de caracteres necessária.', 422);

    if(!$nome_pai || strlen($nome_pai) < 3)
        throw new InvalidArgumentException('O nome do pai informado não contém a quantidade mínima de caracteres necessária.', 422);

    if(!$nome_mae || strlen($nome_mae) < 3)
        throw new InvalidArgumentException('O nome da mãe informado não contém a quantidade mínima de caracteres necessária.', 422);

    if($sexo != 'm' && $sexo !='f')
        throw new InvalidArgumentException('O sexo informado não é válido.', 422);

    if($data_nascimento < Atendido::getDataNascimentoMinima())
        throw new InvalidArgumentException('A data de nascimento informada não é válida.', 422) ;

    $telefone = Util::validarTelefone($telefone);
    if($telefone === false)
        throw new InvalidArgumentException('O telefone informado não está dentro do formato esperado.', 400);

    // Buscar data de expedição atual da pessoa
    $sql_expedicao = "SELECT data_expedicao FROM pessoa WHERE id_pessoa = :id_pessoa";
    $stmt_expedicao = $pdo->prepare($sql_expedicao);
    $stmt_expedicao->bindParam(':id_pessoa', $id_pessoa);
    $stmt_expedicao->execute();
    $pessoa_doc = $stmt_expedicao->fetch(PDO::FETCH_ASSOC);

    var_dump($pessoa_doc);
    die();
    
    if ($pessoa_doc && $pessoa_doc['data_expedicao']) {
        $data_nascimento_obj = new DateTime($data_nascimento);
        $data_expedicao_obj = new DateTime($pessoa_doc['data_expedicao']);
        
        if ($data_nascimento_obj >= $data_expedicao_obj) {
            http_response_code(400);
            die(json_encode( [ 'Erro: A data de nascimento não pode ser posterior ou igual à data de expedição do documento!' ] ) );
        }
    }

    $sql = "UPDATE pessoa SET nome = :nome, sobrenome = :sobrenome, telefone = :telefone, sexo = :sexo, nome_pai = :nome_pai, nome_mae = :nome_mae, data_nascimento = :data_nascimento WHERE id_pessoa = :id_pessoa";

    $pdo = Conexao::connect();

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_pessoa', $id_pessoa, PDO::PARAM_INT);
    $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindParam(':sobrenome', $sobrenome, PDO::PARAM_STR);
    $stmt->bindParam(':telefone', $telefone, PDO::PARAM_STR);
    $stmt->bindParam(':sexo', $sexo, PDO::PARAM_STR);
    $stmt->bindParam(':data_nascimento', $data_nascimento, PDO::PARAM_STR);
    $stmt->bindParam(':nome_pai', $nome_pai, PDO::PARAM_STR);
    $stmt->bindParam(':nome_mae', $nome_mae, PDO::PARAM_STR);

    $stmt->execute();
    echo json_encode("Dados atualizados com sucesso");
} catch (Exception $e) {
    Util::tratarException($e);
}
