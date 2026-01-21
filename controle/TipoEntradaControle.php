<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
include_once ROOT . '/classes/TipoEntrada.php';
include_once ROOT . '/dao/TipoEntradaDAO.php';

class TipoEntradaControle
{
    public function verificar()
    {
        extract($_REQUEST);

        if ((!isset($descricao)) || (empty($descricao))) {
            $msg .= "Descricao do tipo de entrada não informada. Por favor, informe uma descrição!";
            header('Location: ' . WWW . 'html/tipoentrada.html?msg=' . $msg);
        } else {
            $tipoentrada = new TipoEntrada($descricao);
        }
        return $tipoentrada;
    }

    public function listarTodos()
    {
        try {
            $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
            $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(cadastro_entrada|listar_tipoEntrada)\.php)$#';
            if (!filter_var($nextPage, FILTER_VALIDATE_URL))
                throw new InvalidArgumentException('Erro, a URL informada para a próxima página não é válida.', 412);

            $tipoentradaDAO = new TipoEntradaDAO();
            $tipoentradas = $tipoentradaDAO->listarTodos();

            $_SESSION['tipo_entrada'] = $tipoentradas;

            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluir()
    {
        $tipoentrada = $this->verificar();
        $tipoentradaDAO = new TipoEntradaDAO();
        try {
            $tipoentradaDAO->incluir($tipoentrada);
            session_start();
            $_SESSION['msg'] = "Tipo de Entrada cadastrado com sucesso";
            $_SESSION['proxima'] = "Cadastrar outro TipoEntrada";
            $_SESSION['link'] = WWW . "html/matPat/adicionar_tipoEntrada.php";
            header("Location: " . WWW . "html/matPat/adicionar_tipoEntrada.php");
        } catch (PDOException $e) {
            $msg = "Não foi possível registrar o tipo" . "<br>" . $e->getMessage();
            echo $msg;
        }
    }

    public function excluir()
    {
        extract($_REQUEST);
        try {
            $tipoentradaDAO = new TipoEntradaDAO();
            $tipoentradaDAO->excluir($id_tipo);
            header('Location: ' . WWW . 'html/matPat/listar_tipoEntrada.php');
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage();
        }
    }
}
