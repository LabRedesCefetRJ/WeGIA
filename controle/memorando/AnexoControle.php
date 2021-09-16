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

require_once ROOT."/classes/memorando/Anexo.php";
require_once ROOT."/dao/memorando/AnexoDAO.php";

class AnexoControle
{
	public function listarTodos($id_memorando)
	{
		$id_despacho=0;
		extract($_REQUEST);
		$AnexoDAO = new AnexoDAO();
		$anexos = $AnexoDAO->listarTodos($id_memorando);
		if (session_status() !== PHP_SESSION_ACTIVE)
 		{
    		session_start();
		}
		$_SESSION['arquivos'] = $anexos;
	}

	public function listarAnexo($id_anexo)
	{
		$AnexoDAO = new AnexoDAO();
		$anexos = $AnexoDAO->listarAnexo($id_anexo);
		if (session_status() !== PHP_SESSION_ACTIVE)
 		{
    		session_start();
		}
		$_SESSION['arq'] = $anexos;
	}

	public function comprimir($anexoParaCompressao)
	{
		$arquivo_zip = gzcompress($anexoParaCompressao);
		return $arquivo_zip;
	}

	public function incluir($anexo, $lastId)
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

		for($i=0; $i<$novo_total; $i++)
		{
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
			$pos = strpos($arquivo1, ".")+1;
			$extensao = substr($arquivo1, $pos, strlen($arquivo1)+1);
			$nome = substr($arquivo1, 0, $pos-1);
			
			$AnexoControle = new AnexoControle;
			$arquivo_zip = $AnexoControle->comprimir($arquivo);
			
			$anexo = new Anexo();
			$anexo->setId_despacho($lastId);
    		$anexo->setAnexo($arquivo_zip);
    		$anexo->setNome($nome);
    		$anexo->setExtensao($extensao);	
    		$anexoDAO = new AnexoDAO();
			try
			{
				$anexoDAO->incluir($anexo);
			}
			catch(PDOException $e)
			{
				$msg= "Não foi possível criar o despacho"."<br>".$e->getMessage();
            	echo $msg;
			}
		}
	}
}
?>