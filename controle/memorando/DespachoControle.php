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
require_once ROOT . "/classes/memorando/Despacho.php";
require_once ROOT . "/dao/memorando/DespachoDAO.php";
require_once ROOT . "/dao/memorando/MemorandoDAO.php";
require_once ROOT . "/controle/memorando/MemorandoControle.php";

class DespachoControle
{
	//Listar despachos
	public function listarTodos()
	{
		extract($_REQUEST);
		$despachoDAO = new DespachoDAO();
		$despachos = $despachoDAO->listarTodos($id_memorando);
		$_SESSION['despacho'] = $despachos;

		$MemorandoDAO = new MemorandoDAO();
		$dadosMemorando = $MemorandoDAO->listarTodosId($id_memorando);

		$ultimoDespacho =  new MemorandoControle;
		$ultimoDespacho->buscarUltimoDespacho($id_memorando);

		if (!empty($_SESSION['ultimo_despacho'])) {
			if ($dadosMemorando[0]['id_status_memorando'] == 3 and $_SESSION['ultimo_despacho'][0]['id_destinatarioo'] == $_SESSION['id_pessoa']) {
				$memorando = new Memorando('', '', $dadosMemorando[0]['id_status_memorando'], '', '');
				$memorando->setId_memorando($id_memorando);
				$memorando->setId_status_memorando(2);
				$MemorandoDAO2 = new MemorandoDAO();
				$id_status_memorando = 2;
				$MemorandoDAO2->alterarIdStatusMemorando($memorando);
			}
		}
	}

	//Listar Despachos com anexo
	public function listarTodosComAnexo()
	{
		extract($_REQUEST);
		$despachoComAnexoDAO = new DespachoDAO();
		$despachosComAnexo = $despachoComAnexoDAO->listarTodosComAnexo($id_memorando);
		$_SESSION['despachoComAnexo'] = $despachosComAnexo;
	}

	//Incluir despachos  
	public function incluir()
	{
		extract($_REQUEST);
		$despacho = $this->verificarDespacho();
		$despachoDAO = new DespachoDAO();
		try {
			$lastId = $despachoDAO->incluir($despacho);
			$anexoss = $_FILES["anexo"];
			$anexo2 = $_FILES["anexo"]["tmp_name"][0];
			var_dump($anexo2);
			if (isset($anexo2) && !empty($anexo2)) {
				require_once ROOT . "/controle/memorando/AnexoControle.php";
				$arquivo = new AnexoControle();
				$arquivo->incluir($anexoss, $lastId);
			}
			$msg = "success";
			$sccd = "Despacho enviado com sucesso";
			header("Location: " . WWW . "html/memorando/listar_memorandos_ativos.php?msg=" . $msg . "&sccd=" . $sccd);
		} catch (PDOException $e) {
			$msg = "Não foi possível criar o despacho" . "<br>" . $e->getMessage();
			echo $msg;
		}
	}

	//Verificar despachos
	public function verificarDespacho()
	{
		session_start();
		$cpf_usuario = $_SESSION["usuario"];
		extract($_REQUEST);

		$pessoa = new UsuarioDAO();
		$id_pessoa = $pessoa->obterUsuario($cpf_usuario);
		$id_pessoa = $id_pessoa['0']['id_pessoa'];

		try {
			$despacho = new Despacho($texto, $id_pessoa, $destinatario, $id_memorando);
			return $despacho;
		} catch (InvalidArgumentException $e) {
			http_response_code(400);
			exit('Erro ao verificar o despacho: ' . $e->getMessage());
		}
	}

	//Busca um despacho pelo id do memorando
	public function getPorId(int $id){
		try{
			if($id < 1){
                throw new InvalidArgumentException('O id de um despacho não pode ser menor que 1.');
            }

			$despachoDAO = new DespachoDAO();
			$resultado = $despachoDAO->getPorId($id);

			if(!$resultado){
				return null;
			}

			return $resultado;
		}catch(Exception $e){
			echo 'Erro ao buscar um despacho pelo id: '.$e->getMessage();
		}
	}
}
