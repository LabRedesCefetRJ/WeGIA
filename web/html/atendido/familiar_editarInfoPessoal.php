<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
//Refatorar para MVC
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);
require_once '../../dao/Conexao.php';

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

$id = filter_input(INPUT_GET, 'id_pessoa', FILTER_SANITIZE_NUMBER_INT);
$idatendido_familiares = filter_input(INPUT_GET, 'idatendido_familiares', FILTER_SANITIZE_NUMBER_INT);

$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
$sobrenome = filter_input(INPUT_POST, 'sobrenomeForm', FILTER_SANITIZE_SPECIAL_CHARS);
$sexo = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS);

$telefone = filter_input(
    INPUT_POST,
    'telefone',
    FILTER_SANITIZE_NUMBER_INT
);

$data_nascimento = filter_input(
    INPUT_POST,
    'nascimento',
    FILTER_SANITIZE_SPECIAL_CHARS
);

$nome_mae = filter_input(INPUT_POST, 'nome_mae', FILTER_SANITIZE_SPECIAL_CHARS);
$nome_pai = filter_input(INPUT_POST, 'nome_pai', FILTER_SANITIZE_SPECIAL_CHARS);

define("ALTERAR_INFO_PESSOAL", "UPDATE pessoa SET nome=:nome, sobrenome=:sobrenome, sexo=:sexo, data_nascimento=:data_nascimento, telefone=:telefone, nome_mae=:nome_mae, nome_pai=:nome_pai where id_pessoa = :id");

if (!$id || !is_numeric($id)) {
    $_SESSION['msg'] = 'Erro, o valor do id fornecido para uma pessoa não é válido.';
    $_SESSION['tipo'] = 'error';
    header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
    exit();
}

if (!$idatendido_familiares || !is_numeric($idatendido_familiares)) {
    $_SESSION['msg'] = 'Erro, o valor do id fornecido para um familiar não é válido.';
    $_SESSION['tipo'] = 'error';
    header("Location: ../home.php");
    exit();
}

if (!$nome || empty($nome) || !$sobrenome || empty($sobrenome)) {
    $_SESSION['msg'] = 'Erro, as informações de nome e sobrenome estão faltando.';
    $_SESSION['tipo'] = 'error';
    header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
    exit();
}

try {
    Util::validarNomePessoaOuLancar($nome, 'nome', 400);
    Util::validarNomePessoaOuLancar($sobrenome, 'sobrenome', 400);
    Util::validarNomePessoaOpcionalOuLancar($nome_pai, 'nome do pai', 400);
    Util::validarNomePessoaOpcionalOuLancar($nome_mae, 'nome da mãe', 400);
} catch (InvalidArgumentException $e) {
    $_SESSION['msg'] = $e->getMessage();
    $_SESSION['tipo'] = 'error';
    header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
    exit();
}

if ($sexo != 'm' && $sexo != 'f') {
    $_SESSION['msg'] = 'Erro, a opção de sexo fornecida não é válida.';
    $_SESSION['tipo'] = 'error';
    header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
    exit();
}

if (!$telefone || empty($telefone)) {
    $telefone = '';
} else {
    $telefone = Util::validarTelefone($telefone);

    if (!$telefone) {
        $_SESSION['msg'] = 'Erro, o telefone fornecido não está em um formato válido.';
        $_SESSION['tipo'] = 'error';
        header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
        exit();
    }
}

if (!$data_nascimento || empty($data_nascimento)) { //Posteriormente fazer validação do formato da data de nascimento quando o respectivo método for implementado na classe Util.php
    $_SESSION['msg'] = 'Erro, a data de nascimento fornecida não está em um formato válido.';
    $_SESSION['tipo'] = 'error';
    header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
    exit();
}

if ($data_nascimento && $id) {
    try {
        $pdo = Conexao::connect();

        // Buscar data de expedição atual da pessoa
        $sql_expedicao = "SELECT data_expedicao FROM pessoa WHERE id_pessoa = :id_pessoa";
        $stmt_expedicao = $pdo->prepare($sql_expedicao);
        $stmt_expedicao->bindParam(':id_pessoa', $id);
        $stmt_expedicao->execute();
        $pessoa_doc = $stmt_expedicao->fetch(PDO::FETCH_ASSOC);

        // Só valida se existe data de expedição no banco
        if ($pessoa_doc && $pessoa_doc['data_expedicao']) {
            $data_nascimento_obj = new DateTime($data_nascimento);
            $data_expedicao_obj = new DateTime($pessoa_doc['data_expedicao']);

            if ($data_nascimento_obj >= $data_expedicao_obj) {
                $_SESSION['msg'] = 'A data de nascimento não pode ser posterior ou igual à data de expedição do documento!';
                $_SESSION['tipo'] = 'error';
                header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
                exit();
            }
        }
        // Se não existe data de expedição no banco, permite a alteração sem validação
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Erro ao consultar o banco de dados para verificação das datas de nascimento e expedição. {$e->getMessage()}";
        $_SESSION['tipo'] = 'error';
        header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
        exit();
    }
}

try {
    $pdo = Conexao::connect();
    $pessoa = $pdo->prepare(ALTERAR_INFO_PESSOAL);
    $pessoa->bindValue(":id", $id);
    $pessoa->bindValue(":nome", $nome);
    $pessoa->bindValue(":sobrenome", $sobrenome);
    $pessoa->bindValue(":sexo", $sexo);
    $pessoa->bindValue(":telefone", $telefone);
    $pessoa->bindValue(":data_nascimento", $data_nascimento);
    $pessoa->bindValue(":nome_mae", $nome_mae);
    $pessoa->bindValue(":nome_pai", $nome_pai);
    $pessoa->execute();
} catch (PDOException $th) {
    if ($th->getCode() == 23000) {
        $_SESSION['msg'] = "Erro: Dados duplicados ou inválidos.";
    } else {
        $_SESSION['msg'] = "Houve um erro ao atualizar as informações: " . $th->getMessage();
    }
    $_SESSION['tipo'] = "error";
    header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
    exit();
}

header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
