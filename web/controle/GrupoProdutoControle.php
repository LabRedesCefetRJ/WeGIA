<?php
include_once ROOT . '/classes/GrupoProduto.php';
include_once ROOT . '/dao/GrupoProdutoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

class GrupoProdutoControle
{
    private $grupoProdutoDAO; 

    public function __construct()
    {
        $this->grupoProdutoDAO = new GrupoProdutoDAO();
    }


    public function verificar()
    {
        $descricaoGrupo = trim($_REQUEST['descricao_grupo']);

        return new GrupoProduto($descricaoGrupo);
    }

    public function listarTodos()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));

        $regex = '#^\.\./html/matPat/'
        . '(alterar_produto|cadastro_produto|listar_grupo_produto|listar_produto)'
        . '\.php'
        . '(?:\?(?:id_produto=\d+|tipo=(?:ativo|arquivado)))?$#';

        $gruposProduto = $this->grupoProdutoDAO->listarTodos();
        
        $_SESSION['grupo_produto'] = $gruposProduto;

        if (preg_match($regex, $nextPage)) {
            header('Location:' . htmlspecialchars($nextPage));
        } else {
            header('Location:' . '../html/home.php');
        }

        exit;
    }

    public function listarUm()
    {
        $id = filter_input(INPUT_GET, 'id_grupo_produto', FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('O id do grupo de produto informado não é válido.');
        }

        $grupoProduto = $this->grupoProdutoDAO->listarUm($id);
        if (!$grupoProduto) {
            http_response_code(404);
            exit('Grupo de produto não encontrado.');
        }

        $_SESSION['grupo_produto_edicao'] = [
            'id_grupo_produto' => $grupoProduto->getIdGrupoProduto(),
            'descricao_grupo' => $grupoProduto->getDescricaoGrupo()
        ];
        header('Location: ' . WWW . 'html/matPat/editar_grupo_produto.php?id_grupo_produto=' . $id);
        exit;
    }

    public function incluir()
    {
        try {
            $grupoProduto = $this->verificar();

            $this->grupoProdutoDAO->incluir($grupoProduto);

            header(
                "Location: " . WWW .
                "html/matPat/adicionar_grupo_produto.php"
            );
            exit();

        } catch (InvalidArgumentException $e) {

            $_SESSION['erro_grupo_produto'] = $e->getMessage();

            header(
                "Location: " . WWW .
                "html/matPat/adicionar_grupo_produto.php"
            );
            exit();

        } catch (Exception $e) {

            $_SESSION['erro_grupo_produto'] =
                "Não foi possível cadastrar o grupo de produto.";

            header(
                "Location: " . WWW .
                "html/matPat/adicionar_grupo_produto.php"
            );
            exit();
        }
    }

    public function editar()
    {
        $id_grupo_produto = filter_input(
            INPUT_POST,
            'id_grupo_produto',
            FILTER_VALIDATE_INT
        );

        if (!$id_grupo_produto || $id_grupo_produto < 1) {
            http_response_code(400);
            exit('O id do grupo de produto informado não é válido.');
        }

        $grupoProduto = $this->verificar();
        $grupoProduto->setIdGrupoProduto($id_grupo_produto);

        if (!Csrf::validateToken($_POST['csrf_token'])) {
            http_response_code(403);
            exit('O Token CSRF informado é inválido.');
        }

        if (!$grupoProduto->getDescricaoGrupo() || empty($grupoProduto->getDescricaoGrupo())) {
            http_response_code(400);
            exit('A descrição de um grupo de produto não pode ser vazia.');
        }

        try {
            $this->grupoProdutoDAO->editar($grupoProduto);
            header("Location: " . WWW . "html/matPat/listar_grupo_produto.php");
            exit();
        } catch (Exception $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());

            http_response_code(500);

            exit('Não foi possível editar o grupo de produto.');
        }
    }

    public function excluir()
    {
        header('Content-Type: application/json');

        if (!Csrf::validateToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Token CSRF inválido ou ausente."
            ]);

            exit;
        }

        $id_grupo_produto = filter_input(
            INPUT_POST,
            'id_grupo_produto',
            FILTER_VALIDATE_INT
        );

        if (!$id_grupo_produto || $id_grupo_produto < 1) {
            http_response_code(400);

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "O id do grupo de produto informado não é válido."
            ]);

            exit;
        }

        try {
            $this->grupoProdutoDAO->excluir($id_grupo_produto);
            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Grupo de produto excluído com sucesso."
            ]);
        } catch (Exception $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não foi possível excluir o grupo de produto."
            ]);
        }

        exit;
    }
}
