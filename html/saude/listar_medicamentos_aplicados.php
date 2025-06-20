<?php
session_start();
require_once "../../dao/Conexao.php";

header('Content-Type: application/json');

$pdo = Conexao::connect();

$id = $_GET['id_fichamedica'];

$stmt = $pdo->prepare("
  SELECT medicamento, aplicacao, p.nome as nomeFuncionario 
  FROM saude_medicacao sm 
  JOIN saude_medicamento_administracao sa ON (sm.id_medicacao = sa.saude_medicacao_id_medicacao) 
  JOIN saude_atendimento saa ON (saa.id_atendimento = sm.id_atendimento)
  JOIN funcionario f ON (sa.funcionario_id_funcionario = f.id_funcionario) 
  JOIN pessoa p ON (p.id_pessoa = f.id_pessoa) 
  WHERE saa.id_fichamedica = :id 
  ORDER BY aplicacao DESC
");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$medaplicadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formatando data
foreach($medaplicadas as $key => $value){
  $data = new DateTime($value['aplicacao']);
  $medaplicadas[$key]['aplicacao'] = $data->format('d/m/Y H:i:s'); 
}

echo json_encode($medaplicadas);