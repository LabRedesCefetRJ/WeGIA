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

$cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
$id_parentesco = filter_input(INPUT_POST, 'id_parentesco', FILTER_SANITIZE_NUMBER_INT);
$idatendido = filter_input(INPUT_POST, 'idatendido', FILTER_SANITIZE_NUMBER_INT);

$util = new Util();
if(!$util->validarCPF($cpf)){
    http_response_code(400);
    echo json_encode(['erro' => 'O CPF informado não é válido.']);
    exit();
}

if(!$id_parentesco || $id_parentesco < 1){
    http_response_code(400);
    echo json_encode(['erro' => 'O id do parentesco não é válido.']);
    exit();
}

if(!$idatendido || $idatendido < 1){
    http_response_code(400);
    echo json_encode(['erro' => 'O id do atendido não é válido.']);
    exit();
}

//Verfica CPF da pessoa e compara com o digitado.
try {
    $stmt1 = $pdo->prepare("SELECT * FROM pessoa WHERE cpf = :cpf");
    $stmt1->bindParam(":cpf", $cpf);
    $stmt1->execute();
    $id_pessoa = $stmt1->fetch(PDO::FETCH_ASSOC)["id_pessoa"];

    $stmt2 = $pdo->prepare("SELECT pessoa_id_pessoa FROM atendido WHERE idatendido = :id_atendido");
    $stmt2->bindParam(":id_atendido", $idatendido);
    $stmt2->execute();
    $id_pessoa_atendido = $stmt2->fetch(PDO::FETCH_ASSOC)["pessoa_id_pessoa"];
} catch (PDOException $th) {
    echo "Um erro ocorreu na validação do CPF " . $th;
    die();
}

if ($id_pessoa == $id_pessoa_atendido) {
    echo "Você está adicionando um cpf do próprio atendido.";
    die();
} else {
    //Se a pessoa já está cadastrada no BD
    if ($id_pessoa) {
        try {
            $stmt1 = $pdo->prepare("SELECT idatendido_familiares FROM atendido_familiares WHERE pessoa_id_pessoa = :id AND atendido_idatendido = :atendido");
            $stmt1->bindParam(":id", $id_pessoa);
            $stmt1->bindParam(":atendido", $idatendido);
            $stmt1->execute();
            $pessoaJaCadastrada = $stmt1->fetch(PDO::FETCH_ASSOC)["idatendido_familiares"];
        } catch (PDOException $th) {
            echo "Um erro ocorreu na validação do parentesco";
            die();
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
                echo "Houve um erro ao adicionar o dependente ao banco de dados:";
                die();
            }
        } else {
            echo "Essa pessoa já foi cadastrada";
            die();
        }
    } else {
        $_SESSION['cpf_digitado'] = $cpf;
        $_SESSION['parentesco_previo'] = $id_parentesco;
        header("Location: cadastro_atendido_parentesco_pessoa_nova.php?idatendido=$idatendido");
        die();
    }
}
header("Location: Profile_Atendido.php?idatendido=$idatendido");
