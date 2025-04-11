<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

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
require_once ROOT . "/classes/Atendido_ocorrencia.php";
require_once ROOT . "/dao/Atendido_ocorrenciaDAO.php";
require_once ROOT . "/controle/Atendido_ocorrenciaControle.php";
require_once ROOT . "/classes/Atendido_ocorrenciaDoc.php";
require_once ROOT . "/classes/Cache.php";


class Atendido_ocorrenciaControle
{
	//Listar despachos
	public function listarTodos()
	{
		extract($_REQUEST);
		$atendido_ocorrenciaDAO = new atendido_ocorrenciaDAO();
		$ocorrencias = $atendido_ocorrenciaDAO->listarTodos();
		session_start();
		$_SESSION['ocorrencia'] = $ocorrencias;
		header('Location: ' . $nextPage);
	}



	// $MemorandoDAO = new MemorandoDAO();
	// $dadosMemorando = $MemorandoDAO->listarTodosId($id_memorando);

	// $ultimoDespacho =  new MemorandoControle;
	// $ultimoDespacho->buscarUltimoDespacho($id_memorando);

	// if(!empty($_SESSION['ultimo_despacho']))
	// {
	// if($dadosMemorando[0]['id_status_memorando'] == 3 AND $_SESSION['ultimo_despacho'][0]['id_destinatarioo']==$_SESSION['id_pessoa'])
	// {
	// 	$memorando = new Memorando('','',$dadosMemorando[0]['id_status_memorando'],'','');
	// 	$memorando->setId_memorando($id_memorando);
	// 	$memorando->setId_status_memorando(2);
	// 	$MemorandoDAO2 = new MemorandoDAO();
	// 	$id_status_memorando = 2;
	// 	$MemorandoDAO2->alterarIdStatusMemorando($memorando);
	// }
	//}


	//Listar Despachos com anexo
	public function listarTodosComAnexo()
	{
		extract($_REQUEST);
		$despachoComAnexoDAO = new atendido_ocorrenciaDAO();
		$despachosComAnexo = $despachoComAnexoDAO->listarTodosComAnexo($id_memorando);
		$_SESSION['despachoComAnexo'] = $despachosComAnexo;
	}

	public function listarAnexo($id_anexo)
	{
		$Atendido_ocorrenciaDAO = new Atendido_ocorrenciaDAO();
		$anexos = $Atendido_ocorrenciaDAO->listarAnexo($id_anexo);
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
		$_SESSION['arq'] = $anexos;
	}

	public function comprimir($anexoParaCompressao)
	{
		$arquivo_zip = gzcompress($anexoParaCompressao);
		return $arquivo_zip;
	}


	//Incluir despachos  
	public function incluir()
	{
		extract($_REQUEST);
		$ocorrencia = $this->verificarDespacho();
		$ocorrenciaDAO = new Atendido_ocorrenciaDAO();
		try {
			$lastId = $ocorrenciaDAO->incluir($ocorrencia);
			$anexoss = $_FILES["anexo"];
			$anexo2 = $_FILES["anexo"]["tmp_name"][0];
			if (isset($anexo2) && !empty($anexo2)) {
				require_once ROOT . "/controle/Atendido_ocorrenciaDocControle.php";
				$arquivo = new Atendido_ocorrenciaDocControle();
				$arquivo->incluir($anexoss, $lastId);
			}
			$msg = "success";
			$sccd = "Ocorrencia enviada com sucesso";
			header("Location: " . WWW . "html/atendido/cadastro_ocorrencia.php?msg=" . $msg . "&sccd=" . $sccd);
		} catch (PDOException $e) {
			$msg = "Não foi possível criar o despacho" . "<br>" . $e->getMessage();
			echo $msg;
		}
	}

