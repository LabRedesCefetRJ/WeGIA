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

    public function anular()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idEntrada = filter_input(INPUT_POST, 'id_entrada', FILTER_VALIDATE_INT);

            if (!$idEntrada || $idEntrada < 1) {
                throw new InvalidArgumentException("ID da entrada inválido.");
            }

            $entradaDAO = new EntradaDAO();
            $resultado = $entradaDAO->anular($idEntrada);

            if (!empty($resultado['produtos'])) {
                require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'EstoqueService.php';

                $estoqueService = new EstoqueService();

                foreach ($resultado['produtos'] as $idProduto) {
                    $estoqueService->verificarEstoqueMinimo(
                        (int)$idProduto,
                        (int)$resultado['id_almoxarifado']
                    );
                }
            }

            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Entrada anulada com sucesso."
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => $e->getMessage()
            ]);
        }

        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idEntrada = filter_input(INPUT_POST, 'id_entrada', FILTER_VALIDATE_INT);
            $idOrigem = filter_input(INPUT_POST, 'id_origem', FILTER_VALIDATE_INT);
            $idTipo = filter_input(INPUT_POST, 'id_tipo', FILTER_VALIDATE_INT);

            $data = trim($_POST['data'] ?? '');
            $hora = trim($_POST['hora'] ?? '');
            $itensJson = $_POST['itens'] ?? '[]';

            if (!$idEntrada || $idEntrada < 1) {
                throw new InvalidArgumentException("ID da entrada inválido.");
            }

            if (!$idOrigem || $idOrigem < 1) {
                throw new InvalidArgumentException("Origem inválida.");
            }

            if (!$idTipo || $idTipo < 1) {
                throw new InvalidArgumentException("Tipo de entrada inválido.");
            }

            $dataValida = DateTime::createFromFormat('Y-m-d', $data);

            if (!$dataValida || $dataValida->format('Y-m-d') !== $data) {
                throw new InvalidArgumentException("Data inválida.");
            }

            if (!$hora) {
                throw new InvalidArgumentException("Hora inválida.");
            }

            if (strlen($hora) === 5) {
                $hora .= ':00';
            }

            if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
                throw new InvalidArgumentException("Hora inválida.");
            }

            $itens = json_decode($itensJson, true);

            if (!is_array($itens) || empty($itens)) {
                throw new InvalidArgumentException("Nenhum item informado para edição.");
            }

            $entradaDAO = new EntradaDAO();
            $entradaDAO->editarBasico($idEntrada, $data, $hora, $idOrigem, $idTipo, $itens);

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $ientradaDAO = new IentradaDAO();

            $_SESSION['ientrada'] = $ientradaDAO->listarId($idEntrada);
            $_SESSION['entradaUnica'] = $entradaDAO->listarId($idEntrada);

            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Entrada atualizada com sucesso."
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => $e->getMessage()
            ]);
        }

        exit;
    }
}
