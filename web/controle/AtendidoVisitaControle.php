<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
include_once ROOT . '/dao/Conexao.php';
include_once ROOT . '/dao/VisitaDAO.php';
include_once ROOT . '/dao/VisitanteDAO.php';
require_once ROOT . '/classes/Util.php';
require_once ROOT . '/html/permissao/permissao.php';
require_once ROOT . '/classes/FichaRecepcao.php';

/**
 * Visitas vistas a partir do perfil do atendido (aba Visitas).
 *
 * Fica separado dos controles da Recepção porque a permissão é a do módulo Atendido (recurso 12): quem cuida do
 * atendido, como um assistente social, vê os visitantes dele sem precisar de acesso à Recepção.
 */
class AtendidoVisitaControle
{
    private const RECURSO_ATENDIDO = 12;
    private const ACAO_LER = 5;

    /**
     * Ficha (JSON) de um visitante do atendido. Só abre visitantes que já visitaram esse atendido, para que o acesso
     * ao módulo Atendido não exponha os dados de qualquer visitante da instituição.
     */
    public function fichaVisitante()
    {
        try {
            permissao($_SESSION['id_pessoa'], self::RECURSO_ATENDIDO, self::ACAO_LER);

            $opcoes = ['options' => ['min_range' => 1]];
            $idAtendido = filter_input(INPUT_GET, 'id_atendido', FILTER_VALIDATE_INT, $opcoes);
            $idVisitante = filter_input(INPUT_GET, 'id_visitante', FILTER_VALIDATE_INT, $opcoes);

            if (!$idAtendido)
                throw new InvalidArgumentException('O atendido informado é inválido.', 412);

            if (!$idVisitante)
                throw new InvalidArgumentException('O visitante informado é inválido.', 412);

            $pdo = Conexao::connect();
            if (!(new VisitaDAO($pdo))->atendidoRecebeuVisitante($idAtendido, $idVisitante))
                throw new InvalidArgumentException('Visitante não encontrado entre as visitas deste atendido.', 404);

            $dados = (new VisitanteDAO())->buscarDetalhes($idVisitante);
            if ($dados === null)
                throw new InvalidArgumentException('Visitante não encontrado.', 404);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(FichaRecepcao::visitante($dados), JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
