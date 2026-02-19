<?php
if (session_status() === PHP_SESSION_NONE)
	session_start();

date_default_timezone_set("America/Sao_Paulo");

require_once '../dao/Conexao.php';
require_once '../Functions/funcoes.php';
require_once './seguranca/sessionStart.php';
require_once '../classes/Util.php';

try {
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		extract($_REQUEST);

		$pdo = Conexao::connect();
		$stmt = $pdo->prepare('SELECT id_pessoa, cpf, senha, nome, adm_configurado, nivel_acesso FROM pessoa WHERE cpf=:cpf');
		$stmt->bindValue(':cpf', $cpf);

		$stmt->execute();

		$pwd = hash('sha256', $pwd);
		$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($usuario["cpf"] === $cpf && $usuario["senha"] === $pwd) {
			$c = "true";
			$id_pessoa = $usuario["id_pessoa"];
			$nome = $usuario["nome"];
		}

		if ($c == "true") {
			if (isset($_SESSION['usuario'])) {
				session_destroy();
				session_start();
				$_SESSION['usuario'] = $cpf;
				$_SESSION['id_pessoa'] = $id_pessoa;
				$_SESSION['time'] = time() + (30);

				header("Location: ../html/home.php");
				exit();
			} else {
				$_SESSION['usuario'] = $cpf;
				$_SESSION['id_pessoa'] = $id_pessoa;

				if ($linha['adm_configurado'] == 0 && $linha['cpf'] == "admin" && $linha['nivel_acesso'] == 2) {
					header("Location: ../html/alterar_senha.php");
					exit();
				} else {
					header("Location: ../html/home.php");
					exit();
				}
			}
		} else {
			header("Location: ../index.php?erro=erro");
			exit();
		}
	}
} catch (Exception $e) {
	Util::tratarException($e);
}
