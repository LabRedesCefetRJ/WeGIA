<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

include_once '../classes/Ientrada.php';
include_once '../dao/IentradaDAO.php';
include_once '../dao/EntradaDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';

class IentradaControle
{
    public function listarId()
    {
        try {
            //Sanitização e validação de entrada
            $id_entrada = isset($_REQUEST['id_entrada']) ? filter_var($_REQUEST['id_entrada'], FILTER_VALIDATE_INT) : null;

            $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
            $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(listar_Ientrada)\.php(\?id_entrada=\d+)?)$#';

            // Verifica se o ID é válido
            if ($id_entrada === false || $id_entrada === null || $id_entrada <= 0)
                throw new InvalidArgumentException("ID de entrada inválido.", 400);

            $ientradaDAO = new IentradaDAO();
            $entradaDAO  = new EntradaDAO();

            //Uso seguro de prepared statements deve estar garantido dentro do DAO
            $ientrada = $ientradaDAO->listarId($id_entrada);
            $entrada  = $entradaDAO->listarId($id_entrada);

            //Evita armazenar dados muito grandes na sessão
            $_SESSION['ientrada'] = $ientrada;
            $_SESSION['entradaUnica'] = $entrada;

            //Escapa a URL antes do redirecionamento para evitar XSS
            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . WWW . 'html/home.php');
            exit;
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}