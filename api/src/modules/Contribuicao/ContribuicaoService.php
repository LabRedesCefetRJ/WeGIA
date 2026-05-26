<?php

namespace api\modules\Contribuicao;

class ContribuicaoService
{
    private ContribuicaoRepository $contribuicaoRepository;

    public function __construct(ContribuicaoRepository $contribuicaoRepository)
    {
        $this->contribuicaoRepository = $contribuicaoRepository;
    }

    /**
     * Get all contributions for a socio
     *
     * @param int $idSocio The socio ID
     * @return array Array of contributions
     */
    public function obterContribuicoesPorSocio(int $idSocio): array
    {
        return $this->contribuicaoRepository->findBySocioId($idSocio);
    }

    /**
     * Get contributions filtered by payment status
     *
     * @param int $idSocio The socio ID
     * @param bool|null $statusPagamento The payment status (null = all, true = paid, false = pending)
     * @return array Array of contributions
     */
    public function obterContribuicoesPorStatusPagamento(int $idSocio, ?bool $statusPagamento = null): array
    {
        return $this->contribuicaoRepository->findBySocioIdAndStatus($idSocio, $statusPagamento);
    }

    /**
     * Get contributions summary for a socio
     *
     * @param int $idSocio The socio ID
     * @return array Array containing summary data
     */
    public function obterResumoContribuicoes(int $idSocio): array
    {
        return $this->contribuicaoRepository->getSummaryBySocioId($idSocio);
    }

    /**
     * Format contributions for API response
     *
     * @param array $contribuicoes Array of contributions
     * @param bool $includeResume Whether to include summary data
     * @return array Formatted contributions
     */
    public function formatarContribuicoes(array $contribuicoes, bool $includeResume = false): array
    {
        $formatted = [
            'contribuicoes' => $this->formatarItems($contribuicoes)
        ];

        if ($includeResume && !empty($contribuicoes)) {
            $idSocio = (int) ($contribuicoes[0]['id_socio'] ?? 0);
            if ($idSocio > 0) {
                $formatted['resume'] = $this->obterResumoContribuicoes($idSocio);
            }
        }

        return $formatted;
    }

    /**
     * Format individual contribution items
     *
     * @param array $items Array of contribution items
     * @return array Formatted items
     */
    private function formatarItems(array $items): array
    {
        return array_map(function ($item) {
            return [
                'id' => (int)$item['id'],
                'codigo' => $item['codigo'],
                'valor' => (float)$item['valor'],
                'dataGeracao' => $item['dataGeracao'],
                'dataVencimento' => $item['dataVencimento'],
                'dataPagamento' => $item['dataPagamento'],
                'statusPagamento' => (bool)(int)$item['statusPagamento'],
                'plataforma' => $item['plataforma'] ?? null,
                'meioPagamento' => $item['meioPagamento'] ?? null
            ];
        }, $items);
    }
}
