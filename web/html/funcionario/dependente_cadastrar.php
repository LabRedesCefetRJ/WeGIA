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

// Pessoa
require_once '../../Functions/ValidarDependente.php';

$cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
$id_parentesco = filter_input(INPUT_POST, 'id_parentesco', FILTER_SANITIZE_NUMBER_INT);
$id_funcionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);

$redirectProfile = 'profile_funcionario.php?id_funcionario=' . urlencode((string)$id_funcionario);

function redirectDependenteError(string $message, string $field = 'global'): void
{
    global $redirectProfile;
    setSessionFormData($_POST);
    setSessionFormErrors([$field => $message]);
    setSessionOpenModal('depFormModal');
    setSessionMsg($message, 'err');
    header("Location: $redirectProfile");
    exit();
}

if (!$id_funcionario || $id_funcionario < 1) {
    redirectDependenteError('O id do funcionário informado não é válido.');
}

if (!$id_parentesco || $id_parentesco < 1) {
    redirectDependenteError('O parentesco informado não é válido.', 'id_parentesco');
}

if (!$cpf || !Util::validarCPF($cpf)) {
    redirectDependenteError('O CPF informado não é válido.', 'cpf');
}

//Verfica CPF da pessoa e compara com o digitado.
try {
    $stmt = $pdo->prepare("SELECT * FROM pessoa WHERE cpf = :cpf");
    $stmt->bindParam(":cpf", $cpf);
    $stmt->execute();
    $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_pessoa = $pessoa["id_pessoa"] ?? null;

    $stmt = $pdo->prepare("SELECT id_pessoa FROM funcionario WHERE id_funcionario = :id_funcionario");
    $stmt->bindParam(":id_funcionario", $id_funcionario);
    $stmt->execute();
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_pessoa_funcionario = $funcionario["id_pessoa"] ?? null;
} catch (PDOException $th) {
    redirectDependenteError('Erro ao validar o CPF informado.', 'cpf');
}

if($id_pessoa == $id_pessoa_funcionario) {
    redirectDependenteError('Você está adicionando o CPF do próprio funcionário.', 'cpf');
} else {
    //Se a pessoa já está cadastrada no BD
    if($id_pessoa) {
        try {
            $stmt = $pdo->prepare("SELECT id_dependente FROM funcionario_dependentes WHERE id_pessoa = :id and id_funcionario = :funcionario");
            $stmt->bindParam(":id", $id_pessoa);
            $stmt->bindParam(":funcionario", $id_funcionario);
            $stmt->execute();
            $dependente = $stmt->fetch(PDO::FETCH_ASSOC);
            $pessoaJaCadastrada = $dependente["id_dependente"] ?? null;
        } catch (PDOException $th) {
            redirectDependenteError('Erro ao validar o parentesco informado.', 'id_parentesco');
        }

        //Pessoa ainda não foi cadastrada como dependente
        if($pessoaJaCadastrada === NULL) {
            $id_funcionario = trim($id_funcionario);
            $id_pessoa = trim($id_pessoa);
            $id_parentesco = trim($id_parentesco);
        
            try {
            
                if(!is_numeric($id_funcionario) || !is_numeric($id_pessoa) || !is_numeric($id_parentesco)){
                    redirectDependenteError('Os parâmetros informados não correspondem a um tipo válido de ID.');
                }
                $sql = "INSERT IGNORE INTO funcionario_dependentes (id_funcionario, id_pessoa, id_parentesco) VALUES (:id_funcionario, :id_pessoa, :id_parentesco)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id_funcionario', $id_funcionario);
                $stmt->bindParam(':id_pessoa', $id_pessoa);
                $stmt->bindParam(':id_parentesco', $id_parentesco);
                $stmt->execute();
            } catch (PDOException $th) {
                redirectDependenteError('Erro ao adicionar o dependente ao banco de dados.');
            }
        } else {
            redirectDependenteError('Essa pessoa já foi cadastrada como dependente deste funcionário.', 'cpf');
        }
    } else {
        $_SESSION['cpf_digitado'] = $cpf;
        $_SESSION['parentesco_previo'] = $id_parentesco;
        header('Location: cadastro_dependente_pessoa_nova.php?id_funcionario=' . htmlspecialchars($id_funcionario));
        exit();
    }
}
header('Location: profile_funcionario.php?id_funcionario=' . htmlspecialchars($id_funcionario));
