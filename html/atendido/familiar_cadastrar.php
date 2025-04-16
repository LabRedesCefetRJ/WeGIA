<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);
require_once '../../dao/Conexao.php';
$pdo = Conexao::connect();

// Pessoa
require_once '../../Functions/ValidarDependente.php';

$cpf = $_POST['cpf'];
$id_parentesco = $_POST['id_parentesco'];
$idatendido = $_POST['idatendido'];

//Verfica CPF da pessoa e compara com o digitado.
try {
    $xablau = $pdo->prepare("SELECT * FROM pessoa WHERE cpf = :cpf");
    $xablau->bindParam(":cpf", $cpf);
    $xablau->execute();
    $id_pessoa = $xablau->fetch(PDO::FETCH_ASSOC)["id_pessoa"];

    $stmt = $pdo->prepare("SELECT pessoa_id_pessoa FROM atendido WHERE idatendido = :id_atendido");
    $stmt->bindParam(":id_atendido", $idatendido);
    $stmt->execute();
    $id_pessoa_atendido = $stmt->fetch(PDO::FETCH_ASSOC)["id_pessoa"];
} catch (PDOException $th) {
    echo "Um erro ocorreu na validação do CPF " . $th;
    die();
}

if($id_pessoa == $id_pessoa_atendido) {
    echo "Você está adicionando um cpf do próprio atendido.";
    die();
} else {
    //Se a pessoa já está cadastrada no BD
    if($id_pessoa) {
        try {
            $xablau = $pdo->prepare("SELECT idatendido_familiares FROM atendido_familiares WHERE pessoa_id_pessoa = :id AND atendido_idatendido = :atendido");
            $xablau->bindParam(":id", $id_pessoa);
            $xablau->bindParam(":atendido", $idatendido);
            $xablau->execute();
            $pessoaJaCadastrada = $xablau->fetch(PDO::FETCH_ASSOC)["idatendido_familiares"];
        } catch (PDOException $th) {
            echo "Um erro ocorreu na validação do parentesco";
            die();
        }
        //Pessoa ainda não foi cadastrada como dependente
        if($pessoaJaCadastrada === NULL) {
            define("NOVO_FAMILIAR", "INSERT IGNORE INTO atendido_familiares (atendido_idatendido, pessoa_id_pessoa, atendido_parentesco_idatendido_parentesco ) VALUES (:idatendido, :id_pessoa, :id_parentesco);");
            
            try {
                $stmt = $pdo->prepare(NOVO_FAMILIAR);
                $stmt->bindParam(":idatendido", $idatendido);
                $stmt->bindParam(":id_pessoa", $id_pessoa);
                $stmt->bindParam(":id_parentesco", $id_parentesco);
                $stmt->execute();
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