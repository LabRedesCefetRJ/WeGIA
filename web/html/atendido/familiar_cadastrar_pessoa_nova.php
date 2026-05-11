<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}else{
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 12, 7);
require_once '../../dao/Conexao.php';
$pdo = Conexao::connect();

// Pessoa
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'geral' . DIRECTORY_SEPARATOR . 'msg.php';

$idatendido = filter_input(INPUT_POST, 'idatendido', FILTER_SANITIZE_NUMBER_INT);
$redirectCadastro = 'cadastro_atendido_parentesco_pessoa_nova.php?idatendido=' . urlencode((string)$idatendido);

function redirectNovoFamiliarError(string $message, string $field = 'global'): void
{
    global $redirectCadastro;
    setSessionFormData($_POST);
    setSessionFormErrors([$field => $message]);
    setSessionMsg($message, 'err');
    header("Location: $redirectCadastro");
    exit();
}

if(!$idatendido || $idatendido < 1){
    redirectNovoFamiliarError('O id do atendido não é válido.');
}

$cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
$util = new Util();

if($cpf && !$util->validarCPF($cpf)){
    redirectNovoFamiliarError('O CPF informado não é válido.', 'cpf');
}

$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
$sobrenome = filter_input(INPUT_POST, 'sobrenome', FILTER_SANITIZE_SPECIAL_CHARS);
$sexo = filter_input(INPUT_POST, 'sexo', FILTER_SANITIZE_SPECIAL_CHARS);

try {
    Util::validarNomePessoaOuLancar($nome, 'nome', 400);
    Util::validarNomePessoaOuLancar($sobrenome, 'sobrenome', 400);
} catch (InvalidArgumentException $e) {
    setSessionFormErrorFromMessage($e->getMessage());
    redirectNovoFamiliarError($e->getMessage(), getSessionFormErrors(false) ? array_key_first(getSessionFormErrors(false)) : 'global');
}

if($sexo != 'm' && $sexo != 'f'){
    redirectNovoFamiliarError('O sexo informado não é válido no sistema.', 'sexo');
}

$telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
$data_nascimento = filter_input(INPUT_POST, 'nascimento', FILTER_SANITIZE_SPECIAL_CHARS);
$id_parentesco = filter_input(INPUT_POST, 'id_parentesco', FILTER_SANITIZE_SPECIAL_CHARS);
$registro_geral = filter_input(INPUT_POST, 'rg', FILTER_SANITIZE_SPECIAL_CHARS);
$orgao_emissor = filter_input(INPUT_POST, 'orgao_emissor', FILTER_SANITIZE_SPECIAL_CHARS);
$data_expedicao = filter_input(INPUT_POST, 'data_expedicao', FILTER_SANITIZE_SPECIAL_CHARS);

if ($cpf === '') {
    $cpf = null;
}

define("NOVA_PESSOA", "INSERT IGNORE INTO pessoa (cpf, nome, sobrenome, sexo, telefone, data_nascimento, registro_geral, orgao_emissor, data_expedicao) VALUES (:cpf, :nome, :sobrenome, :sexo, :telefone, :data_nascimento, :registro_geral, :orgao_emissor, :data_expedicao)");

try {
    $pessoa = $pdo->prepare(NOVA_PESSOA);
    $pessoa->bindValue(":cpf", $cpf);
    $pessoa->bindValue(":nome", $nome);
    $pessoa->bindValue(":sobrenome", $sobrenome);
    $pessoa->bindValue(":sexo", $sexo);
    $pessoa->bindValue(":telefone", $telefone);
    $pessoa->bindValue(":data_nascimento", $data_nascimento);
    $pessoa->bindValue(":registro_geral", $registro_geral);
    $pessoa->bindValue(":orgao_emissor", $orgao_emissor);
    $pessoa->bindValue(":data_expedicao", $data_expedicao);
    $pessoa->execute();
    $id_pessoa = $pdo->lastInsertId();
} catch (PDOException $th) {
    redirectNovoFamiliarError('Erro ao inserir a pessoa no banco de dados.');
}

// Familiar

$id_parentesco = $_POST['id_parentesco'];

$id_parentesco = filter_input(INPUT_POST, 'id_parentesco', FILTER_SANITIZE_NUMBER_INT);

if(!$id_parentesco || $id_parentesco < 1){
    redirectNovoFamiliarError('O parentesco informado não é válido.', 'id_parentesco');
}

define("NOVO_FAMILIAR", "INSERT IGNORE INTO atendido_familiares (atendido_idatendido, pessoa_id_pessoa, atendido_parentesco_idatendido_parentesco ) VALUES (:idatendido, :id_pessoa, :id_parentesco);");

try {
    $stmt = $pdo->prepare(NOVO_FAMILIAR);
    $stmt->bindParam(":idatendido", $idatendido);
    $stmt->bindParam(":id_pessoa", $id_pessoa);
    $stmt->bindParam(":id_parentesco", $id_parentesco);
    $stmt->execute();
} catch (PDOException $th) {
    redirectNovoFamiliarError('Erro ao adicionar o familiar ao banco de dados.');
}

header("Location: Profile_Atendido.php?idatendido=$idatendido");
