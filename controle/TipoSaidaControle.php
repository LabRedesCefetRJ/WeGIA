<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
include_once ROOT . '/classes/TipoSaida.php';
include_once ROOT . '/dao/TipoSaidaDAO.php';

class TipoSaidaControle
{
    public function verificar()
    {
        extract($_REQUEST);

        if ((!isset($descricao)) || (empty($descricao))) {
            $msg .= "Descrição do tipo de saida não informada. Por favor, informe uma descrição!";
            header('Location: ../html/tiposaida.html?msg=' . urlencode($msg));
            exit();
        } else {
            $tiposaida = new TipoSaida($descricao);
        }
        return $tiposaida;
    }

    public function listarTodos()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(cadastro_saida|listar_tipoSaida|remover_produto)\.php(\?id_produto=\d+)?)$#';

        try {
            if (!filter_var($nextPage, FILTER_VALIDATE_URL))
                throw new InvalidArgumentException('Erro, a URL informada para a próxima página não é válida.', 412);

            $tiposaidaDAO = new TipoSaidaDAO();
            $tiposaida = $tiposaidaDAO->listarTodos();

            $_SESSION['tipo_saida'] = $tiposaida;

            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function incluir()
    {
        try {
            $tiposaida = $this->verificar();
            $tiposaidaDAO = new TipoSaidaDAO();
            $tiposaidaDAO->incluir($tiposaida);

            $_SESSION['msg'] = "Tipo de Saida cadastrado com sucesso";
            $_SESSION['proxima'] = "Cadastrar outro TipoSaida";
            $_SESSION['link'] = WWW . "html/matPat/adicionar_tipoSaida.php";

            header("Location: " . WWW . "html/matPat/cadastro_saida.php");
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function excluir()
    {
        try {
            $id_tipo = filter_var($_REQUEST['id_tipo'], FILTER_SANITIZE_NUMBER_INT);

            if (!$id_tipo || $id_tipo < 1)
                throw new InvalidArgumentException('O id do tipo da saída não é válido.', 412);

            $tiposaidaDAO = new TipoSaidaDAO();
            $tiposaidaDAO->excluir($id_tipo);

            header('Location: ' . WWW . 'html/matPat/listar_tipoSaida.php');
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
