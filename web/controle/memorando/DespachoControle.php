<?php
if (session_status() == PHP_SESSION_NONE)
	session_start();

require_once dirname(__FILE__) . "/../../config.php";

require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/memorando/Despacho.php";
require_once ROOT . "/dao/memorando/DespachoDAO.php";
require_once ROOT . "/dao/memorando/MemorandoDAO.php";
require_once ROOT . "/controle/memorando/MemorandoControle.php";
require_once ROOT . "/classes/Util.php";

class DespachoControle
{
	/**
	 * Verifica se o usuário logado na sessão pode acessar/alterar despachos de um
	 * memorando: precisa ser quem criou o memorando ou já ter participado dele
	 * como remetente/destinatário de algum despacho. Isso é checado aqui (e não
	 * só na página que consome esta classe) porque este método também é
	 * alcançável diretamente via controle/control.php?nomeClasse=DespachoControle,
	 * que só valida permissão genérica no módulo (recurso 3), sem checar o
	 * registro específico -- mesmo padrão do IDOR já corrigido em
	 * AnexoControle::listarAnexo.
	 */
	private function usuarioTemAcessoAoMemorando($id_memorando)
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		$id_memorando = filter_var($id_memorando, FILTER_VALIDATE_INT);
		$id_pessoa = filter_var($_SESSION['id_pessoa'] ?? null, FILTER_VALIDATE_INT);

		if (!$id_memorando || $id_memorando < 1 || !$id_pessoa || $id_pessoa < 1) {
			return false;
		}

		$memorandoDAO = new MemorandoDAO();
		$dadosMemorando = $memorandoDAO->listarTodosId($id_memorando);

		if (empty($dadosMemorando)) {
			return false;
		}

		if ((int)$dadosMemorando[0]['id_pessoa'] === $id_pessoa) {
			return true;
		}

		$memorandosPermitidos = $memorandoDAO->listarIdTodosInativos();

		return in_array($id_memorando, $memorandosPermitidos ?? []);
	}

	//Listar despachos
	public function listarTodos()
	{
		extract($_REQUEST);
		if (!$this->usuarioTemAcessoAoMemorando($id_memorando)) {
			throw new InvalidArgumentException('Você não tem acesso a este memorando.', 403);
		}
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
		if (!$this->usuarioTemAcessoAoMemorando($id_memorando)) {
			throw new InvalidArgumentException('Você não tem acesso a este memorando.', 403);
		}
		$despachoComAnexoDAO = new DespachoDAO();
		$despachosComAnexo = $despachoComAnexoDAO->listarTodosComAnexo($id_memorando);
		$_SESSION['despachoComAnexo'] = $despachosComAnexo;
	}

	//Incluir despachos  
	public function incluir()
	{
		extract($_REQUEST);
		if (!$this->usuarioTemAcessoAoMemorando($id_memorando)) {
			throw new InvalidArgumentException('Você não tem acesso a este memorando.', 403);
		}
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
		try {
			$cpf_usuario = filter_var($_SESSION["usuario"], FILTER_SANITIZE_SPECIAL_CHARS);

			if(!Util::validarCPF($cpf_usuario) && $cpf_usuario != 'admin') 
				throw new InvalidArgumentException("CPF do usuário é inválido. $cpf_usuario", 400);

			$pessoa = new UsuarioDAO();

			$id_pessoa = filter_var($pessoa->obterUsuario($cpf_usuario)['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);
			$destinatario = filter_input(INPUT_POST, 'destinatario', FILTER_SANITIZE_NUMBER_INT);
			if (empty($destinatario)) {
                throw new InvalidArgumentException("É obrigatório selecionar um destino para o memorando.", 400);
            }
			$id_memorando = filter_var($_REQUEST['id_memorando'], FILTER_SANITIZE_NUMBER_INT);
			$texto = $_REQUEST['texto'] ?? '';
			if (!is_string($texto)) {
				throw new InvalidArgumentException('O texto de um despacho não pode ser vazio.', 400);
			}

			$despacho = new Despacho($texto, $id_pessoa, $destinatario, $id_memorando);
			return $despacho;
		} catch (Exception $e) {
			Util::tratarException($e);
		}
	}

	//Busca um despacho pelo id do memorando
	public function getPorId(int $id)
	{
		try {
			if ($id < 1) {
				throw new InvalidArgumentException('O id de um despacho não pode ser menor que 1.', 400);
			}

			if (!$this->usuarioTemAcessoAoMemorando($id)) {
				throw new InvalidArgumentException('Você não tem acesso a este memorando.', 403);
			}

			$despachoDAO = new DespachoDAO();
			$resultado = $despachoDAO->getPorId($id);

			if (!$resultado) {
				return null;
			}

			return $resultado;
		} catch (Exception $e) {
			echo 'Erro ao buscar um despacho pelo id: ' . $e->getMessage();
		}
	}
}
