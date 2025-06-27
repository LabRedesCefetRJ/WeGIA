<?php

$config_path = "config.php";
if (file_exists($config_path)) {
	require_once($config_path);
} else {
	while (true) {
		$config_path = "../" . $config_path;
		if (file_exists($config_path)) break;
	}
	require_once($config_path);
}

require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/memorando/Memorando.php";
require_once ROOT . "/dao/memorando/UsuarioDAO.php";

class MemorandoDAO
{
	/**
	 * Lista os memorandos ativos (Caixa de Entrada) do usuário logado no momento. 
	 */
	public function listarTodos()
	{
		try {
			$Memorandos = array();
			$pdo = Conexao::connect();
			$cpf_usuario = $_SESSION["usuario"];
			$usuario = new UsuarioDAO;
			$id_usuario = $usuario->obterUsuario($cpf_usuario)['0']['id_pessoa'];

			$sql = "SELECT d.id_memorando, d.id_destinatario, m.titulo, d.data, d.id_remetente, m.id_status_memorando, m.id_pessoa, d.id_destinatario FROM despacho d INNER JOIN memorando m ON(d.id_memorando=m.id_memorando) WHERE (d.id_despacho IN (SELECT MAX(id_despacho) FROM despacho GROUP BY id_memorando)) AND m.id_status_memorando!='6' AND d.id_destinatario=:idUsuario ORDER BY m.data DESC";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':idUsuario', $id_usuario);
			$stmt->execute();
			$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ($resultado) {
				$Memorandos = $resultado;
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}

		return json_encode($Memorandos);
	}

	/**
	 * Recebe como parâmetro o id de um memorando e retorna as informações de um memorando guardado no banco de dados
	 */
	public function listarTodosId($id_memorando)
	{
		try {
			$Memorando = array();
			$pdo = Conexao::connect();
			$cpf_usuario = $_SESSION["usuario"];
			$usuario = new UsuarioDAO;
			$id_usuario = $usuario->obterUsuario($cpf_usuario);
			$id_usuario = $id_usuario['0']['id_pessoa'];

			$sql = "SELECT titulo, id_status_memorando, id_pessoa, id_memorando FROM memorando WHERE id_memorando=:idMemorando";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':idMemorando', $id_memorando);
			$stmt->execute();
			$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ($resultado) {
				$Memorando = $resultado;
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}

		return $Memorando;
	}


	/**
	 * Lista todos os memorandos inativos do usuário daquela sessão em específico.
	 */
	public function listarTodosInativos()
	{
		try {
			$Memorandos = array();
			$cpf_usuario = $_SESSION["usuario"];
			$usuario = new UsuarioDAO;
			$id_usuario = $usuario->obterUsuario($cpf_usuario)['0']['id_pessoa'];
			$pdo = Conexao::connect();

			$sql = "SELECT DISTINCT m.id_memorando, m.titulo, m.data, p.nome, m.id_status_memorando FROM memorando m JOIN despacho d ON(d.id_memorando=m.id_memorando) JOIN pessoa p ON(m.id_pessoa=p.id_pessoa) WHERE d.id_remetente=:idUsuario";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':idUsuario', $id_usuario);
			$stmt->execute();
			$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ($resultado) {
				$Memorandos = $resultado;

				foreach ($Memorandos as $key => $value) {
					$Memorandos[$key]['titulo'] = htmlspecialchars($value['titulo']);
				}
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}

		return json_encode($Memorandos);
	}

	/**
	 * Retorna o id de todos os memorandos inativos do usuário logado na sessão
	 */
	public function listarIdTodosInativos()
	{
		try {
			$Memorandos = array();
			$cpf_usuario = $_SESSION["usuario"];
			$usuario = new UsuarioDAO;
			$id_usuario = $usuario->obterUsuario($cpf_usuario)['0']['id_pessoa'];
			$pdo = Conexao::connect();

			$sql = "SELECT DISTINCT m.id_memorando FROM memorando m JOIN despacho d ON(d.id_memorando=m.id_memorando) JOIN pessoa p ON(m.id_pessoa=p.id_pessoa) WHERE (d.id_destinatario=:idUsuario1 OR d.id_remetente=:idUsuario2)";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':idUsuario1', $id_usuario);
			$stmt->bindParam(':idUsuario2', $id_usuario);
			$stmt->execute();
			$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ($resultado) {
				foreach ($resultado as $linha) {
					$Memorandos[] = $linha['id_memorando'];
				}
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}

		return $Memorandos;
	}

