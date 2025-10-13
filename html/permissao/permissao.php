<?php
define("DEBUG", false);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';

/**
 * Verifica se a pessoa cujo o id foi passado como parâmetro possui a permissão necessária para usar um recurso específico do sistema.
 */
function permissao($id_pessoa, $id_recurso, $id_acao = 1): void
{
	try {
		$pdo = Conexao::connect();
		$sql = "SELECT * FROM funcionario WHERE id_pessoa = :ID_PESSOA";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':ID_PESSOA', $id_pessoa, PDO::PARAM_INT);
		$stmt->execute();
		$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

		if (DEBUG) {
			echo json_encode($resultado);
			die();
		}

		if (is_null($resultado))
			throw new LogicException('', 403);

		$id_cargo = $resultado['id_cargo'];

		$sql = "SELECT * FROM permissao WHERE id_cargo = :id_cargo AND id_recurso = :id_recurso";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':id_cargo', $id_cargo, PDO::PARAM_INT);
		$stmt->bindValue(':id_recurso', $id_recurso, PDO::PARAM_INT);
		$stmt->execute();
		$permissao = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$permissao)
			throw new LogicException('', 403);

		if ($permissao['id_acao'] < $id_acao)
			throw new LogicException('', 403);
	} catch (Exception $e) {
		//Armazena exceção em um arquivo de log
		error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
		http_response_code($e->getCode());

		switch ($e) {
			case $e instanceof LogicException:
				header("Location: " . WWW . "html/home.php?msg_c=" . urlencode("Você não tem as permissões necessárias para essa página." . (DEBUG ? " Não há permissão!" : "")));
				break;

			case $e instanceof PDOException:
				echo json_encode(['erro' => 'Erro no servidor ao manipular o banco de dados']);
				break;

			default:
				echo json_encode(['erro' => $e->getMessage()]);
		}
	}
}
