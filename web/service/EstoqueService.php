<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'EstoqueDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'NotificacaoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Notificacao.php';

class EstoqueService
{
    private EstoqueDAO $estoqueDAO;
    private NotificacaoDAO $notificacaoDAO;

    private const RECURSO_MATERIAL_PATRIMONIO = 2;
    private const TIPO_ESTOQUE_BAIXO = 'estoque_baixo';

    public function __construct()
    {
        $this->estoqueDAO = new EstoqueDAO();
        $this->notificacaoDAO = new NotificacaoDAO();
    }

    public function verificarEstoqueMinimo(int $idProduto, int $idAlmoxarifado): void
    {
        $dados = $this->estoqueDAO->buscarDadosEstoqueMinimo($idProduto, $idAlmoxarifado);

        if (!$dados) {
            return;
        }

        $qtdAtual = (int) $dados['qtd'];
        $qtdMinima = (int) $dados['qtd_minima'];

        $link = 'html/matPat/limites_estoque_almoxarifado.php?id_almoxarifado='
                . $idAlmoxarifado
                . '&id_produto='
                . $idProduto;

        if ($qtdMinima <= 0 || $qtdAtual > $qtdMinima) {
            $this->notificacaoDAO->marcarPendentesComoVisualizadasPorReferencia(
                self::RECURSO_MATERIAL_PATRIMONIO,
                self::TIPO_ESTOQUE_BAIXO,
                $link
            );

            return;
        }

        $responsaveis = $this->estoqueDAO->buscarResponsaveisAlmoxarifado($idAlmoxarifado);

        foreach ($responsaveis as $idPessoa) {
            if ($this->notificacaoDAO->existePendente(
                (int) $idPessoa,
                self::RECURSO_MATERIAL_PATRIMONIO,
                self::TIPO_ESTOQUE_BAIXO,
                $link
            )) {
                continue;
            }

            $mensagem = sprintf(
                'O produto %s atingiu o estoque mínimo no almoxarifado "%s". Quantidade atual: %d. Quantidade mínima: %d.',
                $dados['produto'],
                $dados['almoxarifado'],
                $qtdAtual,
                $qtdMinima
            );

            $notificacao = new Notificacao(
                self::RECURSO_MATERIAL_PATRIMONIO,
                'Estoque baixo',
                $mensagem,
                self::TIPO_ESTOQUE_BAIXO,
                $link
            );

            $this->notificacaoDAO->criar($notificacao, [(int) $idPessoa]);
        }
    }
}