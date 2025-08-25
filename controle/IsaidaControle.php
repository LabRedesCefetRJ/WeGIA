<?php
include_once '../classes/Isaida.php';
include_once '../dao/IsaidaDAO.php';
include_once '../dao/SaidaDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class IsaidaControle
{
    public function listarId(){
        extract($_REQUEST);
        try{
            $isaidaDAO = new IsaidaDAO();
            $isaida = $isaidaDAO->listarId($id_saida);
            $saidaDAO = new SaidaDAO();
            $saida = $saidaDAO->listarUmCompletoPorId($id_saida);
            session_start();
            $_SESSION['isaida'] = $isaida;
            $_SESSION['saidaUnica'] = $saida;
            header('Location: ' . $nextPage);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}