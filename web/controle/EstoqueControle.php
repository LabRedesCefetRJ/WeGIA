<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

include_once '../classes/Estoque.php';
include_once '../dao/EstoqueDAO.php';

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'EstoqueService.php';

class EstoqueControle
{
    public function listarTodos()
    {
        $nextPage = trim(filter_input(INPUT_GET, 'nextPage', FILTER_SANITIZE_URL));
        $regex = '#^((\.\./|' . WWW . ')html/(matPat)/(estoque)\.php)$#';

        try {
            $estoqueDAO = new EstoqueDAO();
            $estoques = $estoqueDAO->listarTodos();
            $_SESSION['estoque'] = $estoques;

            preg_match($regex, $nextPage) ? header('Location: ' . htmlspecialchars($nextPage, ENT_QUOTES, 'UTF-8')) : header('Location: ' . WWW . 'html/home.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao listar estoques: " . $e->getMessage());
            echo "Erro ao acessar os dados de estoque. Tente novamente mais tarde.";
        }
    }

    public function listarProdutosPorAlmoxarifadoComLimite()
    {
        try {
            $idAlmoxarifado = filter_input(INPUT_GET, 'id_almoxarifado', FILTER_VALIDATE_INT);

            if (!$idAlmoxarifado || $idAlmoxarifado < 1) {
                throw new InvalidArgumentException('Almoxarifado inválido.');
            }

            header('Location: ' . WWW . 'html/matPat/limites_estoque_almoxarifado.php?id_almoxarifado=' . $idAlmoxarifado);
            exit;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function atualizarQuantidadeMinima()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idProduto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);
            $idAlmoxarifado = filter_input(INPUT_POST, 'id_almoxarifado', FILTER_VALIDATE_INT);
            $qtdMinima = filter_input(INPUT_POST, 'qtd_minima', FILTER_VALIDATE_INT);

            if (!$idProduto || !$idAlmoxarifado || $qtdMinima === false || $qtdMinima < 0) {
                throw new InvalidArgumentException('Dados inválidos.');
            }

            $dao = new EstoqueDAO();
            $dao->atualizarQuantidadeMinima($idProduto, $idAlmoxarifado, $qtdMinima);

            $service = new EstoqueService();
            $service->verificarEstoqueMinimo($idProduto, $idAlmoxarifado);

            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Quantidade mínima atualizada com sucesso.'
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
}
