<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
extract($_REQUEST);
session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
}


// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 1, 3);

require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

extract($_POST);

if ($action == "mudarInfoPessoal"){
    $id_pessoa = $_POST['id_pessoa'];
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $telefone = $_POST['telefone'];
    $sexo = $_POST['sexo'];
    $data_nascimento = $_POST['data_nascimento'];
    $nome_pai = $_POST['nome_pai'];
    $nome_mae = $_POST['nome_mae'];

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

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_pessoa', $id_pessoa);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':sobrenome', $sobrenome);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':data_nascimento', $data_nascimento);
    $stmt->bindParam(':nome_pai', $nome_pai);
    $stmt->bindParam(':nome_mae', $nome_mae);

    try {
        $stmt->execute();
        echo json_encode("Dados atualizados com sucesso");
    } catch (PDOException $th) {
        echo json_encode($th->getMessage());
    }
}


// $nome = ($nome ? "'$nome'" : "NULL");
// $sobrenome = ($sobrenome ? "'$sobrenome'" : "NULL");
// $telefone = ($telefone ? "'$telefone'" : "NULL");
// $nome_pai = ($nome_pai ? "'$nome_pai'" : "NULL");
// $nome_mae = ($nome_mae ? "'$nome_mae'" : "NULL");
// $gender = ($gender ? "'$gender'" : "NULL");
// $sangue = ($sangue ? "'$sangue'" : "NULL");
// $nascimento = ($nascimento ? "'$nascimento'" : "NULL");

// $sql = "UPDATE pessoa SET nome=$nome, sobrenome=$sobrenome, telefone=$telefone, sexo=$sexo, nome_pai=$nome_pai, nome_mae=$nome_mae, data_nascimento=$data_nascimento  WHERE id_pessoa=$id_pessoa;";


// $sql = "UPDATE pessoa SET nome=:nome, sobrenome=:sobrenome, telefone=:telefone, sexo=:sexo, nome_pai=:nome_pai, nome_mae=:nome_mae, data_nascimento=:data_nascimento WHERE id_pessoa=:id_pessoa";

// $stmt->bindParam('id_pessoa',$id_pessoa);
// $stmt->bindParam('nome',$nome);
// $stmt->bindParam('sobrenome',$sobrenome);
// $stmt->bindParam(':cpf',$cpf);
// $stmt->bindParam(':sexo',$sexo);
// $stmt->bindParam(':telefone',$telefone);
// $stmt->bindParam(':data_nascimento',$data_nascimento);
// $stmt->bindParam(':nome_pai',$nome_pai);
// $stmt->bindParam(':nome_mae',$nome_mae);

// $stmt->execute();

// $pdo->query($sql);


$_GET['sql'] = $sql;
echo(json_encode($_GET));