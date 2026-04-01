<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'personalizacao_display.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'SaudeEquipePlantaoService.php';

permissao($_SESSION['id_pessoa'], 5, 5);

$ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: (int) date('Y');
$mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT) ?: (int) date('m');
$formato = (string) (filter_input(INPUT_GET, 'formato', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'calendario');
$autoPrint = filter_input(INPUT_GET, 'auto_print', FILTER_VALIDATE_INT) === 1;

if (!in_array($formato, ['calendario', 'tabela'], true)) {
    $formato = 'calendario';
}

$servicePlantao = new SaudeEquipePlantaoService();
$escala = $servicePlantao->listarEscalaMensal($ano, $mes);
$equipes = $servicePlantao->listarEquipes();
$turnos = ['DIA', 'NOITE'];
$meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
    7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
$mesNome = $meses[$mes] ?? (string) $mes;
$totalDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

function turnoLabelPrint(string $turno): string
{
    return strtoupper($turno) === 'NOITE' ? 'Noite' : 'Dia';
}

function turnoFaixaPrint(string $turno): string
{
    return strtoupper($turno) === 'NOITE' ? '19:00 às 07:00' : '07:00 às 19:00';
}

$mapaDias = [];
for ($dia = 1; $dia <= $totalDiasMes; $dia++) {
    $data = DateTime::createFromFormat('Y-n-j', sprintf('%04d-%d-%d', $ano, $mes, $dia));
    $diasSemana = [1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado', 7 => 'Domingo'];
    $mapaDias[$dia] = [
        'dia' => $dia,
        'nome_dia_semana' => $diasSemana[(int) ($data ? $data->format('N') : 0)] ?? '',
        'turnos' => [
            'DIA' => [
                'turno' => 'DIA',
                'turno_label' => 'Plantão do dia',
                'faixa_horario' => turnoFaixaPrint('DIA'),
                'id_equipe_plantao' => null,
                'equipe_nome' => 'Não definida',
                'equipe_ativa' => null,
                'observacao' => '',
                'membros_plantao' => []
            ],
            'NOITE' => [
                'turno' => 'NOITE',
                'turno_label' => 'Plantão da noite',
                'faixa_horario' => turnoFaixaPrint('NOITE'),
                'id_equipe_plantao' => null,
                'equipe_nome' => 'Não definida',
                'equipe_ativa' => null,
                'observacao' => '',
                'membros_plantao' => []
            ]
        ]
    ];
}

foreach ($escala['dias'] ?? [] as $diaEscala) {
    $dia = (int) ($diaEscala['dia'] ?? 0);
    if ($dia < 1 || $dia > $totalDiasMes) {
        continue;
    }

    foreach ($turnos as $turno) {
        $dadosTurno = $diaEscala['turnos'][$turno] ?? null;
        if (!$dadosTurno) {
            continue;
        }

        $equipeAtiva = isset($dadosTurno['equipe_ativa']) ? (int) $dadosTurno['equipe_ativa'] : 1;
        if (!empty($dadosTurno['id_equipe_plantao']) && $equipeAtiva !== 1) {
            $dadosTurno['id_equipe_plantao'] = null;
            $dadosTurno['equipe_nome'] = 'Não definida';
            $dadosTurno['membros_plantao'] = [];
        }

        $mapaDias[$dia]['turnos'][$turno] = array_merge($mapaDias[$dia]['turnos'][$turno], $dadosTurno);
    }
}

function embaralharPaletaDeterministica(array $palette, int $seed): array
{
    $value = $seed % 2147483647;
    if ($value <= 0) {
        $value += 2147483646;
    }

    for ($index = count($palette) - 1; $index > 0; $index--) {
        $value = ($value * 16807) % 2147483647;
        $random = ($value - 1) / 2147483646;
        $swapIndex = (int) floor($random * ($index + 1));
        [$palette[$index], $palette[$swapIndex]] = [$palette[$swapIndex], $palette[$index]];
    }

    return $palette;
}

function mapaCorEquipes(array $equipes, int $ano, int $mes): array
{
    $palette = embaralharPaletaDeterministica(['#f57c00', '#6a1b9a', '#2e7d32', '#1e3a8a'], ($ano * 100) + $mes + max(count($equipes), 1));
    $ids = array_map(static function ($equipe) {
        return (int) ($equipe['id_equipe_plantao'] ?? 0);
    }, $equipes);

    $ids = array_values(array_filter($ids, static function ($id) {
        return $id > 0;
    }));
    sort($ids);

    $mapa = [];
    foreach ($ids as $index => $idEquipe) {
        $mapa[$idEquipe] = $palette[$index % count($palette)];
    }

    return $mapa;
}

function corTextoEquipe(string $background): string
{
    $hex = ltrim($background, '#');
    if (strlen($hex) !== 6) {
        return '#ffffff';
    }

    $red = hexdec(substr($hex, 0, 2));
    $green = hexdec(substr($hex, 2, 2));
    $blue = hexdec(substr($hex, 4, 2));
    $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

    return $luminance >= 150 ? '#25384a' : '#ffffff';
}

$teamColorMap = mapaCorEquipes($equipes, $ano, $mes);
$equipesUsadas = [];
$preenchidos = 0;
$totalPlantaoMes = $totalDiasMes * 2;

foreach ($mapaDias as $infoDia) {
    foreach ($turnos as $turno) {
        $dadosTurno = $infoDia['turnos'][$turno] ?? [];
        $idEquipe = (int) ($dadosTurno['id_equipe_plantao'] ?? 0);
        if ($idEquipe <= 0) {
            continue;
        }

        $preenchidos++;
        $cor = $teamColorMap[$idEquipe] ?? '#f57c00';
        if (!isset($equipesUsadas[$idEquipe])) {
            $equipesUsadas[$idEquipe] = [
                'nome' => $dadosTurno['equipe_nome'] ?? ('Equipe #' . $idEquipe),
                'cor' => $cor,
                'plantoes' => 0
            ];
        }

        $equipesUsadas[$idEquipe]['plantoes']++;
    }
}

$pendentes = $totalPlantaoMes - $preenchidos;
$emissao = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('d/m/Y H:i:s');

ob_start();
display_campo('Logo', 'file');
$logoPath = trim(ob_get_clean());
$primeiroDiaSemana = (int) date('w', strtotime(sprintf('%04d-%02d-01', $ano, $mes)));
$colunaAtual = 0;
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Impressão da Escala de Plantão</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="./style/equipe_plantao_print.css" />
</head>
<body>
<div class="print-wrap">
    <div class="print-toolbar">
        <div class="left">
            <button type="button" onclick="window.print()">Imprimir</button>
            <button type="button" onclick="window.close()">Fechar</button>
        </div>
        <div class="right">
            <span class="meta">Período: <?php echo htmlspecialchars($mesNome . '/' . $ano, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>

    <header class="print-title">
        <div>
            <h1>Escala Mensal de Plantão</h1>
            <p>Módulo Saúde - Planejamento Operacional</p>
            <p>Período: <?php echo htmlspecialchars($mesNome . '/' . $ano, ENT_QUOTES, 'UTF-8'); ?> | Emissão: <?php echo htmlspecialchars($emissao, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <?php if ($logoPath !== ''): ?>
            <img src="<?php echo htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" class="logo-box">
        <?php endif; ?>
    </header>

    <section class="info-grid">
        <div class="info-card">
            <div class="label">Dias do mês</div>
            <div class="value"><?php echo (int) $totalDiasMes; ?></div>
        </div>
        <div class="info-card">
            <div class="label">Plantões preenchidos</div>
            <div class="value"><?php echo (int) $preenchidos; ?></div>
        </div>
        <div class="info-card">
            <div class="label">Plantões pendentes</div>
            <div class="value"><?php echo (int) $pendentes; ?></div>
        </div>
        <div class="info-card">
            <div class="label">Equipes utilizadas</div>
            <div class="value"><?php echo (int) count($equipesUsadas); ?></div>
        </div>
    </section>

    <?php if (!empty($escala['observacao'])): ?>
        <section class="info-card" style="margin-bottom: 12px;">
            <div class="label">Observação geral da escala</div>
            <div class="value" style="font-size: 13px; font-weight: 500;"><?php echo htmlspecialchars((string) $escala['observacao'], ENT_QUOTES, 'UTF-8'); ?></div>
        </section>
    <?php endif; ?>

    <section class="legend">
        <?php if (empty($equipesUsadas)): ?>
            <span class="legend-item">Nenhuma equipe definida no período.</span>
        <?php else: ?>
            <?php foreach ($equipesUsadas as $dadosEquipe): ?>
                <span class="legend-item">
                    <span class="legend-dot" style="background: <?php echo htmlspecialchars($dadosEquipe['cor'], ENT_QUOTES, 'UTF-8'); ?>;"></span>
                    <?php echo htmlspecialchars($dadosEquipe['nome'], ENT_QUOTES, 'UTF-8'); ?>
                    (<?php echo (int) $dadosEquipe['plantoes']; ?> plantões)
                </span>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <?php if ($formato === 'calendario'): ?>
        <table class="calendar-grid">
            <thead>
            <tr>
                <th>Domingo</th>
                <th>Segunda</th>
                <th>Terça</th>
                <th>Quarta</th>
                <th>Quinta</th>
                <th>Sexta</th>
                <th>Sábado</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php for (; $colunaAtual < $primeiroDiaSemana; $colunaAtual++): ?>
                    <td><div class="day-cell empty-day"></div></td>
                <?php endfor; ?>

                <?php foreach ($mapaDias as $dia => $infoDia): ?>
                    <td>
                        <div class="day-cell">
                            <div class="day-number"><?php echo (int) $dia; ?></div>
                            <?php foreach ($turnos as $turno): ?>
                                <?php
                                $dadosTurno = $infoDia['turnos'][$turno];
                                $idEquipe = (int) ($dadosTurno['id_equipe_plantao'] ?? 0);
                                $corEquipe = $idEquipe > 0 ? ($teamColorMap[$idEquipe] ?? '#f57c00') : '#d7dde4';
                                $corTextoEquipe = $idEquipe > 0 ? corTextoEquipe($corEquipe) : '#536677';
                                $membros = $dadosTurno['membros_plantao'] ?? [];
                                $membrosTexto = empty($membros) ? '' : implode(', ', array_column($membros, 'nome_completo'));
                                ?>
                                <div class="print-shift-block">
                                    <div class="print-shift-header"><?php echo htmlspecialchars(turnoLabelPrint($turno), ENT_QUOTES, 'UTF-8'); ?> <span><?php echo htmlspecialchars(turnoFaixaPrint($turno), ENT_QUOTES, 'UTF-8'); ?></span></div>
                                    <span class="day-team" style="background: <?php echo htmlspecialchars($corEquipe, ENT_QUOTES, 'UTF-8'); ?>; color: <?php echo htmlspecialchars($corTextoEquipe, ENT_QUOTES, 'UTF-8'); ?>;">
                                        <?php echo htmlspecialchars((string) ($dadosTurno['equipe_nome'] ?? 'Não definida'), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <div class="day-members"><?php echo $membrosTexto !== '' ? htmlspecialchars($membrosTexto, ENT_QUOTES, 'UTF-8') : 'Sem membros definidos'; ?></div>
                                    <?php if (!empty($dadosTurno['observacao'])): ?>
                                        <div class="day-note"><?php echo htmlspecialchars((string) $dadosTurno['observacao'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <?php
                    $colunaAtual++;
                    if ($colunaAtual === 7) {
                        echo '</tr>';
                        if ((int) $dia < $totalDiasMes) {
                            echo '<tr>';
                        }
                        $colunaAtual = 0;
                    }
                    ?>
                <?php endforeach; ?>

                <?php if ($colunaAtual > 0 && $colunaAtual < 7): ?>
                    <?php for (; $colunaAtual < 7; $colunaAtual++): ?>
                        <td><div class="day-cell empty-day"></div></td>
                    <?php endfor; ?>
                <?php endif; ?>
            </tr>
            </tbody>
        </table>
    <?php else: ?>
        <table class="scale-table">
            <thead>
            <tr>
                <th>Dia</th>
                <th>Semana</th>
                <th>Turno</th>
                <th>Horário</th>
                <th>Equipe responsável</th>
                <th>Técnicos escalados</th>
                <th>Observação</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($mapaDias as $dia => $infoDia): ?>
                <?php foreach ($turnos as $turno): ?>
                    <?php
                    $dadosTurno = $infoDia['turnos'][$turno];
                    $membros = $dadosTurno['membros_plantao'] ?? [];
                    $membrosTexto = empty($membros) ? '-' : implode(', ', array_column($membros, 'nome_completo'));
                    ?>
                    <tr>
                        <td><?php echo (int) $dia; ?></td>
                        <td><?php echo htmlspecialchars((string) ($infoDia['nome_dia_semana'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(turnoLabelPrint($turno), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(turnoFaixaPrint($turno), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string) ($dadosTurno['equipe_nome'] ?? 'Não definida'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($membrosTexto, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string) ($dadosTurno['observacao'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <section class="signature-area">
        <div class="signature-line">Responsável técnico</div>
        <div class="signature-line">Conferência / supervisão</div>
    </section>
</div>

<?php if ($autoPrint): ?>
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
<?php endif; ?>
</body>
</html>
