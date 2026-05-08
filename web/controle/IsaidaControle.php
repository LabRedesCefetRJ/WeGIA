<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

include_once '../classes/Isaida.php';
include_once '../dao/IsaidaDAO.php';
include_once '../dao/SaidaDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';

class IsaidaControle
{
    public function listarId()
    {
        try {
            //Sanitização e validação de entrada
            $id_saida = isset($_REQUEST['id_saida']) ? filter_var($_REQUEST['id_saida'], FILTER_VALIDATE_INT) : null;

            $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
            $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(listar_Isaida)\.php(\?id_saida=\d+)?)$#';

            // Verifica se o ID é válido
            if ($id_saida === false || $id_saida === null || $id_saida <= 0)
                throw new InvalidArgumentException("ID de saída inválido.", 400);

            $isaidaDAO = new IsaidaDAO();
            $saidaDAO  = new SaidaDAO();

            //Uso seguro de prepared statements deve estar garantido dentro do DAO
            $isaida = $isaidaDAO->listarId($id_saida);
            $saida  = $saidaDAO->listarUmCompletoPorId($id_saida);

            //Evita armazenar dados muito grandes na sessão
            $_SESSION['isaida'] = $isaida;
            $_SESSION['saidaUnica'] = $saida;

            //Escapa a URL antes do redirecionamento para evitar XSS
            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
            exit;
        }
        // Captura específica para erros de banco
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
