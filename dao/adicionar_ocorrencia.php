<?php
//Requisições necessárias
require_once 'Conexao.php';
require_once '../html/permissao/permissao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

//Verifica se um usuário está logado e possui as permissões necessárias
session_start();
permissao($_SESSION['id_pessoa'], 11, 3);

//Sanitiza a entrada.
try {
	$ocorrencia = trim(filter_input(INPUT_POST, 'atendido_ocorrencia_tipos', FILTER_SANITIZE_SPECIAL_CHARS));

	if (!$ocorrencia || empty($ocorrencia))
		throw new InvalidArgumentException('A descrição de uma nova ocorrencia não pode ser vazia.', 412);

	//Executa a consulta no banco de dados da aplicação

	$sql = "INSERT INTO atendido_ocorrencia_tipos(descricao) VALUES (:atendido_ocorrencia_tipos)";
	$pdo = Conexao::connect();
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':atendido_ocorrencia_tipos', $ocorrencia);
	$stmt->execute();

	if($stmt->rowCount() > 0)
		echo json_encode(['sucesso' => $pdo->lastInsertId('atendido_ocorrencia_tipos')]);
} catch (Exception $e) {
	// Erro de duplicidade (Duplicate entry)
	if ($e->getCode() == 23000 && isset($e->errorInfo[1]) && ($e instanceof PDOException && $e->errorInfo[1] == 1062)) {

		http_response_code(412); // Bad Request
		echo json_encode([
			"erro" => "Já existe uma ocorrência cadastrada com essa descrição."
		]);
		exit;
	}
	Util::tratarException($e);
}
