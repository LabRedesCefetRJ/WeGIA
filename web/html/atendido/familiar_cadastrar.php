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
require_once '../../Functions/ValidarDependente.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'geral' . DIRECTORY_SEPARATOR . 'msg.php';

$cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
$id_parentesco = filter_input(INPUT_POST, 'id_parentesco', FILTER_SANITIZE_NUMBER_INT);
$idatendido = filter_input(INPUT_POST, 'idatendido', FILTER_SANITIZE_NUMBER_INT);

$redirectProfile = "Profile_Atendido.php?idatendido=" . urlencode((string)$idatendido);

function redirectFamiliarError(string $message, string $field = 'global'): void
{
    global $redirectProfile;
    setSessionFormData($_POST);
    setSessionFormErrors([$field => $message]);
    setSessionOpenModal('depFormModal');
    setSessionMsg($message, 'err');
    header("Location: $redirectProfile");
    exit();
}

$util = new Util();
if($cpf && !$util->validarCPF($cpf)){
    redirectFamiliarError('O CPF informado não é válido.', 'cpf');
}

if(!$id_parentesco || $id_parentesco < 1){
    redirectFamiliarError('O parentesco informado não é válido.', 'id_parentesco');
}

if(!$idatendido || $idatendido < 1){
    redirectFamiliarError('O id do atendido não é válido.');
}

//Verfica CPF da pessoa e compara com o digitado.
try {
    $stmt1 = $pdo->prepare("SELECT * FROM pessoa WHERE cpf = :cpf");
    $stmt1->bindParam(":cpf", $cpf);
    $stmt1->execute();
    $pessoa = $stmt1->fetch(PDO::FETCH_ASSOC);
    $id_pessoa = $pessoa["id_pessoa"] ?? null;

    $stmt2 = $pdo->prepare("SELECT pessoa_id_pessoa FROM atendido WHERE idatendido = :id_atendido");
    $stmt2->bindParam(":id_atendido", $idatendido);
    $stmt2->execute();
    $atendido = $stmt2->fetch(PDO::FETCH_ASSOC);
    $id_pessoa_atendido = $atendido["pessoa_id_pessoa"] ?? null;
} catch (PDOException $th) {
    redirectFamiliarError('Erro ao validar o CPF informado.', 'cpf');
}

if ($id_pessoa == $id_pessoa_atendido) {
    redirectFamiliarError('Você está adicionando o CPF do próprio atendido.', 'cpf');
} else {
    //Se a pessoa já está cadastrada no BD
    if ($id_pessoa) {
        try {
            $stmt1 = $pdo->prepare("SELECT idatendido_familiares FROM atendido_familiares WHERE pessoa_id_pessoa = :id AND atendido_idatendido = :atendido");
            $stmt1->bindParam(":id", $id_pessoa);
            $stmt1->bindParam(":atendido", $idatendido);
            $stmt1->execute();
            $familiar = $stmt1->fetch(PDO::FETCH_ASSOC);
            $pessoaJaCadastrada = $familiar["idatendido_familiares"] ?? null;
        } catch (PDOException $th) {
            redirectFamiliarError('Erro ao validar o parentesco informado.', 'id_parentesco');
        }
        //Pessoa ainda não foi cadastrada como dependente
        if ($pessoaJaCadastrada === NULL) {
            define("NOVO_FAMILIAR", "INSERT IGNORE INTO atendido_familiares (atendido_idatendido, pessoa_id_pessoa, atendido_parentesco_idatendido_parentesco ) VALUES (:idatendido, :id_pessoa, :id_parentesco);");

            try {
                $stmt2 = $pdo->prepare(NOVO_FAMILIAR);
                $stmt2->bindParam(":idatendido", $idatendido);
                $stmt2->bindParam(":id_pessoa", $id_pessoa);
                $stmt2->bindParam(":id_parentesco", $id_parentesco);
                $stmt2->execute();
            } catch (PDOException $th) {
                redirectFamiliarError('Erro ao adicionar o familiar ao banco de dados.');
            }
        } else {
            redirectFamiliarError('Essa pessoa já foi cadastrada como familiar deste atendido.', 'cpf');
        }
    } else {
        $_SESSION['cpf_digitado'] = $cpf;
        $_SESSION['parentesco_previo'] = $id_parentesco;
        header("Location: cadastro_atendido_parentesco_pessoa_nova.php?idatendido=$idatendido");
        exit();
    }
}
header("Location: Profile_Atendido.php?idatendido=$idatendido");
