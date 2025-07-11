<?php
require_once '../../dao/Conexao.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Sanitização das variáveis
$enfermidadeCid = filter_input(INPUT_POST, 'cid', FILTER_SANITIZE_SPECIAL_CHARS);
$enfermidadeNome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$enfermidadeCid || !$enfermidadeNome) {
	http_response_code(400);
	echo json_encode (['erro' => 'As informações referentes ao CID e ao nome da enfermidade devem ser preenchidas para que o cadastro ocorra']);
	exit();
}

//Validação do CID
$regexCid = '/^[A-TV-Z][0-9]{2}(\.[0-9A-Z]{1,4})?$/';

if (!preg_match($regexCid, $enfermidadeCid)) {
	http_response_code(400);
	echo json_encode(['erro' => 'O CID informado não está dentro do padrão CID-10 da OMS']);
	exit();
}

try {
	$pdo = Conexao::connect();
	$sql = "INSERT INTO saude_tabelacid(CID, descricao) VALUES (:enfermidadeCid, :enfermidadeNome)";

	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':enfermidadeCid', $enfermidadeCid);
	$stmt->bindParam('enfermidadeNome', $enfermidadeNome);
	$stmt->execute();
} catch (PDOException $e) {
	echo json_encode(['erro' => 'Erro ao cadastrar enfermidade: ' . $e->getMessage()]);
}
