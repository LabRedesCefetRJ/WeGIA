<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'NotificacaoDAO.php';

class NotificacaoControle
{
    public function listarPorUsuario(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idPessoa = filter_var($_SESSION['id_pessoa'] ?? null, FILTER_VALIDATE_INT);

            if (!$idPessoa) {
                throw new InvalidArgumentException('Usuário inválido.');
            }

            $dao = new NotificacaoDAO();

            echo json_encode([
                'sucesso' => true,
                'dados' => $dao->listarPorUsuario($idPessoa)
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ]);
        }

        exit;
    }

    public function contarPendentes(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idPessoa = filter_var($_SESSION['id_pessoa'] ?? null, FILTER_VALIDATE_INT);

            if (!$idPessoa) {
                throw new InvalidArgumentException('Usuário inválido.');
            }

            $dao = new NotificacaoDAO();

            echo json_encode([
                'sucesso' => true,
                'total' => $dao->contarPendentes($idPessoa)
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ]);
        }

        exit;
    }

    public function marcarComoVisualizada(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idPessoa = filter_var($_SESSION['id_pessoa'] ?? null, FILTER_VALIDATE_INT);
            $idNotificacao = filter_input(INPUT_POST, 'id_notificacao', FILTER_VALIDATE_INT);

            if (!$idPessoa || !$idNotificacao) {
                throw new InvalidArgumentException('Dados inválidos.');
            }

            $dao = new NotificacaoDAO();
            $dao->marcarComoVisualizada($idNotificacao, $idPessoa);

            echo json_encode(['sucesso' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ]);
        }

        exit;
    }

    public function marcarTodasComoVisualizadas(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idPessoa = filter_var($_SESSION['id_pessoa'] ?? null, FILTER_VALIDATE_INT);

            if (!$idPessoa) {
                throw new InvalidArgumentException('Usuário inválido.');
            }

            $dao = new NotificacaoDAO();
            $dao->marcarTodasComoVisualizadas($idPessoa);

            echo json_encode(['sucesso' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ]);
        }

        exit;
    }
}