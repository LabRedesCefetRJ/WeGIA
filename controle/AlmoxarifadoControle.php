<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Almoxarifado.php';
include_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'AlmoxarifadoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class AlmoxarifadoControle
{
    /**
     * Atribui para a chave 'almoxarifado' da sessão um array de todos os almoxarifados cadastrados no BD da aplicação.
     */
    public function listarTodos()
    {
        require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat|geral)/(editar_permissoes|cadastro_entrada|cadastro_saida|listar_almox|remover_produto)\.php(\?id_produto=\d+)?)$#';

        try {
            if (!filter_var($nextPage, FILTER_VALIDATE_URL))
                throw new InvalidArgumentException('Erro, a URL informada para a próxima página não é válida.', 400);

            $almoxarifadoDAO = new AlmoxarifadoDAO();
            $almoxarifados = $almoxarifadoDAO->listarTodos();

            $_SESSION['almoxarifado'] = $almoxarifados;

            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Extrai de um formulário HTTP via requisição POST o parâmetro 'descricao_almoxarifado' e cadastra no BD da aplicação um novo almoxarifado.
     */
    public function incluir()
    {
        try {
            $descricao_almoxarifado = filter_input(INPUT_POST, 'descricao_almoxarifado', FILTER_SANITIZE_SPECIAL_CHARS);
            $almoxarifado = new Almoxarifado($descricao_almoxarifado);

            $almoxarifadoDAO = new AlmoxarifadoDAO();
            $almoxarifadoDAO->incluir($almoxarifado);

            $_SESSION['msg'] = "Almoxarifado cadastrado com sucesso";
            $_SESSION['proxima'] = "Cadastrar outro almoxarifado";
            $_SESSION['link'] = WWW . "html/matPat/adicionar_almoxarifado.php";

            header("Location: " . WWW . "html/matPat/adicionar_almoxarifado.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Extrai de um formulário HTTP via requisição POST o parâmetro 'id_almoxarifado' e remove do sistema o almoxarifado de id equivalente no BD da aplicação.
     */
    public function excluir()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $idAlmoxarifado = filter_input(INPUT_POST, 'id_almoxarifado', FILTER_SANITIZE_NUMBER_INT);

            if (!$idAlmoxarifado || !is_numeric($idAlmoxarifado))
                throw new InvalidArgumentException("O parâmetro idAlmoxarifado deve ser um número válido.", 400);


            if ($idAlmoxarifado < 1)
                throw new InvalidArgumentException("O id de um almoxarifado deve ser um inteiro maior ou igual a 1.", 422);

            $almoxarifadoDAO = new AlmoxarifadoDAO();
            $almoxarifadoDAO->excluir($idAlmoxarifado);
            header('Location: ' . WWW . 'html/matPat/listar_almox.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    //Futuramente adicionar um método para alterar a descrição de um almoxarifado
}
