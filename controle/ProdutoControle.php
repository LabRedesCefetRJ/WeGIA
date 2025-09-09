<?php
include_once ROOT . '/classes/Categoria.php';
include_once ROOT . '/dao/CategoriaDAO.php';
include_once ROOT . '/classes/Unidade.php';
include_once ROOT . '/dao/UnidadeDAO.php';
include_once ROOT . '/classes/Produto.php';
include_once ROOT . '/dao/ProdutoDAO.php';

include_once ROOT . '/classes/Estoque.php';
include_once ROOT . '/dao/EstoqueDAO.php';

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class ProdutoControle
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if (is_null($pdo)) {
            $this->pdo = Conexao::connect();
        } else {
            $this->pdo = $pdo;
        }
    }

    public function verificar()
    {
        extract($_REQUEST);
        if ((!isset($descricao)) || empty(($descricao))) {
            $msg .= "descricao do produto nÃ£o informado. Por favor, informe um descricao!";
            header('Location: ' . WWW . 'html/produto.html?msg=' . $msg);
        }
        if ((!isset($codigo)) || empty(($codigo))) {
            $msg .= "Código do produto nÃ£o informado. Por favor, informe o código!";
            header('Location: ' . WWW . 'html/produto.html?msg=' . $msg);
        }
        if ((!isset($preco)) || empty(($preco))) {
            $msg .= "Preço do produto nÃ£o informado. Por favor, informe um preço!";
            header('Location: ' . WWW . 'html/produto.html?msg=' . $msg);
        } else {
            $produto = new produto($descricao, $codigo, $preco);

            return $produto;
        }
    }

    public function listarTodos()
    {
        extract($_REQUEST);
        $produtoDAO = new produtoDAO();
        $produtos = $produtoDAO->listarTodos();
        session_start();
        $_SESSION['produtos'] = $produtos;
        header('Location: ' . $nextPage);
    }

    public function listarporCodigo($codigo)
    {
        session_start();
        $codigo = $_REQUEST['codigo'];
        try {
            $produtoDao = new ProdutoDAO();
            $produto = $produtoDao->listarUm($codigo);
            $_SESSION['produto'] = $produto;
        } catch (Exception $e) {
            $msg = "Não foi possível listar o produto!";
            header('Location: caminho.php?msg=' . $msg);
        }

        $catDao = new CategoriaDAO();
        $categorias = $catDao->listarTodos();
        $_SESSION['categorias'] = $categorias;

        header('Location: ' . $_REQUEST['nextPage']);
    }

    public function listarporNome($descricao)
    {
        session_start();
        $descricao = $_REQUEST['descricao'];
        try {
            $produtoDao = new ProdutoDAO();
            $produto = $produtoDao->listarUm($descricao);
            $_SESSION['produto'] = $produto;
        } catch (Exception $e) {
            $msg = "Não foi possível listar o produto!";
            header('Location: ' . WWW . 'html/geral/msg.php?msg=' . $msg);
        }

        $catDao = new CategoriaDAO();
        $categorias = $catDao->listarTodos();
        $_SESSION['categorias'] = $categorias;

        header('Location: ' . $_REQUEST['nextPage']);
    }

    public function listarDescricao()
    {
        $produtoDAO = new ProdutoDAO();
        $produtos = $produtoDAO->listarDescricao();
        session_start();
        $_SESSION['autocomplete'] = $produtos;
        header('Location: ' . $_REQUEST['nextPage']);
    }

    public function incluir()
    {
        $produto = $this->verificar();
        //extract($_REQUEST);
        $id_categoria = $_REQUEST['id_categoria'];
        $id_unidade = $_REQUEST['id_unidade'];
        $produtoDAO = new ProdutoDAO();
        //$catDAO = new CategoriaDAO();
        //$uniDAO = new UnidadeDAO();

        //$categoria = $catDAO->listarUm($id_categoria);
        //$unidade = $uniDAO->listarUm($id_unidade);
        try {

            $produto->set_categoria_produto($id_categoria);
            $produto->set_unidade($id_unidade);

            $produtoDAO->incluir($produto);

            session_start();
            header("Location: " . WWW . "html/matPat/cadastro_produto.php");
        } catch (PDOException $e) {
            $msg = "Não foi possível registrar o produto <form> <input type='button' value='Voltar' onClick='history.go(-1)'> </form>" . "<br>" . $e->getMessage();
            echo $msg;
        }
    }

    public function excluir()
    {
        try {
            $idProduto = filter_input(INPUT_GET, 'id_produto', FILTER_VALIDATE_INT);

            if (!$idProduto || $idProduto < 1) {
                throw new InvalidArgumentException('O id do produto informado não é válido.', 400);
            }

            $stmtProduto = $this->pdo->prepare("SELECT qtd FROM estoque WHERE id_produto =:idProduto");
            $stmtProduto->bindValue(':idProduto', $idProduto, PDO::PARAM_INT);
            $stmtProduto->execute();
            $produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);

            $stmtSaidas = $this->pdo->prepare("SELECT * FROM isaida WHERE id_produto=:idProduto");
            $stmtSaidas->bindValue(':idProduto', $idProduto, PDO::PARAM_INT);
            $stmtSaidas->execute();

            $stmtEntradas = $this->pdo->prepare("SELECT * FROM ientrada WHERE id_produto=:idProduto");
            $stmtEntradas->bindValue(':idProduto', $idProduto, PDO::PARAM_INT);
            $stmtEntradas->execute();

            $registros = $stmtSaidas->fetchAll(PDO::FETCH_ASSOC) || $stmtEntradas->fetchAll(PDO::FETCH_ASSOC);
            
            if ($produto) {
                if (intval($produto['qtd']) < 0 && !$registros) {
                    $produtoDAO = new ProdutoDAO();
                    $produtoDAO->excluir($idProduto);
                    header('Location:' . WWW . 'html/matPat/listar_produto.php');
                } else {
                    header('Location: ' . WWW . 'html/matPat/remover_produto.php?id_produto=' . htmlspecialchars($idProduto));
                }
            } else {
                if (!$registros) {

                    $produtoDAO = new ProdutoDAO();
                    $produtoDAO->excluir($idProduto);
                    header('Location: ' . WWW . 'html/matPat/listar_produto.php');
                } else {
                    header('Location: ' . WWW . 'html/matPat/remover_produto.php?id_produto=' . htmlspecialchars($idProduto));
                }
            }
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarId()
    {
        extract($_REQUEST);
        $id = $_GET['id_produto'];
        try {
            $produtoDAO = new ProdutoDAO();
            $produto = $produtoDAO->listarId($id);
            session_start();
            $_SESSION['produto'] = $produto;
            header('Location: ' . $nextPage);
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage();
        }
    }

    public function alterarProduto()
    {
        extract($_REQUEST);
        $produto = new Produto($descricao, $codigo, $preco);
        $produtoDAO = new ProdutoDAO();
        $catDAO = new CategoriaDAO();
        $uniDAO = new UnidadeDAO();

        $categoria = $catDAO->listarUm($id_categoria);
        $unidade = $uniDAO->listarUm($id_unidade);

        try {
            $produto->setId_produto($id_produto);
            $produto->set_categoria_produto($id_categoria);
            $produto->set_unidade($id_unidade);
            $produtoDAO->alterarProduto($produto);
            header('Location: ' . $nextPage);
        } catch (Exception $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }

    /**
     * Retorna uma lista dos produtos disponíveis no sistema e suas respectivas quantidades no almoxarifado especificado
     */
    public function getProdutosParaCadastrarEntradaOuSaidaPorAlmoxarifado()
    {
        require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'ProdutoDTOCadastro.php';

        try {
            $almoxarifadoId = filter_input(INPUT_GET, 'almoxarifado', FILTER_SANITIZE_NUMBER_INT);

            if ($almoxarifadoId < 1) {
                throw new InvalidArgumentException('O id do almoxarifado informado é inválido', 400);
            }

            $produtoDAO = new ProdutoDAO();

            $produtosPorAlmoxarifado = $produtoDAO->getProdutosPorAlmoxarifado($almoxarifadoId);

            $produtos = json_decode($produtoDAO->listarTodos(), true);

            $aux = [];
            $produtosDTO = [];

            foreach ($produtosPorAlmoxarifado as $produto) {
                $aux[$produto['id_produto']] = $produto;
            }

            foreach ($produtos as $produto) {
                $produto['qtd'] = isset($aux[$produto['id_produto']]) ? $aux[$produto['id_produto']]['qtd'] : 0;
                $produtosDTO[] = new ProdutoDTOCadastro($produto['id_produto'], $produto['descricao'], $produto['qtd'], $produto['codigo'], $produto['preco']);
            }

            echo json_encode($produtosDTO);
        } catch (Exception $e) {
            http_response_code($e->getCode());
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }
}
