<?php
session_start();

//verificar permissão
if(!isset($_SESSION['id_pessoa'])){
    http_response_code(403);
    exit('Usuário não autenticado');
}

require_once '../../html/permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 6, 5);

require_once "../Conexao.php";
$pdo = Conexao::connect();

$tipo_exame = trim(filter_input(INPUT_POST, 'tipo_exame', FILTER_SANITIZE_STRING));

$sql = "INSERT INTO pet_tipo_exame(descricao_exame) values(:tipoExame)";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':tipoExame', $tipo_exame);
$stmt->execute();

$pd = $pdo->query("SELECT * FROM pet_tipo_exame");
$p = $pd->fetchAll();
$array = array();
foreach ($p as $valor) {
    $array[] = array('id_tipo_exame'=>$valor['id_tipo_exame'], 'tipo_exame' => $valor['descricao_exame']);
}

echo json_encode($p);
?>
