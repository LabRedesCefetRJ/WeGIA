<?php
session_start();
require_once '../../dao/Conexao.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
header('Content-Type: application/json');

try {
	if (!isset($_SESSION['id_pessoa'])) {
		throw new RuntimeException('Sua sessão expirou. Atualize a página e tente novamente.', 401);
	}

	// Verifica Permissão do Usuário
	require_once '../permissao/permissao.php';
	permissao($_SESSION['id_pessoa'], 53, 7);

	$alergiaNome = trim((string)filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING));
	if ($alergiaNome === '') {
		throw new InvalidArgumentException('O nome da alergia precisa ser informado.', 400);
	}

	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if ($mysqli->connect_errno) {
		throw new RuntimeException('Não foi possível adicionar a alergia.', 500);
	}

	$ultima_alergia = $mysqli->query("SELECT * FROM saude_tabelacid WHERE CID LIKE 'T78.4.%' ORDER BY CAST(SUBSTRING_INDEX(CID, '.', -1) AS UNSIGNED) DESC LIMIT 1");
	if ($ultima_alergia === false) {
		throw new RuntimeException('Não foi possível adicionar a alergia.', 500);
	}

	if ($ultima_alergia->num_rows > 0) {
		$row = $ultima_alergia->fetch_assoc();
		$divided_row = explode(".", $row["CID"]);
		$row_number = end($divided_row);
		$row_number += 1;
		$alergia_CID = "T78.4." . $row_number;
	} else {
		$alergia_CID = "T78.4.0";
	}

	$pdo = Conexao::connect();
	$sql = "INSERT into saude_tabelacid(CID, descricao) values(:alergiaCid, :alergiaNome)";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':alergiaCid', $alergia_CID);
	$stmt->bindParam(':alergiaNome', $alergiaNome);
	$stmt->execute();
	echo json_encode([
		'status' => 'sucesso',
		'mensagem' => 'Alergia adicionada com sucesso.',
		'alergia' => [
			'cid' => $alergia_CID,
			'nome' => $alergiaNome
		]
	]);
	exit();
} catch (Throwable $e) {
	Util::tratarException($e);
}
