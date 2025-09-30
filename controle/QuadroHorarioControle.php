<?php
include_once '../dao/QuadroHorarioDAO.php';

class QuadroHorarioControle
{
    // Tipos

    public function listarTipo()
    {
        require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/quadro_horario/(listar_tipo_quadro_horario)\.php)$#';

        (new QuadroHorarioDAO())->listarTipos();

        preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
    }

    public function adicionarTipo()
    {
        $tipo = trim(filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS));
        $nextPage = trim(filter_input(INPUT_POST, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/quadro_horario/(adicionar_tipo_quadro_horario)\.php)$#';

        if (!$tipo || strlen($tipo) == 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'O tipo não pode ser vazio.']);
            exit();
        }

        if (session_status() === PHP_SESSION_NONE)
            session_start();

        try {
            $log = (new QuadroHorarioDAO())->adicionarTipo($tipo);
            $_SESSION['msg'] = $log;
        } catch (PDOException $e) {
            echo ('Erro ao adicionar tipo ' . htmlspecialchars($tipo) . ' ao banco de dados: ' . $e->getMessage());
            $_SESSION['msg'] = "Erro ao adicionar tipo: " . $e->getMessage();
            $_SESSION['flag'] = "erro";
        }

        $_SESSION['btnVoltar'] = true;

        if ($nextPage) {
            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
        }
    }

    public function removerTipo()
    {
        extract($_REQUEST);
        $log = (new QuadroHorarioDAO)->removerTipo($id);
        session_start();
        $_SESSION['msg'] = $log;
        header("Location: $nextPage");
    }

    // Escalas

    public function listarEscala()
    {
        require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/quadro_horario/(listar_escala)\.php)$#';

        (new QuadroHorarioDAO())->listarEscalas();

        preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
    }

    public function adicionarEscala()
    {
        $escala = trim(filter_input(INPUT_POST, 'escala', FILTER_SANITIZE_SPECIAL_CHARS));
        $nextPage = trim(filter_input(INPUT_POST, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/quadro_horario/(adicionar_escala)\.php)$#';

        if (!$escala || strlen($escala) == 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'A escala não pode ser vazia.']);
            exit();
        }

        if (session_status() === PHP_SESSION_NONE)
            session_start();

        try {
            $log = (new QuadroHorarioDAO())->adicionarEscala($escala);
            $_SESSION['msg'] = $log;
        } catch (PDOException $e) {
            echo ('Erro ao adicionar escala ' . htmlspecialchars($escala) . ' ao banco de dados: ' . $e->getMessage());
            $_SESSION['msg'] = "Erro ao adicionar escala: " . $e->getMessage();
            $_SESSION['flag'] = "erro";
        }
        $_SESSION['btnVoltar'] = true;

        if ($nextPage) {
            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
        }
    }

    public function removerEscala()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/quadro_horario/(listar_escala)\.php)$#';

        if (!$id || $id < 1) {
            http_response_code(400);
            echo json_encode(['erro' => 'O id da escala fornecido é inválido.']);
            exit();
        }

        $log = (new QuadroHorarioDAO)->removerEscala($id);

        if (session_status() === PHP_SESSION_NONE)
            session_start();

        $_SESSION['msg'] = $log;

        preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
    }
}
