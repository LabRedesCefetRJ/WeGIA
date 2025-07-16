<?php
include_once '../classes/Isaida.php';
include_once '../dao/IsaidaDAO.php';
include_once '../dao/SaidaDAO.php';

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
            error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}"); 
            http_response_code($e->getCode()); 
        }
    }
}