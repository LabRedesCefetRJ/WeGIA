<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SaudeEquipePlantaoHistoricoDAO.php';

class SaudeEquipePlantaoHistoricoService
{
    private const MESES_PT_BR = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];

    private SaudeEquipePlantaoHistoricoDAO $dao;

    public function __construct(?SaudeEquipePlantaoHistoricoDAO $dao = null)
    {
        $this->dao = $dao ?? new SaudeEquipePlantaoHistoricoDAO();
    }

    public function listarHistoricoEscalasMensais(?int $limite = 120): array
    {
        $historico = $this->dao->listarHistoricoEscalasMensais($limite);

        return array_map(function (array $item): array {
            $mes = (int) ($item['mes'] ?? 0);
            $ano = (int) ($item['ano'] ?? 0);
            $mesNome = self::MESES_PT_BR[$mes] ?? '';

            return [
                'id_escala_mensal' => (int) ($item['id_escala_mensal'] ?? 0),
                'ano' => $ano,
                'mes' => $mes,
                'mes_nome' => $mesNome,
                'periodo_label' => sprintf('%02d/%04d', max($mes, 0), max($ano, 0)),
                'periodo_extenso' => $mesNome !== '' ? sprintf('%s de %04d', $mesNome, $ano) : sprintf('%02d/%04d', $mes, $ano),
                'status_label' => ((int) ($item['bloqueada'] ?? 0) === 1) ? 'Fechada' : 'Em edição',
                'bloqueada' => isset($item['bloqueada']) ? (int) $item['bloqueada'] : 0,
                'observacao' => $item['observacao'] ?? null,
                'data_criacao' => $item['data_criacao'] ?? null,
                'data_atualizacao' => $item['data_atualizacao'] ?? null,
                'quantidade_turnos_definidos' => (int) ($item['quantidade_turnos_definidos'] ?? 0),
                'quantidade_dias_com_escala' => (int) ($item['quantidade_dias_com_escala'] ?? 0)
            ];
        }, $historico);
    }
}
