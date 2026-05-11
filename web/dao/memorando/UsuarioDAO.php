<?php

class UsuarioDAO
{
	public function obterUsuario($usuario)
	{
		$pdo = Conexao::connect();
		$consulta = $pdo->prepare("SELECT id_pessoa FROM pessoa WHERE cpf = :cpf");
		$consulta->bindValue(':cpf', $usuario);
		$consulta->execute();

		if($consulta->rowCount() === 0) {
			throw new InvalidArgumentException('Usuário não encontrado.', 404);
		}

		return $consulta->fetch(PDO::FETCH_ASSOC);
	}
}
