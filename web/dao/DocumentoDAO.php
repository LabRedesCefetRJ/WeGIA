<?php
	require_once ROOT.'/classes/Documento.php';
	require_once 'Conexao.php';
	class DocumentoDAO
	{

		public function comprimir($anexoParaCompressao)
		{
			$arquivo_zip = gzcompress($anexoParaCompressao);
			return $arquivo_zip;
		}

		public function incluir($documento)
		{
			try {
            $sql = 'call cadimagem(:idPessoa,:imagem,:extensao,:descricao)';
            $sql = str_replace("'", "\'", $sql);            
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);
            $idPessoa=$documento->getIdPessoa();
			$imagem=$documento->getImagem();
            $extensao=$documento->getExtensao();
            $descricao=$documento->getDescricao();
            $stmt->bindParam(':idPessoa',$idPessoa);
			$stmt->bindParam(':imagem',$imagem);
			$stmt->bindParam(':extensao',$extensao);
			$stmt->bindParam(':descricao',$descricao);
            $stmt->execute();
	        }catch (PDOException $e) {
	            echo 'Error: <b>  na tabela documento = ' . $sql . '</b> <br /><br />' . $e->getMessage();
	        }
		}
		public function alterar($documento)
		{
			try {
				$sql = 'update documento set imgdoc=:imagem, imagem_extensao=:extensao,descricao=:descricao where id_documento=:id_documento';
	            $sql = str_replace("'", "\'", $sql);
	            $pdo = Conexao::connect();
	            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	            $stmt = $pdo->prepare($sql);
	            $idDocumento=$documento->getIdDocumento();
				$imagem=$documento->getImagem();
            	$extensao=$documento->getExtensao();
           	 	$descricao=$documento->getDescricao();
           	 	$stmt->bindParam(':id_documento',$idDocumento);
				$stmt->bindParam(':imagem',$imagem);
				$stmt->bindParam(':extensao',$extensao);
				$stmt->bindParam(':descricao',$descricao);
				$stmt->execute();
			} catch (PDOException $e) {
				echo 'Error: <b>  na tabela Documento = ' . $sql . '</b> <br /><br />' . $e->getMessage();
			}
		}
		public function excluir($id)
		{
			try {
				$sql = 'delete from documento where id_documento=:id_documento';
            $sql = str_replace("'", "\'", $sql);
            $pdo = Conexao::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':id_documento', $id);
            
            $stmt->execute();
			} catch (PDOException $e) {
				echo 'Error: <b>  na tabela documentos = ' . $sql . '</b> <br /><br />' . $e->getMessage();
			}
		}
	}
?>