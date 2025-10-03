<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'QuadroHorarioDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class QuadroHorarioControle
{
    // Tipos

    public function listarTipo()
    {
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

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            if (!$tipo || strlen($tipo) == 0)
                throw new InvalidArgumentException('O tipo do quadro de horários não pode ser vazio.', 400);

            $log = (new QuadroHorarioDAO())->adicionarTipo($tipo);

            $log === TRUE ? $_SESSION['msg'] = sprintf("Tipo '%s' cadastrado com sucesso.", htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8')) : $_SESSION['msg'] = sprintf("O tipo '%s' já foi cadastrado.", htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'));
        } catch (Exception $e) {
            Util::tratarException($e);
            $e instanceof PDOException ? $_SESSION['msg'] = 'Erro no servidor ao manipular o banco de dados.' : $_SESSION['msg'] = "Erro ao adicionar tipo: " . $e->getMessage();
            $_SESSION['flag'] = "erro";
        }

        $_SESSION['btnVoltar'] = true;

        if ($nextPage)
            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
    }

    public function removerTipo()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id do tipo fornecido é inválido.', 422);

            $log = (new QuadroHorarioDAO)->removerTipo($id);

            $log === TRUE ? $_SESSION['msg'] = "Tipo removido com sucesso." : $_SESSION['msg'] = "Não é possível excluir um tipo ainda atribuído ao quadro horário de um funcionário.";
        } catch (Exception $e) {
            Util::tratarException($e);
            $e instanceof PDOException ? $_SESSION['msg'] = 'Erro no servidor ao manipular o banco de dados.' : $_SESSION['msg'] = "Erro ao remover tipo: " . $e->getMessage();
            $_SESSION['flag'] = "erro";
        }
    }

    // Escalas

    public function listarEscala()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/quadro_horario/(listar_escala)\.php)$#';

        (new QuadroHorarioDAO())->listarEscalas();

        preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
    }

    public function adicionarEscala()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $escala = trim(filter_input(INPUT_POST, 'escala', FILTER_SANITIZE_SPECIAL_CHARS));
            $nextPage = trim(filter_input(INPUT_POST, 'nextPage', FILTER_SANITIZE_URL));
            $regex = '#^((\.\./|' . WWW . ')html/quadro_horario/(adicionar_escala)\.php)$#';

            if (!$escala || strlen($escala) == 0)
                throw new InvalidArgumentException('A escala não pode ser vazia.', 400);

            $log = (new QuadroHorarioDAO())->adicionarEscala($escala);

            $log === TRUE ? $_SESSION['msg'] = sprintf("Escala '%s' cadastrada com sucesso.", htmlspecialchars($escala, ENT_QUOTES, 'UTF-8')) : $_SESSION['msg'] = sprintf(" A escala '%s' já existe no sistema.", htmlspecialchars($escala, ENT_QUOTES, 'UTF-8'));
        } catch (Exception $e) {
            Util::tratarException($e);
            $e instanceof PDOException ? $_SESSION['msg'] = 'Erro no servidor ao manipular o banco de dados.' : $_SESSION['msg'] = "Erro ao adicionar escala: " . $e->getMessage();
            $_SESSION['flag'] = "erro";
        }

        $_SESSION['btnVoltar'] = true;

        if ($nextPage)
            preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
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

        $_SESSION['msg'] = $log;

        preg_match($regex, $nextPage) ? header('Location:' . htmlspecialchars($nextPage)) : header('Location:' . '../html/home.php');
    }
}
