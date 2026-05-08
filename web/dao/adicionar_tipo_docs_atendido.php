<?php
//refactor to MVC
if (session_status() === PHP_SESSION_NONE)
	session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes'  . DIRECTORY_SEPARATOR . 'Util.php';

try {
	if (!isset($_SESSION['usuario']))
		throw new Exception('O usuário não está logado no sistema.', 401);

	$idPessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

	if (!$idPessoa || $idPessoa < 1)
		throw new InvalidArgumentException('O id da pessoa do usuário não pode ser menor que 1.', 412);

	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'MiddlewareDAO.php';

	$middlewareDao = new MiddlewareDAO();

	if (!$middlewareDao->verificarPermissao($idPessoa, 'recursos', ['recursos' => [1, 12]]))
		throw new Exception('O usuário não possui permissão para acessar essa funcionalidade.', 401);

	$descricao = trim(filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS));

	if (!$descricao || empty($descricao)) 
		throw new InvalidArgumentException('A descrição de um novo tipo de documentação não pode ser vazia.', 412);

	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';

	$sql = "INSERT INTO atendido_docs_atendidos(descricao) VALUES (:descricao)";
	$pdo = Conexao::connect();
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':descricao', $descricao);
	$stmt->execute();
} catch (Exception $e) {
	Util::tratarException($e);
}
