<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'memorando' . DIRECTORY_SEPARATOR . 'Anexo.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'Functions' . DIRECTORY_SEPARATOR . 'funcoes.php';

class AnexoDAO
{
	//Fução para listar os memorandos
	public function listarTodos($id_memorando)
	{
		try {
			$Anexos = array();
			$pdo = Conexao::connect();
			$consulta = $pdo->query("SELECT a.extensao, a.nome, d.id_despacho, a.id_anexo FROM anexo a JOIN despacho d ON(a.id_despacho=d.id_despacho) JOIN memorando m ON(d.id_memorando=m.id_memorando) WHERE m.id_memorando=$id_memorando");
			$x = 0;

			while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
				$AnexoDAO = new AnexoDAO;
				$Anexos[$x] = array('extensao' => $linha['extensao'], 'nome' => $linha['nome'], 'id_despacho' => $linha['id_despacho'], 'id_anexo' => $linha['id_anexo']);
				$x++;
			}
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}
		return json_encode($Anexos);
	}

	//Função para listar anexos
	public function listarAnexo($idAnexo)
	{
		$anexo = array();
		$pdo = Conexao::connect();
		$stmt = $pdo->prepare("SELECT anexo FROM anexo WHERE id_anexo=:idAnexo");
		$stmt->bindParam(':idAnexo', $idAnexo, PDO::PARAM_INT);
		$stmt->execute();

		$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($resultados as $key => $value) {
			$decode = gzuncompress($value['anexo']);
			$anexo[$key] = ['anexo' => $decode];
		}

		return $anexo;
	}

	//Função para incluir um anexo
	public function incluir($anexo)
	{
		try {
			$sql = "call insanexo(:id_despacho, :anexo, :extensao, :nome)";
			$sql = str_replace("'", "\'", $sql);
			$pdo = Conexao::connect();
			$id_despacho = $anexo->getId_despacho();
			$arquivo = $anexo->getAnexo();
			$extensao = $anexo->getExtensao();
			$nome = $anexo->getNome();
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id_despacho', $id_despacho);
			$stmt->bindParam(':anexo', $arquivo);
			$stmt->bindParam(':extensao', $extensao);
			$stmt->bindParam(':nome', $nome);
			$stmt->execute();
		} catch (PDOException $e) {
			echo 'Error:' . $e->getMessage();
		}
	}
}