	public function verificar()
	{
		extract($_REQUEST);
		// se não estiver definida ou vazia//
		if ((!isset($descricao)) || (empty($descricao))) {
			$msg .= "Descricao do atendido não informado. Por favor, informe a descricao!";
			header('Location: ../html/atendido/cadastro_ocorrencia.php?msg=' . $msg);
		}
		if ((!isset($atendido_idatendido)) || (empty($atendido_idatendido))) {
			$atendido_idatendido = "";
		}
		if ((!isset($id_funcionario)) || (empty($id_funcionario))) {
			$id_funcionario = "";
		}

		if ((!isset($id_tipos_ocorrencia)) || (empty($id_tipos_ocorrencia))) {
			$id_tipos_ocorrencia = "";
		}
		// if((!isset($idatendido_ocorrencias)) || (empty($idatendido_ocorrencias))){
		// 	$idatendido_ocorrencias="";
		// }
		if ((!isset($data)) || (empty($data))) {
			$msg .= "Data da ocorrencia não informada. Por favor, informe a data!";
			header('Location: ../html/atendido/cadastro_ocorrencia.php?msg=' . $msg);
		}
		// if((!isset($nome)) || (empty($nome))){
		// 	$msg .= "Data da ocorrencia não informada. Por favor, informe a data!";
		// 	header('Location: ../html/atendido/cadastro_ocorrencia.php?msg='.$msg); 
		// }

		$ocorrencia = new Ocorrencia($descricao);
		$ocorrencia->setAtendido_idatendido($atendido_idatendido);
		// $ocorrencia->setNome($nome);  
		$ocorrencia->setFuncionario_idfuncionario($id_funcionario);
		$ocorrencia->setId_tipos_ocorrencia($id_tipos_ocorrencia);
		// $ocorrencia->setIdatendido_ocorrencias($idatendido_ocorrencias);
		$ocorrencia->setData($data);
		return $ocorrencia;
	}
	//Verificar despachos
	public function verificarDespacho()
	{
		session_start();
		$cpf_usuario = $_SESSION["usuario"];
		extract($_REQUEST);
		if (!isset($descricao) || (empty($descricao))) {
			$msg = "Ocorrência não informada. Por favor informe um texto.";
		}
		// $pessoa = new Atendido_ocorrenciaDoc();
		// $id_pessoa = $pessoa->obterUsuario($cpf_usuario);
		// $id_pessoa = $id_pessoa['0']['id_pessoa'];
		$ocorrencia = new Ocorrencia($descricao);
		$ocorrencia->setAtendido_idatendido($atendido_idatendido);
		$ocorrencia->setFuncionario_idfuncionario($id_funcionario);
		$ocorrencia->setId_tipos_ocorrencia($id_tipos_ocorrencia);
		$ocorrencia->setData($data);
		// $ocorrencia->setIdatendido_ocorrencias($idatendido_ocorrencias);
		// $ocorrencia->setExtensao($extensao); 
		// $ocorrencia->setNome($nome);  
		return $ocorrencia;
	}

	public function incluirdoc($anexo, $lastId)
	{
		extract($_REQUEST);
		$total = count($anexo['name']);
		$arq = $_FILES['anexo'];

		$arq['name'] =  array_unique($arq['name']);
		$arq['type'] =  array_unique($arq['type']);
		$arq['tmp_name'] =  array_unique($arq['tmp_name']);
		$arq['error'] =  array_unique($arq['error']);
		$arq['size'] =  array_unique($arq['size']);

		$anexo['name'] =  array_unique($anexo['name']);
		$anexo['type'] =  array_unique($anexo['type']);
		$anexo['tmp_name'] =  array_unique($anexo['tmp_name']);
		$anexo['error'] =  array_unique($anexo['error']);
		$anexo['size'] =  array_unique($anexo['size']);

		$novo_total = count($arq['name']);

		for ($i = 0; $i < $novo_total; $i++) {
			/*$zip = new ZipArchive();
			if($zip->open('anexo_zip.zip', ZIPARCHIVE::CREATE) == TRUE)
			{
				$zip->addFile($arq['tmp_name'][$i], $nome.".".$extensao);
			}
			var_dump($zip);
			$caminho=$zip->filename;
			$zip->close();
			$arquivo_zip = file_get_contents($caminho);
			unlink('anexo_zip.zip');*/
			/*$fp = fopen($_FILES['anexo']['tmp_name'][$i], "rb");
			$conteudo = fread($fp, $tamanho_arquivo);
			$conteudo = addslashes($conteudo);
			fclose($fp);*/

			$anexo_tmpName = $arq['tmp_name'];
			$arquivo = file_get_contents($anexo_tmpName[$i]);
			$arquivo1 = $arq['name'][$i];
			$tamanho = strlen($arquivo1);
			$pos = strpos($arquivo1, ".") + 1;
			$extensao = substr($arquivo1, $pos, strlen($arquivo1) + 1);
			$nome = substr($arquivo1, 0, $pos - 1);

			$AnexoControle = new AnexoControle;
			$arquivo_zip = $AnexoControle->comprimir($arquivo);

			try {
				$anexo = new Anexo();
				$anexo->setId_despacho($lastId);
				$anexo->setAnexo($arquivo_zip);
				$anexo->setNome($nome);
				$anexo->setExtensao($extensao);
			} catch (InvalidArgumentException $e) {
				echo 'Erro ao tentar incluir documentação: ' . $e->getMessage();
			}

			try {
				$anexoDAO = new AnexoDAO();
				$anexoDAO->incluir($anexo);
			} catch (PDOException $e) {
				$msg = "Não foi possível criar o despacho" . "<br>" . $e->getMessage();
				echo $msg;
			}
		}
	}
	public function listarUm()
	{
		extract($_REQUEST);
		$cache = new Cache();
		$inf = $cache->read($id);
		if (!$inf) {
			try {
				$atendido_ocorrenciaDAO = new Atendido_ocorrenciaDAO();
				$inf = $atendido_ocorrenciaDAO->listar($id);
				session_start();
				$_SESSION['atendido_ocorrencia'] = $inf;
				$cache->save($id, $inf, '15 seconds');
				header('Location:' . $nextPage);
			} catch (PDOException $e) {
				echo $e->getMessage();
			}
		} else {
			header('Location:' . $nextPage);
		}
	}
}
