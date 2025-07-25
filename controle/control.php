<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function processaRequisicao($nomeClasse, $metodo, $modulo = null)
{
	if ($nomeClasse && $metodo) {
		//Controladoras permitidas
		$controladorasRecursos = [
			'AdocaoControle' => [6, 64],
			'AlmoxarifadoControle' => [2, 21, 22, 23, 24, 91],
			'AlmoxarifeControle' => [91],
			'Atendido_ocorrenciaControle' => [12],
			'Atendido_ocorrenciaDocControle' => [12],
			'AtendidoControle' => [12],
			'AvisoControle' => [5],
			'AvisoNotificacaoControle' => [5],
			'CargoControle' => [11],
			'CategoriaControle' => [21, 2],
			'controleSaudePet' => [6, 61, 62, 63],
			'DestinoControle' => [21, 2],
			'DocumentoControle' => [5],
			'EnderecoControle' => [9, 12],
			'EnfermidadeControle' => [5, 54],
			'MedicamentoPacienteControle' => [5],
			'EntradaControle' => [23],
			'EstoqueControle' => [22],
			'FuncionarioControle' => [11, 91],
			'IentradaControle' => [23],
			'InformacaoAdicionalControle' => [11],
			'InternoControle' => [],
			'IsaidaControle' => [24],
			'ModuloControle' => [91],
			'MedicamentoControle' => [6, 61, 62, 63],
			'OrigemControle' => [23],
			'ProdutoControle' => [22, 23, 24],
			'PetControle' => [6, 61, 62, 63],
			'AtendimentoControle' => [6, 61, 62, 63],
			'QuadroHorarioControle' => [11],
			'SaidaControle' => [22, 24],
			'SaudeControle' => [5, 12],
			'SinaisVitaisControle' => [5],
			'TipoEntradaControle' => [23],
			'TipoSaidaControle' => [22, 24],
			'UnidadeControle' => [22],
			'MemorandoControle' => [3],
			'DespachoControle' => [3]
		];

		/*Por padrão o control.php irá recusar qualquer controladora informada,
		adicione as controladoras que serão permitidas a lista branca $controladorasRecursos*/
		if (!array_key_exists($nomeClasse, $controladorasRecursos)) {
			throw new InvalidArgumentException('Controladora inválida', 400);
		}

		//Método de alterar senha é de acesso universal para todos os logados no sistema
		if ($metodo != 'alterarSenha') {
			require_once(__DIR__ . '/../dao/MiddlewareDAO.php');
			$middleware = new MiddlewareDAO();

			//Verifica se a pessoa possui o recurso necessário para acessar a funcionalidade desejada
			if (!$middleware->verificarPermissao($_SESSION['id_pessoa'], $nomeClasse, $controladorasRecursos)) {
				throw new LogicException('Acesso não autorizado', 401); // Considerar fazer uma exception de autorização para o projeto
			}
		}

		$pathRequire = '';

		if ($modulo) {
			$pathRequire = $modulo . DIRECTORY_SEPARATOR;
		}

		$pathRequire .= $nomeClasse . ".php";

		if (!file_exists($pathRequire)) {
			throw new InvalidArgumentException('O arquivo para requisição da classe não existe.', 400);
		}

		require_once($pathRequire);

		if (!class_exists($nomeClasse)) {
			throw new InvalidArgumentException('A classe informada não existe no sistema.', 400);
		}

		// Cria uma instância da classe e chama o método
		$objeto = new $nomeClasse();

		if (!method_exists($objeto, $metodo)) {
			throw new InvalidArgumentException('O método informado não existe na classe.', 400);
		}

		$objeto->$metodo();
	} else {
		throw new InvalidArgumentException('O método e a controladora não podem ser vazios', 400);
	}
}

try {
	//Pessoas desautenticadas não devem ter acesso as funcionalidades do control.php
	if (!isset($_SESSION['id_pessoa'])) {
		throw new LogicException('Operação negada: Cliente não autorizado', 401); // Considerar fazer uma exception de autorização para o projeto
	}

	$nomeClasse = '';
	$metodo = '';
	$modulo = '';

	if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
		// Recebe o JSON da requisição
		$json = file_get_contents('php://input');
		// Decodifica o JSON
		$data = json_decode($json, true);

		// Extrai as variáveis do array $data
		$nomeClasse = $data['nomeClasse'] ?? null;
		$metodo = $data['metodo'] ?? null;
		$modulo = $data['modulo'] ?? null;
	} else {
		// Recebe os dados do formulário normalmente
		$nomeClasse = $_REQUEST['nomeClasse'] ?? null;
		$metodo = $_REQUEST['metodo'] ?? null;
		$modulo = $_REQUEST['modulo'] ?? null;
	}

	processaRequisicao($nomeClasse, $metodo, $modulo);
} catch (Exception $e) {
	require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
	Util::tratarException($e);
}
