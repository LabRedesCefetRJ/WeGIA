<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);
require_once '../../dao/Conexao.php';

$id = trim($_GET['id_pessoa']);
$idatendido_familiares = trim($_GET['idatendido_familiares']);
$nome = trim($_POST['nome']);
$sobrenome = trim($_POST['sobrenomeForm']);
$sexo = trim($_POST['gender']);
$telefone = trim($_POST['telefone']);
$data_nascimento = trim($_POST['nascimento']);
$nome_mae = trim($_POST['nome_mae']);
$nome_pai = trim($_POST['nome_pai']);

define("ALTERAR_INFO_PESSOAL", "UPDATE pessoa SET nome=:nome, sobrenome=:sobrenome, sexo=:sexo, data_nascimento=:data_nascimento, telefone=:telefone, nome_mae=:nome_mae, nome_pai=:nome_pai where id_pessoa = :id");

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    exit('Erro, o valor do id fornecido para uma pessoa não é válido.');
}

if (!$idatendido_familiares || !is_numeric($idatendido_familiares)) {
    http_response_code(400);
    exit('Erro, o valor do id fornecido para um familiar não é válido.');
}

if (!$nome || empty($nome) || !$sobrenome || empty($sobrenome)) {
    http_response_code(400);
    exit('Erro, as informações de nome e sobrenome estão faltando.');
}

if ($sexo != 'm' && $sexo != 'f') {
    http_response_code(400);
    exit('Erro, a opção de sexo fornecida não é válida.');
}

if (!$telefone || empty($telefone)) { //Posteiormente fazer validação do formato do telefone quando o respectivo método for implementado na classe Util.php
    http_response_code(400);
    exit('Erro, o telefone fornecido não está em um formato válido.');
}

if (!$data_nascimento || empty($data_nascimento)) { //Posteriormente fazer validação do formato da data de nascimento quando o respectivo método for implementado na classe Util.php
    http_response_code(400);
    exit('Erro, a data de nascimento fornecida não está em um formato válido.');
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
                //$_SESSION['msg'] = "Erro: A data de nascimento não pode ser posterior à data de expedição do documento!";
                //$_SESSION['tipo'] = "error";
                //header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
                //exit;
                die( json_encode( ['A data de nascimento não pode ser posterior ou igual à data de expedição do documento!'] ) );
            }
        }
        // Se não existe data de expedição no banco, permite a alteração sem validação
    } catch (PDOException $e) {
        die( json_encode( ["Erro ao consultar o banco de dados para verificação das datas de nascimento e expedição.{$e->getMessage()}"] ) );   
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
    echo "Houve um erro ao inserir a pessoa no banco de dados: $th";
    die();
}

header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");