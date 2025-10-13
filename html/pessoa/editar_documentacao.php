<?php

session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 1, 3);

require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

extract($_POST);


if ($data_expedicao && $id_pessoa) {
    // Buscar data de nascimento atual da pessoa
    $sql_nascimento = "SELECT data_nascimento FROM pessoa WHERE id_pessoa = :id_pessoa";
    $stmt_nascimento = $pdo->prepare($sql_nascimento);
    $stmt_nascimento->bindParam(':id_pessoa', $id_pessoa);
    $stmt_nascimento->execute();
    $pessoa = $stmt_nascimento->fetch(PDO::FETCH_ASSOC);
    
    if ($pessoa && $pessoa['data_nascimento']) {
        $data_nascimento = new DateTime($pessoa['data_nascimento']);
        $data_expedicao_obj = new DateTime($data_expedicao);
        
        if ($data_expedicao_obj <= $data_nascimento) {
            http_response_code(400);
            die (json_encode( [ 'Erro: A data de expedição do documento não pode ser anterior ou igual à data de nascimento!' ] ) );
        }
    }
}

$rg = ($rg ? "'$rg'" : "NULL");
$orgao_emissor = ($orgao_emissor ? "'$orgao_emissor'" : "NULL");
$data_expedicao = ($data_expedicao ? "'$data_expedicao'" : "NULL");
$cpf = ($cpf ? "'$cpf'" : "NULL");

$sql = "UPDATE pessoa SET registro_geral = :rg, orgao_emissor = :orgao_emissor, data_expedicao = :data_expedicao, cpf = :cpf WHERE id_pessoa = :id_pessoa";

$stmt = $pdo->prepare($sql);

$stmt->bindParam(':rg', $rg);
$stmt->bindParam(':orgao_emissor', $orgao_emissor);
$stmt->bindParam(':data_expedicao', $data_expedicao);
$stmt->bindParam(':cpf', $cpf);
$stmt->bindParam(':id_pessoa', $id_pessoa);

$stmt->execute();

$_GET['sql'] = $sql;
echo(json_encode($_GET));