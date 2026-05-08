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

require_once ROOT . "/classes/memorando/Anexo.php";
require_once ROOT . "/dao/memorando/AnexoDAO.php";

class AnexoControle
{
	//Função para listar os memorandos
	public function listarTodos($id_memorando)
	{
		$id_despacho = 0;
		extract($_REQUEST);
		$AnexoDAO = new AnexoDAO();
		$anexos = $AnexoDAO->listarTodos($id_memorando);
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
		$_SESSION['arquivos'] = $anexos;
	}

	//Função para listar anexos
	public function listarAnexo($idAnexo)
	{
		try {
			$idAnexo = filter_var($idAnexo, FILTER_VALIDATE_INT);

			if (!$idAnexo || $idAnexo < 1) {
				throw new InvalidArgumentException('O id fornecido para o anexo não é válido.', 400);
			}

			$AnexoDAO = new AnexoDAO();
			$anexos = $AnexoDAO->listarAnexo($idAnexo);
			if (session_status() !== PHP_SESSION_ACTIVE) {
				session_start();
			}
			$_SESSION['arq'] = $anexos;
		} catch (Exception $e) {
			require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
			Util::tratarException($e);
		}
	}

	//Função para comprimir uma string de dados
	public function comprimir($anexoParaCompressao)
	{
		$arquivo_zip = gzcompress($anexoParaCompressao);
		return $arquivo_zip;
	}

	//Função para incluir um anexo
	public function incluir($anexo, $lastId)
	{
		extract($_REQUEST);
		//$total = count($anexo['name']);
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
			//$tamanho = strlen($arquivo1);
			$pos = strpos($arquivo1, ".") + 1;
			$extensao = substr($arquivo1, $pos, strlen($arquivo1) + 1);
			$nome = substr($arquivo1, 0, $pos - 1);

			$AnexoControle = new AnexoControle;
			$arquivo_zip = $AnexoControle->comprimir($arquivo);

			//Insere um novo anexo
			try {
				$anexo = new Anexo();
				$anexo->setId_despacho($lastId);
				$anexo->setAnexo($arquivo_zip);
				$anexo->setNome($nome);
				$anexo->setExtensao($extensao);
			} catch (InvalidArgumentException $e) {
				echo "Erro ao tentar inserir anexo: " . $e->getMessage();
			}

			//Cria um novo despacho
			try {
				$anexoDAO = new AnexoDAO();
				$anexoDAO->incluir($anexo);
			} catch (PDOException $e) {
				$msg = "Não foi possível criar o despacho" . "<br>" . $e->getMessage();
				echo $msg;
			}
		}
	}
}
