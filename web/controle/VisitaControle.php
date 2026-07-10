<?php
if (session_status() === PHP_SESSION_NONE)
session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
include_once ROOT . "/dao/Conexao.php";
include_once ROOT . '/classes/Visita.php';
include_once ROOT . '/dao/VisitaDAO.php';
require_once ROOT . '/classes/Util.php';

class VisitaControle
{
    public function verificarVisita()
    {
        extract($_REQUEST);

        $camposObrigatorios = ['idVisitante', 'idVisitado'];

        foreach ($camposObrigatorios as $campo) {
            if (!isset($$campo) || empty($$campo)) {
                http_response_code(412);
                header('Location: ../html/recepcao/registro_entrada.php?msg=O campo ' . $campo . ' é obrigatório.');
                exit();
            }
        }

        if (!filter_var($idVisitante, FILTER_VALIDATE_INT) || $idVisitante <= 0) {
            http_response_code(412);
            header('Location: ../html/recepcao/registro_entrada.php?msg=Visitante inválido.');
            exit();
        }

        if (!filter_var($idVisitado, FILTER_VALIDATE_INT) || $idVisitado <= 0) {
            http_response_code(412);
            header('Location: ../html/recepcao/registro_entrada.php?msg=Visitado inválido.');
            exit();
        }

        return new Visita($idVisitante, $idVisitado);
    }

    public function incluir()
    {
        try {
            $visita = $this->verificarVisita();

            if (!Csrf::validateToken($_POST['csrf_token']))
                throw new InvalidArgumentException('O Token CSRF informado é inválido.', 403);

            $visitaDAO = new VisitaDAO();
            $idVisita = $visitaDAO->incluir($visita);

            if (!isset($idVisita))
                throw new PDOException('Erro ao registrar a visita.', 500);

            $_SESSION['msg'] = "Visitante cadastrado com sucesso";
            $_SESSION['tipo'] = "success";

            header("Location: ../html/recepcao/pre_registro_entrada.php");
        }
        catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}