<?php
//Requisições necessárias
require_once 'Conexao.php';
require_once '../html/permissao/permissao.php';

//Verifica se um usuário está logado e possui as permissões necessárias
session_start();
permissao($_SESSION['id_pessoa'], 11, 3);

//Sanitiza a entrada.
$ocorrencia = trim(filter_input(INPUT_POST, 'atendido_ocorrencia_tipos', FILTER_SANITIZE_STRING));

if(!$ocorrencia || empty($ocorrencia)){
	http_response_code(400);
	exit('Erro, a descrição de uma nova ocorrencia não pode ser vazia.');
}

//Executa a consulta no banco de dados da aplicação
try {
	$sql = "INSERT INTO atendido_ocorrencia_tipos(descricao) VALUES (:atendido_ocorrencia_tipos)";
	$pdo = Conexao::connect();
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':atendido_ocorrencia_tipos', $ocorrencia);
	$stmt->execute();
} catch (PDOException $e) {
	http_response_code(500);
	echo 'Erro ao inserir uma nova ocorrência. Entre em contato com o suporte técnico para mais informações. ';
}