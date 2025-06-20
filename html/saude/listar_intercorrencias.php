<?php
session_start();
require_once "../../dao/Conexao.php";

header('Content-Type: application/json');

$pdo = Conexao::connect();

$id = $_GET['id_fichamedica'];

$stmt = $pdo->prepare("
  SELECT descricao, data
  FROM aviso
  JOIN saude_fichamedica sf on ( sf.id_fichamedica = :id_fichamedica)
  WHERE id_pessoa_atendida = sf.id_pessoa
  ORDER BY data DESC
");
$stmt->bindValue(':id_fichamedica', $id, PDO::PARAM_INT);
$stmt->execute();
$intercorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($intercorrencias as $key => $value){
  $data = new DateTime($value['data']);
  $intercorrencias[$key]['data'] = $data->format('d/m/Y H:i:s'); 
}

echo json_encode($intercorrencias);