	//Criar memorando
	public function incluir($memorando)
	{
		try {
			$sql = "CALL insmemorando(:id_pessoa, :id_status_memorando, :titulo, :data)";
			$sql = str_replace("'", "\'", $sql);
			$pdo = Conexao::connect();
			$id_pessoa = $memorando->getId_pessoa();
			$id_status_memorando = $memorando->getId_status_memorando();
			$titulo = $memorando->getTitulo();
			$data = $memorando->getData();
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id_pessoa', $id_pessoa);
			$stmt->bindParam(':id_status_memorando', $id_status_memorando);
			$stmt->bindParam(':titulo', $titulo);
			$stmt->bindParam(':data', $data);
			$stmt->execute();

			$consulta = $pdo->query("SELECT MAX(id_memorando) FROM memorando");
			$x = 0;

			while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
				$id[$x] = array('id' => $linha['MAX(id_memorando)']);
				$x++;
			}
			$lastId = $id[0]['id'];
		} catch (PDOException $e) {
			echo 'Error: <b>  na tabela memorando = ' . $sql . '</b> <br /><br />' . $e->getMessage();
		}

		return $lastId;
	}

	//Alterar status do memorando
	public function alterarIdStatusMemorando($memorando)
	{
		try {
			$sql = "update memorando set id_status_memorando=:id_status_memorando where id_memorando=:id_memorando";
			$sql = str_replace("'", "\'", $sql);
			$pdo = Conexao::connect();
			$stmt = $pdo->prepare($sql);
			$id_status_memorando = $memorando->getId_status_memorando();
			$id_memorando = $memorando->getId_memorando();
			$stmt->bindParam(':id_status_memorando', $id_status_memorando);
			$stmt->bindParam(':id_memorando', $id_memorando);
			$stmt->execute();
		} catch (PDOException $e) {
			echo 'Error: <b>  na tabela memorando = ' . $sql . '</b> <br /><br />' . $e->getMessage();
		}
	}

	/**
	 * Busca o último despacho associado ao id do memorando informado pelo parâmetro
	 */
	public function buscarUltimoDespacho($id_memorando)
	{
		$Despacho = array();
		try {
			$pdo = Conexao::connect();

			$sql = "SELECT id_destinatario, id_despacho, id_remetente FROM despacho WHERE id_despacho IN (SELECT MAX(id_despacho) FROM despacho WHERE id_memorando=:idMemorando)";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':idMemorando', $id_memorando);
			$stmt->execute();

			$x = 0;
			while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$Despacho[$x] = array('id_destinatarioo' => $linha['id_destinatario'], 'id_despacho' => $linha['id_despacho'], 'id_remetente' => $linha['id_remetente']);
				$x++;
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}

		return $Despacho;
	}
	/**
	 * Busca o id do status de um memorando cujo o seu id foi passado como parâmetro para o método.
	 */
	public function buscarIdStatusMemorando($id_memorando)
	{
		try {
			$id = array();
			$pdo = Conexao::connect();

			$sql = "SELECT id_status_memorando FROM memorando WHERE id_memorando=:idMemorando";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':idMemorando', $id_memorando);
			$stmt->execute();
			$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($resultado) {
				$id[] = $resultado['id_status_memorando'];
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}
		return $id;
	}

	/**
	 * Verifica se um memorando com id igual ao informado como parâmetro existe na base de dados.
	 */
	public function issetMemorando($id_memorando)
	{
		try {
			$pdo = Conexao::connect();

			$sql = "SELECT id_memorando FROM memorando WHERE id_memorando=:idMemorando";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':idMemorando', $id_memorando);
			$stmt->execute();

			if (null == $stmt->fetch(PDO::FETCH_ASSOC)) {
				$retorno = 1;
			} else {
				$retorno = 0;
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}
		return $retorno;
	}
}
