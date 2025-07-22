<?php
//Aplicar correções sugeridas pela issue #250
if(session_status() === PHP_SESSION_NONE){
	session_start();
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR .'permissao.php';

if(!isset($_SESSION['usuario'])){
	header('Location: ../../index.php');
	exit(401);
}

permissao($_SESSION['id_pessoa'], 54, 7);

require_once '../../dao/Conexao.php';

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
	error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
	http_response_code($e->getCode());
	echo json_encode(['erro' => 'Erro no servidor ao adicionar enfermidade.']);
}
