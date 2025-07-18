<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);
session_start();

function processaRequisicao($nomeClasse, $metodo, $modulo = null, $data = null)
{
	if ($nomeClasse && $metodo) {

		//Pessoas não autenticadas não devem ter acesso as funcionalidades do control.php
		if (!isset($_SESSION['id_pessoa'])) {
			http_response_code(401);
			exit('Operação negada: Cliente não autorizado');
		}

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
			http_response_code(400);
			echo json_encode(['erro' => 'Controladora inválida']);
			exit();
		}

		//Método de alterar senha é de acesso universal para todos os logados no sistema
		if ($metodo != 'alterarSenha') {
			$path = __DIR__;
			require_once($path . '/../dao/MiddlewareDAO.php');
			$middleware = new MiddlewareDAO();

			//Verifica se a pessoa possuí o recurso necessário para acessar a funcionalidade desejada
			if (!$middleware->verificarPermissao($_SESSION['id_pessoa'], $nomeClasse, $controladorasRecursos)) {
				http_response_code(401);
				echo json_encode(['erro' => 'Acesso não autorizado']);
				exit();
			}
		}

		if ($modulo) {
			include_once $modulo . "/" . $nomeClasse . ".php";
		} else {
			include_once $nomeClasse . ".php";
		}

		$objeto = new $nomeClasse();
		
		if (!is_null($data)) {
			$objeto->$metodo($data);
		} else {
			$objeto->$metodo();
		}
	} else {
		// Responde com erro se as variáveis necessárias não foram fornecidas
		http_response_code(400);
		echo json_encode(['erro' => 'O método e a controladora não podem ser vazios']);
		exit();
	}
}

if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
	// Recebe o JSON da requisição
	$json = file_get_contents('php://input');
	// Decodifica o JSON
	$data = json_decode($json, true);

	// Extrai as variáveis do array $data
	$nomeClasse = $data['nomeClasse'] ?? null;
	$metodo = $data['metodo'] ?? null;
	$modulo = $data['modulo'] ?? null;

	// Remove as chaves de controle para verificar se há dados adicionais
	$dadosExtras = $data;
	unset($dadosExtras['nomeClasse'], $dadosExtras['metodo'], $dadosExtras['modulo']);

	// Processa a requisição com ou sem dados adicionais
	if (!empty($dadosExtras)) {
		processaRequisicao($nomeClasse, $metodo, $modulo, $dadosExtras);
	} else {
		processaRequisicao($nomeClasse, $metodo, $modulo);
	}
} else {
	// Recebe os dados do formulário normalmente
	$nomeClasse = $_REQUEST['nomeClasse'] ?? null;
	$metodo = $_REQUEST['metodo'] ?? null;
	$modulo = $_REQUEST['modulo'] ?? null;

	$json = file_get_contents('php://input');
	$data = json_decode($json, true);

	$dadosExtras = $data;
	unset($dadosExtras['nomeClasse'], $dadosExtras['metodo'], $dadosExtras['modulo']);

	if (!empty($dadosExtras)) {
		processaRequisicao($nomeClasse, $metodo, $modulo, $dadosExtras);
	} else {
		processaRequisicao($nomeClasse, $metodo, $modulo);
	}
}

