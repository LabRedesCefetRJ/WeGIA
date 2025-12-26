<?php
if(session_status() === PHP_SESSION_NONE)
	session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';

require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Atendido_ocorrencia.php";
require_once ROOT . "/dao/Atendido_ocorrenciaDAO.php";
require_once ROOT . "/classes/Atendido_ocorrenciaDoc.php";
require_once ROOT . "/classes/Cache.php";
require_once ROOT . "/classes/Util.php";


class Atendido_ocorrenciaControle
{
	//Listar despachos
	public function listarTodos()
	{
		try {
			$atendido_ocorrenciaDAO = new atendido_ocorrenciaDAO();
			$ocorrencias = $atendido_ocorrenciaDAO->listarTodos();
			$_SESSION['ocorrencia'] = $ocorrencias;
		} catch (Exception $e) {
			Util::tratarException($e);
		}
	}

	//Listar Despachos com anexo
	public function listarTodosComAnexo()
	{
		extract($_REQUEST);
		$despachoComAnexoDAO = new atendido_ocorrenciaDAO();
		$despachosComAnexo = $despachoComAnexoDAO->listarTodosComAnexo($id_memorando);
		$_SESSION['despachoComAnexo'] = $despachosComAnexo;
	}

	public function listarAnexo($id_ocorrencia)
	{
		$Atendido_ocorrenciaDAO = new Atendido_ocorrenciaDAO();
		$anexos = $Atendido_ocorrenciaDAO->listarAnexo($id_ocorrencia);
		
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
			$ocorrenciaDAO->incluir($ocorrencia);

			$arquivos = $_FILES["arquivos"];

			$ocorrenciaDAO->incluirArquivos($arquivos);

			$msg = "success";
			$sccd = "Ocorrencia enviada com sucesso";
			header("Location: " . WWW . "html/atendido/cadastro_ocorrencia.php?msg=" . urlencode($msg) . "&sccd=" . urlencode($sccd));
		} catch (PDOException $e) {
			Util::tratarException($e);
		}
	}

	public function verificar()
	{
		extract($_REQUEST);
		// se não estiver definida ou vazia//
		if ((!isset($descricao)) || (empty($descricao))) {
			$msg .= "Descricao do atendido não informado. Por favor, informe a descricao!";
			header('Location: ../html/atendido/cadastro_ocorrencia.php?msg=' . urlencode($msg));
			exit();
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
		
		if ((!isset($data)) || (empty($data))) {
			$msg .= "Data da ocorrencia não informada. Por favor, informe a data!";
			header('Location: ../html/atendido/cadastro_ocorrencia.php?msg=' . urlencode($msg));
			exit();
		}

		$ocorrencia = new Ocorrencia($descricao);
		$ocorrencia->setAtendido_idatendido($atendido_idatendido);
		
		$ocorrencia->setFuncionario_idfuncionario($id_funcionario);
		$ocorrencia->setId_tipos_ocorrencia($id_tipos_ocorrencia);
		
		$ocorrencia->setData($data);
		return $ocorrencia;
	}
	//Verificar despachos
	public function verificarDespacho()
	{
		extract($_REQUEST);
		
		$ocorrencia = new Ocorrencia($descricao);
		$ocorrencia->setAtendido_idatendido($atendido_idatendido);
		$ocorrencia->setFuncionario_idfuncionario($id_funcionario);
		$ocorrencia->setId_tipos_ocorrencia($id_tipos_ocorrencia);
		$ocorrencia->setData($data);
	
		return $ocorrencia;
	}

	public function incluirdoc($anexo, $lastId)
	{
		extract($_REQUEST);
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
			$anexo_tmpName = $arq['tmp_name'];
			$arquivo = file_get_contents($anexo_tmpName[$i]);
			$arquivo1 = $arq['name'][$i];
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
				Util::tratarException($e);
			}

			try {
				$anexoDAO = new AnexoDAO();
				$anexoDAO->incluir($anexo);
			} catch (PDOException $e) {
				Util::tratarException($e);
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
				$_SESSION['atendido_ocorrencia'] = $inf;
				$cache->save($id, $inf, '15 seconds');
			} catch (Exception $e) {
				Util::tratarException($e);
			}
		} 
	}
}
