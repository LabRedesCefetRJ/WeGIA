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
$pdo = Conexao::connect();

// Pessoa
require_once '../../Functions/ValidarDependente.php';

$cpf = $_POST['cpf'];
$id_parentesco = $_POST['id_parentesco'];
$id_funcionario = $_POST['id_funcionario'];

//Verfica CPF da pessoa e compara com o digitado.
try {
    $stmt = $pdo->prepare("SELECT * FROM pessoa WHERE cpf = :cpf");
    $stmt->bindParam(":cpf", $cpf);
    $stmt->execute();
    $id_pessoa = $stmt->fetch(PDO::FETCH_ASSOC)["id_pessoa"];

    $stmt = $pdo->prepare("SELECT id_pessoa FROM funcionario WHERE id_funcionario = :id_funcionario");
    $stmt->bindParam(":id_funcionario", $id_funcionario);
    $stmt->execute();
    $id_pessoa_funcionario = $stmt->fetch(PDO::FETCH_ASSOC)["id_pessoa"];
} catch (PDOException $th) {
    echo "Um erro ocorreu na validação do CPF";
    die();
}

if($id_pessoa == $id_pessoa_funcionario) {
    echo "Você está adicionando um cpf do próprio funcionário.";
    die();
} else {
    //Se a pessoa já está cadastrada no BD
    if($id_pessoa) {
        try {
            $stmt = $pdo->prepare("SELECT id_dependente FROM funcionario_dependentes WHERE id_pessoa = :id and id_funcionario = :funcionario");
            $stmt->bindParam(":id", $id_pessoa);
            $stmt->bindParam(":funcionario", $id_funcionario);
            $stmt->execute();
            $pessoaJaCadastrada = $stmt->fetch(PDO::FETCH_ASSOC)["id_dependente"];
        } catch (PDOException $th) {
            echo "Um erro ocorreu na validação do parentesco";
            die();
        }

        //Pessoa ainda não foi cadastrada como dependente
        if($pessoaJaCadastrada === NULL) {
            $id_funcionario = trim($id_funcionario);
            $id_pessoa = trim($id_pessoa);
            $id_parentesco = trim($id_parentesco);
        
            try {
            
                if(!is_numeric($id_funcionario) || !is_numeric($id_pessoa) || !is_numeric($id_parentesco)){
                    echo 'Erro: Os parâmetros informados para a consulta não correspondem a um tipo válido de ID';
                    die();
                }
                $sql = "INSERT IGNORE INTO funcionario_dependentes (id_funcionario, id_pessoa, id_parentesco) VALUES (:id_funcionario, :id_pessoa, :id_parentesco)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id_funcionario', $id_funcionario);
                $stmt->bindParam(':id_pessoa', $id_pessoa);
                $stmt->bindParam(':id_parentesco', $id_parentesco);
                $stmt->execute();
            } catch (PDOException $th) {
                echo "Houve um erro ao adicionar o dependente ao banco de dados: $th";
                die();
            }
        } else {
            echo "Essa pessoa já foi cadastrada";
            die();
        }
    } else {
        $_SESSION['cpf_digitado'] = $cpf;
        $_SESSION['parentesco_previo'] = $id_parentesco;
        header('Location: cadastro_dependente_pessoa_nova.php?id_funcionario=' . htmlspecialchars($id_funcionario));
        die();
    }
}
header('Location: profile_funcionario.php?id_funcionario=' . htmlspecialchars($id_funcionario));