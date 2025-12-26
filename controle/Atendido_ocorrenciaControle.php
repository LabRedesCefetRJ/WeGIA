<?php
if (session_status() === PHP_SESSION_NONE)
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
			$atendido_ocorrenciaDAO = new Atendido_ocorrenciaDAO();
			$ocorrencias = $atendido_ocorrenciaDAO->listarTodos();
			session_start();
			$_SESSION['ocorrencia'] = $ocorrencias;
		} catch (Exception $e) {
			Util::tratarException($e);
		}
	}

	//Listar Despachos com anexo
	public function listarTodosComAnexo()
	{
		extract($_REQUEST);
		$despachoComAnexoDAO = new Atendido_ocorrenciaDAO();
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


	public function incluir()
	{
		extract($_REQUEST);

		$atendido_idatendido = filter_var($atendido_idatendido ?? null, FILTER_VALIDATE_INT) ?: 0;
		if ($atendido_idatendido < 1) {
			$_SESSION['msg']  = "ID do atendido inválido!";
			$_SESSION['tipo'] = "error";
			header("Location: " . WWW . "html/atendido/cadastro_ocorrencia.php?idatendido=0");
			exit;
		}

		try {

			if (!empty($data)) {
				$pdo = Conexao::connect();
				$sql_nascimento = "
                SELECT p.data_nascimento 
                FROM atendido a 
                JOIN pessoa p ON a.pessoa_id_pessoa = p.id_pessoa 
                WHERE a.idatendido = :id
            ";
				$stmt_nascimento = $pdo->prepare($sql_nascimento);
				$stmt_nascimento->bindValue(':id', $atendido_idatendido, PDO::PARAM_INT);
				$stmt_nascimento->execute();
				$atendido_nasc = $stmt_nascimento->fetch(PDO::FETCH_ASSOC);

				if (
					!$atendido_nasc ||
					empty($atendido_nasc['data_nascimento']) ||
					$atendido_nasc['data_nascimento'] === '0000-00-00'
				) {
					$_SESSION['msg']  = "Atenção: Atendido sem data de nascimento cadastrada.";
					$_SESSION['tipo'] = "warning";
					header("Location: " . WWW . "html/atendido/Profile_Atendido.php?idatendido=" . $atendido_idatendido);
					exit;
				}

				try {
					$data_nascimento_obj = new DateTime($atendido_nasc['data_nascimento']);
					$data_ocorrencia_obj = new DateTime($data);
				} catch (Exception $e) {
					error_log("Erro DateTime em ocorrência: " . $e->getMessage());
					$_SESSION['msg']  = "Erro no formato da data. Verifique a data de nascimento e a data da ocorrência.";
					$_SESSION['tipo'] = "error";
					header("Location: " . WWW . "html/atendido/cadastro_ocorrencia.php?idatendido=" . $atendido_idatendido);
					exit;
				}

				if ($data_ocorrencia_obj < $data_nascimento_obj) {
					$_SESSION['msg']  = "Erro: A data da ocorrência não pode ser anterior à data de nascimento!";
					$_SESSION['tipo'] = "error";
					header("Location: " . WWW . "html/atendido/cadastro_ocorrencia.php?idatendido=" . $atendido_idatendido);
					exit;
				}
			}

			$ocorrencia    = $this->verificarDespacho();
			$ocorrenciaDAO = new Atendido_ocorrenciaDAO();
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
		$msg = "";

		if (!isset($descricao) || empty($descricao)) {
			$msg = "Descrição da ocorrência não informada!";
			header('Location: ../html/atendido/cadastro_ocorrencia.php?msg=' . urlencode($msg));
			exit;
		}

		if (!isset($data) || empty($data)) {
			$msg = "Data da ocorrência não informada!";
			header('Location: ../html/atendido/cadastro_ocorrencia.php?msg=' . urlencode($msg));
			exit;
		}

		$ocorrencia = new Ocorrencia($descricao);
		$ocorrencia->setAtendido_idatendido($atendido_idatendido ?? '');
		$ocorrencia->setFuncionario_idfuncionario($id_funcionario ?? '');
		$ocorrencia->setId_tipos_ocorrencia($id_tipos_ocorrencia ?? '');
		$ocorrencia->setData($data);
		return $ocorrencia;
	}

	public function verificarDespacho()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		extract($_REQUEST);

		if (!isset($descricao) || empty($descricao)) {
			throw new InvalidArgumentException("Descrição da ocorrência obrigatória!");
		}

		$ocorrencia = new Ocorrencia($descricao);
		$ocorrencia->setAtendido_idatendido($atendido_idatendido ?? 0);
		$ocorrencia->setFuncionario_idfuncionario($id_funcionario ?? 0);
		$ocorrencia->setId_tipos_ocorrencia($id_tipos_ocorrencia ?? 0);
		$ocorrencia->setData($data ?? date('Y-m-d'));
		return $ocorrencia;
	}

	public function incluirdoc($anexo, $lastId)
	{
		extract($_REQUEST);
		$arq = $_FILES['anexo'];

		$arq['name'] = array_unique(array_filter($arq['name']));
		$arq['type'] = array_unique(array_filter($arq['type']));
		$arq['tmp_name'] = array_unique(array_filter($arq['tmp_name']));
		$arq['error'] = array_unique(array_filter($arq['error']));
		$arq['size'] = array_unique(array_filter($arq['size']));

		$novo_total = count($arq['name']);

		for ($i = 0; $i < $novo_total; $i++) {
			if ($arq['error'][$i] !== UPLOAD_ERR_OK) continue;

			$arquivo = file_get_contents($arq['tmp_name'][$i]);
			$arquivo1 = $arq['name'][$i];
			$pos = strrpos($arquivo1, ".");
			if ($pos === false) continue;

			$extensao = substr($arquivo1, $pos + 1);
			$nome = substr($arquivo1, 0, $pos);

			$AnexoControle = new AnexoControle;
			$arquivo_zip = $AnexoControle->comprimir($arquivo);

			try {
				$anexo_obj = new Anexo();
				$anexo_obj->setId_despacho($lastId);
				$anexo_obj->setAnexo($arquivo_zip);
				$anexo_obj->setNome($nome);
				$anexo_obj->setExtensao($extensao);

				$anexoDAO = new AnexoDAO();
				$anexoDAO->incluir($anexo_obj);
			} catch (Exception $e) {
				Util::tratarException($e);
			}
		}
	}

	public function listarUm()
	{
		extract($_REQUEST);
		$cache = new Cache();
		$inf = $cache->read($id);

		$atendido_ocorrenciaDAO = new Atendido_ocorrenciaDAO();
		$inf = $atendido_ocorrenciaDAO->listar($id);

		$_SESSION['atendido_ocorrencia'] = $inf;
		$cache->save($id, $inf, '15 seconds');
		exit;
	}
}
