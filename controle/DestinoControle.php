<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
include_once ROOT . '/classes/Destino.php';
include_once ROOT . '/dao/DestinoDAO.php';

class DestinoControle
{
    public function verificar()
    {
        extract($_REQUEST);
        if ((!isset($nome)) || (empty($nome))) {
            $msg = "Nome do destino nÃ£o informado. Por favor, informe um nome!";
            header('Location: ' . WWW . 'html/destino.html?msg=' . $msg);
        }
        if ((!isset($cnpj)) || (empty($cnpj))) {
            $cnpj = '';
        }
        if ((!isset($cpf)) || (empty($cpf))) {
            $cpf = '';
        }
        if ((!isset($telefone)) || (empty($telefone))) {
            $msg .= "Telefone do destino não informado. Por favor, informe um telefone!";
            header('Location: ../html/destino.html?msg=' . $msg);
        }

        $cpf = strlen($cpf) > 0 ? $cpf : null;

        $destino = new Destino($nome, $cnpj, $cpf, $telefone);
        return $destino;
    }

    public function listarTodos()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(cadastro_saida|listar_destino|remover_produto)\.php(\?id_produto=\d+)?)$#';

        try {
            if (!filter_var($nextPage, FILTER_VALIDATE_URL))
                throw new InvalidArgumentException('Erro, a URL informada para a próxima página não é válida.', 412);

            $destinoDAO = new DestinoDAO();
            $destinos = $destinoDAO->listarTodos();

            $_SESSION['destino'] = $destinos;

            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluir()
    {
        try {
            $destino = $this->verificar();
            $destinoDAO = new DestinoDAO();
            $destinoDAO->incluir($destino);

            $_SESSION['msg'] = "Destino cadastrado com sucesso";
            $_SESSION['proxima'] = "Cadastrar outro Destino";
            $_SESSION['link'] = WWW . "html/matPat/cadastro_destino.php";

            header("Location: " . WWW . "html/matPat/cadastro_destino.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
    public function excluir()
    {
        try {
            $id_destino = filter_var($_REQUEST['id_destino'], FILTER_SANITIZE_NUMBER_INT);

            if (!$id_destino || $id_destino < 1)
                throw new InvalidArgumentException('O id do tipo da saída não é válido.', 412);

            $destinoDAO = new DestinoDAO();
            $destinoDAO->excluir($id_destino);

            header('Location: ' . WWW . 'html/matPat/listar_destino.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
