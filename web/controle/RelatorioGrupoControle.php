<?php

require_once dirname(__FILE__, 2)
    . DIRECTORY_SEPARATOR . 'dao'
    . DIRECTORY_SEPARATOR . 'RelatorioGrupoDAO.php';

class RelatorioGrupoControle
{
    private RelatorioGrupoDAO $relatorioGrupoDAO;

    public function __construct()
    {
        $this->relatorioGrupoDAO = new RelatorioGrupoDAO();
    }

    public function gerar()
    {
        try {
            $idGrupo = filter_input(
                INPUT_POST,
                'grupo',
                FILTER_VALIDATE_INT
            );

            $idAlmoxarifado = filter_input(
                INPUT_POST,
                'almoxarifado',
                FILTER_VALIDATE_INT
            );

            $dataInicio = !empty($_POST['data_inicio'])
                ? $_POST['data_inicio']
                : null;

            $dataFim = !empty($_POST['data_fim'])
                ? $_POST['data_fim']
                : null;

            if (!$idGrupo || $idGrupo < 1) {
                throw new InvalidArgumentException(
                    'O grupo informado não é válido.',
                    400
                );
            }

            if (!$idAlmoxarifado || $idAlmoxarifado < 1) {
                throw new InvalidArgumentException(
                    'O almoxarifado informado não é válido.',
                    400
                );
            }

            if ($dataInicio !== null) {
                $this->validarData($dataInicio);
            }

            if ($dataFim !== null) {
                $this->validarData($dataFim);
            }

            if (
                $dataInicio !== null &&
                $dataFim !== null &&
                $dataInicio > $dataFim
            ) {
                throw new InvalidArgumentException(
                    'A data inicial não pode ser posterior à data final.',
                    400
                );
            }

            $nomeGrupo =
                $this->relatorioGrupoDAO->buscarNomeGrupo($idGrupo);

            $nomeAlmoxarifado =
                $this->relatorioGrupoDAO
                    ->buscarNomeAlmoxarifado($idAlmoxarifado);

            if ($nomeGrupo === null) {
                throw new InvalidArgumentException(
                    'Grupo não encontrado.',
                    404
                );
            }

            if ($nomeAlmoxarifado === null) {
                throw new InvalidArgumentException(
                    'Almoxarifado não encontrado.',
                    404
                );
            }

            $produtos =
                $this->relatorioGrupoDAO->buscarResumoProdutos(
                    $idGrupo,
                    $idAlmoxarifado,
                    $dataInicio,
                    $dataFim
                );

            $produtosPorCategoria =
                $this->agruparProdutosPorCategoria($produtos);

            $entradasPorProduto = [];

            $entradasDetalhadas =
                $this->relatorioGrupoDAO->buscarEntradasDetalhadas(
                    $idGrupo,
                    $idAlmoxarifado,
                    $dataInicio,
                    $dataFim
                );

            $entradasPorProduto =
                $this->agruparPorProduto($entradasDetalhadas);

            $saidasDetalhadas =
                $this->relatorioGrupoDAO->buscarSaidasDetalhadas(
                    $idGrupo,
                    $idAlmoxarifado,
                    $dataInicio,
                    $dataFim
                );

            $saidasPorProduto =
                $this->agruparPorProduto($saidasDetalhadas);

            $_SESSION['relatorio_grupo'] = [
                'produtos' => $produtos,
                'produtos_por_categoria' => $produtosPorCategoria,
                'entradas_por_produto' => $entradasPorProduto,
                'saidas_por_produto' => $saidasPorProduto,
                'nome_grupo' => $nomeGrupo,
                'nome_almoxarifado' => $nomeAlmoxarifado,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ];

            header(
                'Location: ' .
                WWW .
                'html/matPat/relatorio_geracao_grupo.php'
            );

            exit;

        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    private function agruparProdutosPorCategoria(array $produtos): array
    {
        $produtosPorCategoria = [];

        foreach ($produtos as $produto) {

            $categoria = !empty($produto['categoria'])
                ? $produto['categoria']
                : 'Sem categoria';

            if (!isset($produtosPorCategoria[$categoria])) {
                $produtosPorCategoria[$categoria] = [];
            }

            $produtosPorCategoria[$categoria][] = $produto;
        }

        return $produtosPorCategoria;
    }

    private function agruparPorProduto(array $movimentacoes): array
    {
        $movimentacoesPorProduto = [];

        foreach ($movimentacoes as $movimentacao) {

            $idProduto = (int) $movimentacao['id_produto'];

            if (!isset($movimentacoesPorProduto[$idProduto])) {
                $movimentacoesPorProduto[$idProduto] = [];
            }

            $movimentacoesPorProduto[$idProduto][] = $movimentacao;
        }

        return $movimentacoesPorProduto;
    }

    private function validarData(string $data): void
    {
        $objetoData = DateTime::createFromFormat(
            'Y-m-d',
            $data
        );

        if (
            !$objetoData ||
            $objetoData->format('Y-m-d') !== $data
        ) {
            throw new InvalidArgumentException(
                'A data informada não é válida.',
                400
            );
        }
    }
}