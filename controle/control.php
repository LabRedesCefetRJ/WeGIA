<?php

header('Content-Type: text/html; charset=utf-8'); 

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
            'ExameControle' => [5],
            'MedicoControle' => [5],
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

        if (!array_key_exists($nomeClasse, $controladorasRecursos)) {
            throw new InvalidArgumentException('Controladora inválida', 400);
        }

        if ($metodo != 'alterarSenha') {
            require_once(__DIR__ . '/../dao/MiddlewareDAO.php');
            $middleware = new MiddlewareDAO();

            if (!$middleware->verificarPermissao($_SESSION['id_pessoa'], $nomeClasse, $controladorasRecursos)) {
                throw new LogicException('Acesso não autorizado', 401);
            }
        }

        $pathRequire = dirname(__FILE__) . DIRECTORY_SEPARATOR;

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

        $objeto = new $nomeClasse();

        if (!method_exists($objeto, $metodo)) {
            throw new InvalidArgumentException('O método informado não existe na classe.', 400);
        }

        $objeto->$metodo();
    } else {
        throw new InvalidArgumentException('O método e a controladora não podem ser vazios', 400);
    }
}


$is_json_request = false;

try {
    if (!isset($_SESSION['id_pessoa'])) {
        throw new LogicException('Operação negada: Cliente não autorizado', 401);
    }

    $nomeClasse = '';
    $metodo = '';
    $modulo = '';

    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        
        $is_json_request = true;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $nomeClasse = filter_var($data['nomeClasse'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $metodo = filter_var($data['metodo'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $modulo = filter_var($data['modulo'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);

    } else {
        $nomeClasse = filter_var($_REQUEST['nomeClasse'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $metodo = filter_var($_REQUEST['metodo'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $modulo = filter_var($_REQUEST['modulo'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    processaRequisicao($nomeClasse, $metodo, $modulo);

} catch (Exception $e) {
    
    $codigo = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($codigo);

    if ($is_json_request) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'erro',
            'mensagem' => $e->getMessage()
        ]);
    } else {
        require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
        Util::tratarException($e);
    }
}