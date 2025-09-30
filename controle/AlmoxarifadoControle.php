<?php
include_once ROOT . '/classes/Almoxarifado.php';
include_once ROOT . '/dao/AlmoxarifadoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class AlmoxarifadoControle
{
    public function verificar()
    {
        $descricao_almoxarifado = trim($_POST['descricao_almoxarifado']);
        try {
            $almoxarifado = new Almoxarifado($descricao_almoxarifado);
            return $almoxarifado;
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            exit('Erro ao verificar almoxarifado: ' . $e->getMessage());
        }
    }
    public function listarTodos()
    {
        require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat|geral)/(editar_permissoes|cadastro_entrada|cadastro_saida|listar_almox|remover_produto)\.php(\?id_produto=\d+)?)$#';

        if (!filter_var($nextPage, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            exit('Erro, a URL informada para a próxima página não é válida.');
        }

        $almoxarifadoDAO = new AlmoxarifadoDAO();
        $almoxarifados = $almoxarifadoDAO->listarTodos();

        if (session_status() === PHP_SESSION_NONE)
            session_start();

        $_SESSION['almoxarifado'] = $almoxarifados;

        preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
    }
    public function incluir()
    {
        $almoxarifado = $this->verificar();
        $almoxarifadoDAO = new AlmoxarifadoDAO();
        try {
            $almoxarifadoDAO->incluir($almoxarifado);
            session_start();
            $_SESSION['msg'] = "Almoxarifado cadastrado com sucesso";
            $_SESSION['proxima'] = "Cadastrar outro almoxarifado";
            $_SESSION['link'] = WWW . "html/matPat/adicionar_almoxarifado.php";
            header("Location: " . WWW . "html/matPat/adicionar_almoxarifado.php");
        } catch (PDOException $e) {
            echo "Não foi possível registrar o almoxarifado";
        }
    }
    public function excluir()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null)) {
                http_response_code(403);
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 403);
            }

            $idAlmoxarifado = filter_input(INPUT_POST, 'id_almoxarifado', FILTER_SANITIZE_NUMBER_INT);

            if (!$idAlmoxarifado || !is_numeric($idAlmoxarifado) || $idAlmoxarifado < 1) {
                http_response_code(400);
                throw new InvalidArgumentException("O id de um almoxarifado deve ser um inteiro maior ou igual a 1", 400);
            }

            $almoxarifadoDAO = new AlmoxarifadoDAO();
            $almoxarifadoDAO->excluir($idAlmoxarifado);
            header('Location: ' . WWW . 'html/matPat/listar_almox.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
