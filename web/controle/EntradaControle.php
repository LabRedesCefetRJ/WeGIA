<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
include_once '../classes/Entrada.php';
include_once '../dao/EntradaDAO.php';
include_once '../classes/Origem.php';
include_once '../dao/OrigemDAO.php';
include_once '../classes/Almoxarifado.php';
include_once '../dao/AlmoxarifadoDAO.php';
include_once '../classes/TipoEntrada.php';
include_once '../dao/TipoEntradaDAO.php';
include_once '../classes/Ientrada.php';
include_once '../dao/IentradaDAO.php';
include_once '../classes/Produto.php';
include_once '../dao/ProdutoDAO.php';
class EntradaControle
{
    public function verificar()
    {

        session_start();

        // Acesse variáveis diretamente e valide
        $total_total = isset($_REQUEST['total_total']) ? floatval($_REQUEST['total_total']) : 0;
        //extract($_REQUEST);

        Util::definirFusoHorario();
        $horadata = explode(" ", date('Y-m-d H:i'));
        $data = $horadata[0];
        $hora = $horadata[1];
        $valor_total = $total_total;
        $responsavel = $_SESSION['id_pessoa'];

        if (!$responsavel) {
            throw new Exception("Responsável não encontrado na sessão.");
        }

        $entrada = new Entrada($data, $hora, $valor_total, $responsavel);

        return $entrada;
    }

    public function listarTodos()
    {
        try {
            $entradaDAO = new EntradaDAO();
            $entradas = $entradaDAO->listarTodos();

            echo json_encode([
                "sucesso" => true,
                "dados" => $entradas
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não foi possível listar as entradas"
            ]);
        }
    }

    public function listarTodosComProdutos()
    {
        header('Content-Type: application/json');

        try {
            $entradaDAO = new EntradaDAO();
            $entradas = $entradaDAO->listarTodosComProdutos();

            echo json_encode([
                "sucesso" => true,
                "dados" => $entradas
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não foi possível listar as entradas com produtos"
            ]);
        }

        exit;
    }

    public function incluir()
    {
        extract($_REQUEST);
        $entrada = $this->verificar();

        $entradaDAO = new EntradaDAO();
        $origemDAO = new OrigemDAO();
        $almoxarifadoDAO = new AlmoxarifadoDAO();
        $TipoEntradaDAO = new TipoEntradaDAO();
        $origem = explode("-", $origem);
        $origem = $origem[0];
        $origem = $origemDAO->listarUm($origem);
        $almoxarifado = $almoxarifadoDAO->listarUm($almoxarifado);
        $TipoEntrada = $TipoEntradaDAO->listarUm($tipo_entrada);

        try {
            $entrada->set_origem($origem);
            $entrada->set_almoxarifado($almoxarifado);
            $entrada->set_tipo($TipoEntrada);

            $entradaDAO->incluir($entrada);

            $id_responsavel = $entradaDAO->ultima();
            $id_responsavel = implode("", $id_responsavel);

            $x = 1;
            $id = "id";
            $qtdd = "qtd";
            $valor_unitario = "valor_unitario";
            while ($x <= $conta) {
                if (isset(${$id . $x})) {
                    $ientrada = new Ientrada(${$qtdd . $x}, ${$valor_unitario . $x});
                    $ientradaDAO = new IentradaDAO();
                    $produtoDAO = new ProdutoDAO();
                    $produto = $produtoDAO->listarUm(${$id . $x});
                    $entrada = $entradaDAO->listarUm($id_responsavel);


                    $ientrada->setId_produto($produto);
                    $ientrada->setId_entrada($entrada);
                    $ientrada = $ientradaDAO->incluir($ientrada);
                }
                $x++;
            }

            echo json_encode([
                    "sucesso" => true,
                    "mensagem" => "Entrada cadastrada com sucesso"
            ]);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => $e->getMessage()
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não foi possível adicionar a entrada"
            ]);
        }
    }

    public function listarId()
    {
        header('Content-Type: application/json');

        $id_entrada = $_REQUEST['id_entrada'] ?? null;

        if (!$id_entrada || !is_numeric($id_entrada) || $id_entrada < 1) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "ID inválido"
            ]);
            exit;
        }

        try {
            $entradaDAO = new EntradaDAO();
            $entrada = $entradaDAO->listarId($id_entrada);

            echo json_encode([
                "sucesso" => true,
                "dados" => $entrada
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não foi possível buscar a entrada"
            ]);
        }
    }

    public function listarArquivados()
    {
        header('Content-Type: application/json');

        try {
            $entradaDAO = new EntradaDAO();
            $entradas = $entradaDAO->listarArquivados();

            echo json_encode([
                "sucesso" => true,
                "dados" => $entradas
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não foi possível listar as entradas arquivadas"
            ]);
        }
        exit;
    }
}
