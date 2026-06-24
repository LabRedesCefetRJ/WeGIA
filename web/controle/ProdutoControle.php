<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

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
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

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

        $codigo = isset($codigo) && $codigo !== '' ? $codigo : null;

        if ((!isset($descricao)) || empty($descricao)) {
            $msg .= "descricao do produto nÃ£o informado. Por favor, informe um descricao!";
            header('Location: ' . WWW . 'html/produto.html?msg=' . $msg);
            exit;
        }
        /*if ((!isset($codigo)) || empty($codigo)) {
            $msg .= "Código do produto nÃ£o informado. Por favor, informe o código!";
            header('Location: ' . WWW . 'html/produto.html?msg=' . $msg);
        }*/
        if ((!isset($preco)) || empty($preco)) {
            $msg .= "Preço do produto nÃ£o informado. Por favor, informe um preço!";
            header('Location: ' . WWW . 'html/produto.html?msg=' . $msg);
            exit;
        } else {
            $produto = new Produto($descricao, $codigo, $preco);

            return $produto;
        }
    }

    public function listarTodos()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(listar_produto|remover_produto)\.php(\?id_produto=\d+|\?tipo=ativo)?)$#';

        try {
            if (!filter_var($nextPage, FILTER_VALIDATE_URL))
                throw new InvalidArgumentException('Erro, a URL informada para a próxima página não é válida.', 412);

            $produtoDAO = new ProdutoDAO();
            $produtos = $produtoDAO->listarTodos();

            $_SESSION['produtos'] = $produtos;

            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarporCodigo($codigo)
    {
        try {
            $produtoDao = new ProdutoDAO();
            $produto = $produtoDao->listarUm($codigo);
            $_SESSION['produto'] = $produto;

            $catDao = new CategoriaDAO();
            $categorias = $catDao->listarTodos();
            $_SESSION['categorias'] = $categorias;

            header('Location: ' . $_REQUEST['nextPage']);
        } catch (Exception $e) {
            Util::tratarException($e);
            $msg = "Não foi possível listar o produto!";
            header('Location: caminho.php?msg=' . $msg);
        }
    }

    public function listarporNome($descricao)
    {
        try {
            $produtoDao = new ProdutoDAO();
            $produto = $produtoDao->listarUm($descricao);
            $_SESSION['produto'] = $produto;


            $catDao = new CategoriaDAO();
            $categorias = $catDao->listarTodos();
            $_SESSION['categorias'] = $categorias;

            header('Location: ' . $_REQUEST['nextPage']);
        } catch (Exception $e) {
            Util::tratarException($e);
            $msg = "Não foi possível listar o produto!";
            header('Location: ' . WWW . 'html/geral/msg.php?msg=' . $msg);
        }
    }

    public function listarDescricao()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(cadastro_entrada|cadastro_saida)\.php)$#';

        try {
            if (!filter_var($nextPage, FILTER_VALIDATE_URL))
                throw new InvalidArgumentException('Erro, a URL informada para a próxima página não é válida.', 412);

            $produtoDAO = new ProdutoDAO();
            $produtos = $produtoDAO->listarDescricao();

            $_SESSION['autocomplete'] = $produtos;

            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluir()
    {
        try {
            $produto = $this->verificar();
            $id_categoria = filter_var($_REQUEST['id_categoria'], FILTER_SANITIZE_NUMBER_INT);
            $id_unidade = filter_var($_REQUEST['id_unidade'], FILTER_SANITIZE_NUMBER_INT);
            $produtoDAO = new ProdutoDAO($this->pdo);

            $produto->set_categoria_produto($id_categoria);
            $produto->set_unidade($id_unidade);

            $produtoDAO->incluir($produto);

            header("Location: " . WWW . "html/matPat/cadastro_produto.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function excluir()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null)) {
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);
            }

            $idProduto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);

            if (!$idProduto || $idProduto < 1) {
                throw new InvalidArgumentException('O id do produto informado não é válido.', 400);
            }

            $produtoDAO = new ProdutoDAO();

            if ($produtoDAO->possuiHistoricoOuEstoque($idProduto)) {
                $_SESSION['erro'] = "Não é possível excluir este produto, pois existem registros de entrada/saída ou estoque vinculados.";
                $_SESSION['id_arquivar'] = $idProduto;

                header('Location: ' . WWW . 'html/matPat/listar_produto.php');
                exit;
            }

            $produtoDAO->excluir($idProduto);

            $_SESSION['msg'] = "Produto excluído com sucesso.";
            header('Location: ' . WWW . 'html/matPat/listar_produto.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['erro'] = "Não é possível excluir este produto, pois existem registros vinculados.";
                $_SESSION['id_arquivar'] = $_POST['id_produto'] ?? null;

                header('Location: ' . WWW . 'html/matPat/listar_produto.php');
                exit;
            }

            Util::tratarException($e);
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
        } catch (Exception $e) {
            Util::tratarException($e);
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
            Util::tratarException($e);
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
            Util::tratarException($e);
        }
    }

    public function arquivar()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null)) {
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);
            }

            $idProduto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);

            if (!$idProduto || $idProduto < 1) {
                throw new InvalidArgumentException('ID inválido para arquivamento.', 400);
            }

            $produtoDAO = new ProdutoDAO();
            $produtoDAO->arquivar($idProduto);

            $_SESSION['msg'] = "Produto arquivado com sucesso.";

            header('Location: ' . WWW . 'html/matPat/listar_produto.php');
            exit;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function desarquivar()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null)) {
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);
            }

            $idProduto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);

            if (!$idProduto || $idProduto < 1) {
                throw new InvalidArgumentException('ID inválido para restauração.', 400);
            }

            $produtoDAO = new ProdutoDAO();
            $produtoDAO->desarquivar($idProduto);

            $_SESSION['msg'] = "Produto restaurado com sucesso.";

            header('Location: ' . WWW . 'html/matPat/listar_produto.php?tipo=arquivado');
            exit;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarArquivados()
    {
        $produtoDAO = new ProdutoDAO();
        $_SESSION['produtos'] = $produtoDAO->listarArquivados();

        header('Location: ' . WWW . 'html/matPat/listar_produto.php?tipo=arquivado');
        exit;
    }
}
