<?php

/**
 * Médias de visitas do relatório do módulo Recepção.
 *
 * Uma visita é uma entrada registrada (o grupo de visitantes e visitados que entrou junto), e não cada par
 * visitante × visitado gravado na tabela visita. Cada visita conta uma vez só:
 * - período por presença (padrão): no dia da entrada. A listagem também traz visitas que entraram antes do período
 *   e continuavam em andamento nele, mas essas não entram nos totais nem nas médias (ficam em entradas_anteriores);
 * - período por entrada ou por saída: todas as visitas listadas contam, porque o horário filtrado já está no período.
 *
 * Período considerado:
 * - início: a data inicial do filtro ou, sem ela, o dia da primeira entrada encontrada;
 * - fim: a data final do filtro ou, sem ela, hoje. Um fim no futuro é limitado a hoje, para não diluir a
 *   média com dias que ainda não aconteceram.
 * As médias semanal e mensal só são calculadas quando o período tem pelo menos uma semana / um mês, para não
 * extrapolar a partir de poucos dias.
 */
class EstatisticaVisitas
{
    /**
     * @param array $grupos Entradas devolvidas por VisitaDAO::listarHistorico.
     * @param array $filtros Filtros validados por VisitaControle::lerFiltrosHistorico.
     */
    public static function calcular(array $grupos, array $filtros, ?DateTimeImmutable $hoje = null): array
    {
        $hoje = ($hoje ?? new DateTimeImmutable('today'))->setTime(0, 0);

        $porPresenca = ($filtros['referencia'] ?? 'presenca') === 'presenca';
        $porSaida = ($filtros['referencia'] ?? '') === 'saida';

        $inicioFiltro = $porPresenca && !empty($filtros['data_inicio']) ? $filtros['data_inicio'] . ' 00:00:00' : null;
        $anteriores = $inicioFiltro === null ? [] : array_filter($grupos, fn($g) => $g['horario_entrada'] < $inicioFiltro);
        $grupos = array_values(array_filter($grupos, fn($g) => $inicioFiltro === null || $g['horario_entrada'] >= $inicioFiltro));

        $totalEntradas = count($grupos);
        $totalVisitantes = array_sum(array_map(fn($g) => count($g['visitantes']), $grupos));

        $resultado = [
            'total_entradas' => $totalEntradas,
            'total_visitantes' => $totalVisitantes,
            'entradas_anteriores' => count($anteriores),
            'inicio' => null,
            'fim' => null,
            'fim_limitado_a_hoje' => false,
            'dias' => 0,
            'media_diaria' => null,
            'media_semanal' => null,
            'media_mensal' => null,
        ];

        if (!empty($filtros['data_inicio'])) {
            $inicio = new DateTimeImmutable($filtros['data_inicio']);
        } elseif ($totalEntradas > 0) {
            // Sem data inicial, o período começa no primeiro horário usado pelo filtro (a saída, quando é por saída).
            $saidas = array_filter(array_map(fn($g) => $g['horario_saida'] ?? null, $grupos));
            $primeira = $porSaida && $saidas ? min($saidas) : min(array_map(fn($g) => $g['horario_entrada'], $grupos));
            $inicio = (new DateTimeImmutable($primeira))->setTime(0, 0);
        } else {
            return $resultado;
        }

        $fim = !empty($filtros['data_fim']) ? new DateTimeImmutable($filtros['data_fim']) : $hoje;
        if ($fim > $hoje) {
            $fim = $hoje;
            $resultado['fim_limitado_a_hoje'] = true;
        }

        $resultado['inicio'] = $inicio;
        $resultado['fim'] = $fim;

        if ($inicio > $fim) {
            // Período inteiro no futuro: ainda não há dias para calcular média.
            return $resultado;
        }

        $dias = $inicio->diff($fim)->days + 1;
        $resultado['dias'] = $dias;
        $resultado['media_diaria'] = $totalEntradas / $dias;

        if ($dias >= 7) {
            $resultado['media_semanal'] = $totalEntradas / ($dias / 7);
        }

        $meses = self::mesesDeCalendario($inicio, $fim);
        if ($meses >= 1 - 1e-9) {
            $resultado['media_mensal'] = $totalEntradas / $meses;
        }

        return $resultado;
    }

    /**
     * Quantidade de meses do período, contando cada mês de calendário pela fração dos seus dias que está
     * dentro do período (outubro inteiro = 1; de 16/10 a 15/11 = 16/31 + 15/30).
     */
    public static function mesesDeCalendario(DateTimeImmutable $inicio, DateTimeImmutable $fim): float
    {
        $meses = 0.0;
        $cursor = $inicio;

        while ($cursor <= $fim) {
            $ultimoDiaDoMes = $cursor->modify('last day of this month');
            $fimTrecho = $ultimoDiaDoMes < $fim ? $ultimoDiaDoMes : $fim;

            $diasNoTrecho = $cursor->diff($fimTrecho)->days + 1;
            $meses += $diasNoTrecho / (int) $cursor->format('t');

            $cursor = $ultimoDiaDoMes->modify('+1 day');
        }

        return $meses;
    }

    public static function formatarMedia(?float $media): ?string
    {
        return $media === null ? null : number_format($media, 2, ',', '.');
    }
}
