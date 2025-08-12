<?php
include_once '../classes/Ientrada.php';
include_once '../dao/IentradaDAO.php';
include_once '../dao/EntradaDAO.php';

class IentradaControle
{
    public function listarId(){
        extract($_REQUEST);
        try{
            $ientradaDAO = new IentradaDAO();
            $ientrada = $ientradaDAO->listarId($id_entrada);
            $entradaDAO = new EntradaDAO();
            $entrada = $entradaDAO->listarId($id_entrada);
            session_start();
            $_SESSION['ientrada'] = $ientrada;
            $_SESSION['entradaUnica'] = $entrada;
            header('Location: ' . $nextPage);
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage();
        }
    }
}