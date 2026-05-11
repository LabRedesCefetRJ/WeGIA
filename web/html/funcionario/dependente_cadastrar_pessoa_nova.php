<?php
if(session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
    exit();
}else{
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);
require_once '../../dao/Conexao.php';
require_once '../../classes/Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'geral' . DIRECTORY_SEPARATOR . 'msg.php';
$pdo = Conexao::connect();

$id_funcionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
$redirectCadastro = 'cadastro_dependente_pessoa_nova.php?id_funcionario=' . urlencode((string)$id_funcionario);

function redirectNovoDependenteError(string $message, string $field = 'global'): void
{
    global $redirectCadastro;
    setSessionFormData($_POST);
    setSessionFormErrors([$field => $message]);
    setSessionMsg($message, 'err');
    header("Location: $redirectCadastro");
    exit();
}

if (!$id_funcionario || $id_funcionario < 1) {
    redirectNovoDependenteError('O id do funcionário informado não é válido.');
}

// Pessoa

    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $sobrenome = filter_input(INPUT_POST, 'sobrenome', FILTER_SANITIZE_SPECIAL_CHARS);
    $sexo = filter_input(INPUT_POST, 'sexo', FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $data_nascimento = filter_input(INPUT_POST, 'nascimento', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_parentesco = filter_input(INPUT_POST, 'id_parentesco', FILTER_SANITIZE_NUMBER_INT);
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
    $registro_geral = filter_input(INPUT_POST, 'rg', FILTER_SANITIZE_SPECIAL_CHARS);
    $orgao_emissor = filter_input(INPUT_POST, 'orgao_emissor', FILTER_SANITIZE_SPECIAL_CHARS);
    $data_expedicao = filter_input(INPUT_POST, 'data_expedicao', FILTER_SANITIZE_SPECIAL_CHARS);

    try {
        Util::validarNomePessoaOuLancar($nome, 'nome', 400);
        Util::validarNomePessoaOuLancar($sobrenome, 'sobrenome', 400);
    } catch (InvalidArgumentException $e) {
        setSessionFormErrorFromMessage($e->getMessage());
        redirectNovoDependenteError($e->getMessage(), getSessionFormErrors(false) ? array_key_first(getSessionFormErrors(false)) : 'global');
    }

    if (!$cpf || !Util::validarCPF($cpf)) {
        redirectNovoDependenteError('O CPF informado não é válido.', 'cpf');
    }

    if ($sexo !== 'm' && $sexo !== 'f') {
        redirectNovoDependenteError('O sexo informado não é válido.', 'sexo');
    }

    if (!$id_parentesco || $id_parentesco < 1) {
        redirectNovoDependenteError('O parentesco informado não é válido.', 'id_parentesco');
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
    } catch (PDOException $th) {
        redirectNovoDependenteError('Erro ao inserir a pessoa no banco de dados.');
    }

    // Dependente
    try {
        $sql = "SELECT id_pessoa FROM pessoa WHERE cpf =:cpf";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->execute();
        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_pessoa = $pessoa["id_pessoa"] ?? null;
    } catch (PDOException $th) {
        redirectNovoDependenteError('Erro ao obter a pessoa cadastrada no banco de dados.');
    }

    try {
        $id_funcionario = trim($id_funcionario);
        $id_pessoa = trim($id_pessoa);
        $id_parentesco = trim($id_parentesco);

        if(!is_numeric($id_funcionario) || !is_numeric($id_pessoa) || !is_numeric($id_parentesco)){
            redirectNovoDependenteError('Os parâmetros informados não correspondem a um tipo válido de ID.');
        }
        $sql = "INSERT IGNORE INTO funcionario_dependentes (id_funcionario, id_pessoa, id_parentesco) VALUES (:id_funcionario, :id_pessoa, :id_parentesco)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_funcionario', $id_funcionario);
        $stmt->bindParam(':id_pessoa', $id_pessoa);
        $stmt->bindParam(':id_parentesco', $id_parentesco);
        $stmt->execute();
    } catch (PDOException $th) {
        redirectNovoDependenteError('Erro ao adicionar o dependente ao banco de dados.');
    }

header("Location: profile_funcionario.php?id_funcionario=$id_funcionario");
