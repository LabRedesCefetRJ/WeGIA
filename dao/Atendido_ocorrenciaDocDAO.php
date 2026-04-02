<?php

$config_path = "config.php";
if(file_exists($config_path)){
    require_once($config_path);
}else{
    while(true){
        $config_path = "../" . $config_path;
        if(file_exists($config_path)) break;
    }
    require_once($config_path);
}

require_once ROOT."/classes/Atendido_ocorrenciaDoc.php";
require_once ROOT."/classes/Util.php";
require_once ROOT."/dao/Conexao.php";
require_once ROOT."/Functions/funcoes.php";

class Atendido_ocorrenciaDocDAO
//SELECT aod.arquivo_extensao, aod.arquivo_nome, aod.idatendido_ocorrencia_doc, ao.idatendido_ocorrencias FROM atendido_ocorrencia_doc aod JOIN atendido_ocorrencia ao ON (aod.idatendido_ocorrencia_doc=ao.idatendido_ocorrencias) WHERE aod.idatendido_ocorrencia_doc = " .$idatendido_ocorrencia_doc.";");
{
	public function listarTodos($idatendido_ocorrencias)
	 {
	 	try{
			$Anexos = array();
			$pdo = Conexao::connect();
			$consulta = $pdo->query("SELECT arquivo_nome, arquivo_extensao FROM `atendido_ocorrencia_doc` WHERE atentido_ocorrencia_idatentido_ocorrencias = $idatendido_ocorrencias");
			$x = 0;
			
			while($linha = $consulta->fetch(PDO::FETCH_ASSOC))
				{
					$AnexoDAO = new Atendido_ocorrenciaDocDAO;
					$Anexos[$x] = array('arquivo_extensao'=>$linha['arquivo_extensao'], 'arquivo_nome'=>$linha['arquivo_nome']);
					$x++;
				}
	 	}
	 	catch(PDOException $e)
	 	{
	 		echo 'Error:' . $e->getMessage();
	 	}
	 	return json_encode($Anexos);
	 }

	// public function listarAnexo($id_anexo)
	// {
	// 	try
	// 	{	
	// 		$Anexo = array();
	// 		$pdo = Conexao::connect();
	// 		$consulta = $pdo->query("SELECT anexo FROM anexo WHERE id_anexo=$id_anexo");
	// 		$x = 0;

	// 		while($linha = $consulta->fetch(PDO::FETCH_ASSOC))
	// 		{
	// 			$AnexoDAO = new AnexoDAO;
	// 			$decode = gzuncompress($linha['anexo']);
	// 			$Anexo[$x] = array('anexo'=>$decode);
	// 			$x++;
	// 		}
	// 	}
	// 	catch(PDOException $e)
	// 	{
	// 		echo 'Error:' . $e->getMessage;
	// 	}
	// 	return $Anexo;
	// }

	public function incluir($anexo)
	{
		try
		{
			$pdo = Conexao::connect();
			$consulta = $pdo->query("SELECT max(idatendido_ocorrencias) from atendido_ocorrencia;")->fetch(PDO::FETCH_ASSOC);
			
			//$sql = "call insarquivo_ocorrencia(:atentido_ocorrencia_idatentido_ocorrencias, :arquivo_nome, :arquivo_extensao, :arquivo)";
			
			$sql = "INSERT INTO atendido_ocorrencia_doc (atentido_ocorrencia_idatentido_ocorrencias, data, arquivo_nome, arquivo_extensao, arquivo) VALUES (:atentido_ocorrencia_idatentido_ocorrencias, :data, :arquivo_nome, :arquivo_extensao, :arquivo)";

			$id = $consulta['max(idatendido_ocorrencias)'];
			$arquivo = $anexo->getAnexo();
			Util::definirFusoHorario();
			$data = date('Y-m-d H:i:s');
			$extensao = $anexo->getExtensao();
			$nome = $anexo->getNome();
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':atentido_ocorrencia_idatentido_ocorrencias', $id);
			$stmt->bindParam(':arquivo', $arquivo);
			$stmt->bindParam(':arquivo_extensao', $extensao);
			$stmt->bindParam(':arquivo_nome', $nome);
			$stmt->bindParam(':data', $data);
			$stmt->execute();
		}
		catch(PDOException $e)
		{
			echo 'Error:' . $e->getMessage();
		}
	}
}
?>
