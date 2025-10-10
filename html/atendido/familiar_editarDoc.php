<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);
extract($_REQUEST);

session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);
require_once '../../dao/Conexao.php';

$id = $_GET['id_pessoa'];
$rg = trim($_POST['rg']);
$orgao_emissor = trim($_POST['orgao_emissor']);
$cpf = trim($_POST['cpf']);
$data_expedicao = trim($_POST['data_expedicao']);

/*if(count($data_expedicao) <10){
        $data_expedicao= null;
    }*/

define("ALTERAR_DOC", "UPDATE pessoa SET orgao_emissor=:orgao_emissor, data_expedicao=:data_expedicao, registro_geral=:registro_geral, cpf=:cpf where id_pessoa = :id");

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    exit('Erro, o id da pessoa fornecido não é válido');
}

if (!$cpf || empty($cpf)) { //Fazer posteriormente uma validação do formato do CPF quando essa funcionalidade for implementada na classe Util.php
    http_response_code();
    exit('Erro, o CPF fornecido está vazio.');
}

if (!$rg || !$orgao_emissor || !$data_expedicao || empty($rg) || empty($orgao_emissor) || empty($data_expedicao)) { //Fazer posteriormente uma validação do formato do RG quando essa funcionalidade for implementada na classe Util.php
    http_response_code(400);
    exit('Erro, estão faltando informações necessárias para realizar a alteração do RG.');
}

if ($data_expedicao && $id) {
    try {
        $pdo = Conexao::connect();
        
        // Buscar data de nascimento atual da pessoa
        $sql_nascimento = "SELECT data_nascimento FROM pessoa WHERE id_pessoa = :id_pessoa";
        $stmt_nascimento = $pdo->prepare($sql_nascimento);
        $stmt_nascimento->bindParam(':id_pessoa', $id);
        $stmt_nascimento->execute();
        $pessoa = $stmt_nascimento->fetch(PDO::FETCH_ASSOC);
        
        // Só valida se existe data de nascimento no banco
        if ($pessoa && $pessoa['data_nascimento']) {
            $data_nascimento = new DateTime($pessoa['data_nascimento']);
            $data_expedicao_obj = new DateTime($data_expedicao);
            
            if ($data_expedicao_obj <= $data_nascimento) {
                //$_SESSION['msg'] = "Erro: A data de expedição do documento não pode ser anterior à data de nascimento!";
                //$_SESSION['tipo'] = "error";
                //header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
                //exit;
                die( json_encode( ['A data de expedição do documento não pode ser anterior ou igual à data de nascimento!'] ) );
            }
        }
        // Se não existe data de nascimento no banco, permite a alteração sem validação
    } catch (PDOException $e) {
        die( json_encode( ["Erro ao consultar o banco de dados para verificação das datas de nascimento e expedição.{$e->getMessage()}"] ) );   
    }
}


try {
    $pdo = Conexao::connect();
    $pessoa = $pdo->prepare(ALTERAR_DOC);
    $pessoa->bindValue(":id", $id);
    $pessoa->bindValue(":orgao_emissor", $orgao_emissor);
    $pessoa->bindValue(":data_expedicao", $data_expedicao);
    $pessoa->bindValue(":cpf", $cpf);
    $pessoa->bindValue(":registro_geral", $rg);
    $pessoa->execute();
} catch (PDOException $th) {
    echo "Houve um erro ao inserir a pessoa no banco de dados: $th";
    die();
}

$idatendido_familiares = $_GET['idatendido_familiares'];
header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");