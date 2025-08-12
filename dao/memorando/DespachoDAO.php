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
require_once ROOT . "/classes/memorando/Despacho.php";
require_once ROOT . "/Functions/funcoes.php";
require_once ROOT . "/dao/memorando/MemorandoDAO.php";

class DespachoDAO
{
	//Função para listar os memorandos
	public function listarTodos($id_memorando)
	{
		try {
			$Despachos = array();
			$pdo = Conexao::connect();
			$consulta = $pdo->query("SELECT p.nome, d.texto, d.id_remetente, d.data, d.id_despacho FROM despacho d JOIN pessoa p ON d.id_remetente=p.id_pessoa WHERE d.id_memorando='$id_memorando' ORDER BY d.data");
			$consulta1 = $pdo->query("SELECT p.nome FROM despacho d JOIN pessoa p ON d.id_destinatario=p.id_pessoa WHERE id_memorando='$id_memorando'ORDER BY d.data");
			$x = 0;

			while ($linha = $consulta->fetch(PDO::FETCH_ASSOC) and $linha1 = $consulta1->fetch(PDO::FETCH_ASSOC)) {
				$Despachos[$x] = array('remetente' => $linha['nome'], 'data' => $linha['data'], 'texto' => $linha['texto'], 'id' => $linha['id_despacho'], 'destinatario' => $linha1['nome']);
				$x++;
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}

		return json_encode($Despachos);
	}

	//Lista todos os memorandos com anexo
	public function listarTodosComAnexo($id_memorando)
	{
		try {
			$Despachos = array();
			$pdo = Conexao::connect();
			$consulta = $pdo->query("SELECT DISTINCT d.id_despacho FROM despacho d JOIN anexo a ON(d.id_despacho=a.id_despacho) JOIN memorando m ON(d.id_memorando=m.id_memorando) WHERE d.id_memorando=$id_memorando");
			$x = 0;

			while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
				$Despachos[$x] = array('id_despacho' => $linha['id_despacho']);
				$x++;
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}

		return json_encode($Despachos);
	}

	//Função para incluir um despacho
	public function incluir(Despacho $despacho)
	{
		try {
			$pdo = Conexao::connect();
			$sql = "call insdespacho(:id_memorando, :id_remetente, :id_destinatario, :texto, :data)";
			$sql = str_replace("'", "\'", $sql);
			$id_memorando = $despacho->getId_memorando();
			$id_remetente = $despacho->getId_remetente();
			$id_destinatario = $despacho->getId_destinatario();
			$texto = $despacho->getTexto();
			$data = $despacho->getData();
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id_memorando', $id_memorando);
			$stmt->bindParam(':id_remetente', $id_remetente);
			$stmt->bindParam(':id_destinatario', $id_destinatario);
			$stmt->bindParam(':texto', $texto);
			$stmt->bindParam(':data', $data);
			$stmt->execute();

			$id = array();
			$consulta = $pdo->query("SELECT MAX(id_despacho) FROM despacho");
			$x = 0;

			while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
				$id[$x] = array('id' => $linha['MAX(id_despacho)']);
				$x++;
			}

			$lastId = $id[0]['id'];

			$MemorandoDAO = new MemorandoDAO();
			$dadosMemorando = $MemorandoDAO->listarTodosId($id_memorando);

			if ($id_remetente == $id_destinatario && $dadosMemorando[0]['id_status_memorando'] == 7) {
				$memorando = new Memorando('', '', $dadosMemorando[0]['id_status_memorando'], '', '');
				$memorando->setId_memorando($id_memorando);
				$memorando->setId_status_memorando(2);
				$MemorandoDAO2 = new MemorandoDAO();
				//$id_status_memorando = 2;
				$MemorandoDAO2->alterarIdStatusMemorando($memorando);
			}

			if ($id_remetente != $id_destinatario) {
				$memorando = new Memorando('', '', $dadosMemorando[0]['id_status_memorando'], '', '');
				$memorando->setId_memorando($id_memorando);
				$memorando->setId_status_memorando(3);
				$MemorandoDAO2 = new MemorandoDAO();
				//$id_status_memorando = 3;
				$MemorandoDAO2->alterarIdStatusMemorando($memorando);
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}
		return $lastId;
	}

	//Função para pegar o id do despacho pelo id do memorando
	public function getPorId(int $id){
		$sql = 'SELECT * FROM despacho WHERE id_memorando=:idMemorando';
		$pdo = Conexao::connect();

		$stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idMemorando', $id);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado;
	}
}
