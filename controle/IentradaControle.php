<?php
include_once '../classes/Ientrada.php';
include_once '../dao/IentradaDAO.php';
include_once '../dao/EntradaDAO.php';

class IentradaControle
{
    public function listarId(){
        extract($_REQUEST);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        try{
            $ientradaDAO = new IentradaDAO();
            $entradaDAO = new EntradaDAO();
            
            if ($id_entrada === false || $id_entrada === null) {
                throw new Exception("ID de entrada inválido ou ausente.");
            } else {
                $ientrada = $ientradaDAO->listarId($id_entrada);
                $entrada = $entradaDAO->listarId($id_entrada);
            }
            $_SESSION['ientrada'] = $ientrada;
            $_SESSION['entradaUnica'] = $entrada;
            if (! strpos($nextPage, 'http')) {
                header('Location: ' . $nextPage);
            }
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage();
        }
    }
}