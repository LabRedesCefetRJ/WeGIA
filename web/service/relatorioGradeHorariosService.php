<?php

$baseDir = dirname(__DIR__);

require_once $baseDir . '/assets/vendor/setasign/fpdi/src/autoload.php';

spl_autoload_register(function ($class) use ($baseDir) {
    $map = [
        'Psr\\Log\\'               => $baseDir . '/assets/vendor/psr/log/src/',
        'Psr\\Http\\Message\\'     => $baseDir . '/assets/vendor/psr/http-message/src/',
        'Mpdf\\PsrLogAwareTrait\\' => $baseDir . '/assets/vendor/mpdf/psr-log-aware-trait/src/',
        'Mpdf\\Http\\Message\\'    => $baseDir . '/assets/vendor/mpdf/psr-http-message-shim/src/',
        'Mpdf\\'                   => $baseDir . '/assets/vendor/mpdf/mpdf/src/',
    ];

    foreach ($map as $prefix => $path) {
        if (strpos($class, $prefix) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $path . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

require_once $baseDir . '/assets/vendor/mpdf/mpdf/src/functions.php';

use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;

function e($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

require_once $baseDir . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'AgendaDAO.php';

try {
    $pdo = Conexao::connect();
    if (!$pdo) {
        throw new Exception("Não foi possível estabelecer conexão com o banco.");
    }

    $agendaDAO = new AgendaDAO($pdo);
    $idAgenda  = isset($_GET['id_agenda']) ? (int)$_GET['id_agenda'] : 0;
    $mesAlvo   = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
    $anoAlvo   = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

    // --- DEFINIÇÃO DA AGENDA ---
    if ($idAgenda <= 0) {
        $agendas = $agendaDAO->listarAgendas();
        if (empty($agendas)) {
            throw new Exception("Nenhuma agenda encontrada.");
        }
        $idAgenda   = $agendas[0]['id'];
        $nomeAgenda = e($agendas[0]['descricao']);
    } else {
        $agendaInfo = $agendaDAO->listarAgendaPorId($idAgenda);
        $nomeAgenda = $agendaInfo ? e($agendaInfo['descricao']) : "Agenda " . $idAgenda;
    }

    $logoHtml = '';

    $dadosImagem = $agendaDAO->obterLogo();

    if ($dadosImagem && !empty($dadosImagem['imagem'])) {

        $base64 = gzuncompress($dadosImagem['imagem']);

        $tipo = strtolower($dadosImagem['tipo'] ?? 'png');

        $logoHtml = '
            <img
                src="data:image/' . $tipo . ';base64,' . $base64 . '"
                style="height:45px;"
            />';
    }

    $alocacoes      = $agendaDAO->listarAlocacoesPorAgenda($idAgenda);
    $horariosEquipe = [];
    foreach ($alocacoes as $evento) {
        $idEq = $evento['id_equipe'];
        if (!isset($horariosEquipe[$idEq])) {
            $evStart               = new DateTime($evento['start']);
            $evEnd                 = new DateTime($evento['end']);
            $horariosEquipe[$idEq] = e($evStart->format('H:i') . " às " . $evEnd->format('H:i'));
        }
    }

    $equipes = $agendaDAO->listarEquipes($idAgenda);
    if (empty($equipes)) {
        throw new Exception("Nenhuma equipe vinculada a esta agenda.");
    }

    $dadosEquipes    = [];
    $legendasEquipes = [];
    foreach ($equipes as $eq) {
        $membros      = $agendaDAO->listarMembrosPorEquipe($eq['id']);
        $nomesMembros = array_map(function ($m) {
            $partesNome = explode(' ', $m['nome']);
            return e($partesNome[0]);
        }, $membros);

        $membrosStr        = !empty($nomesMembros) ? implode(', ', $nomesMembros) : 'Sem membros';
        $eq['membros_str'] = $membrosStr;
        $dadosEquipes[$eq['id']] = $eq;

        $nomeEquipeSafe = e($eq['nome']);
        $textoHorario   = isset($horariosEquipe[$eq['id']]) ? " <strong style='color:#007BFF;'>{$horariosEquipe[$eq['id']]}</strong>" : "";
        $legendasEquipes[] = "<strong style='color:#1a365d;'>{$nomeEquipeSafe}:</strong> <span style='color:#333333;'>({$membrosStr})</span>{$textoHorario}";
    }

    $numeroDias    = cal_days_in_month(CAL_GREGORIAN, $mesAlvo, $anoAlvo);
    $eventosPorDia = array_fill(1, $numeroDias, []);
    foreach ($alocacoes as $evento) {
        $evStart = new DateTime($evento['start']);
        if ($evStart->format('m') == $mesAlvo && $evStart->format('Y') == $anoAlvo) {
            $d = (int)$evStart->format('d');
            if ($d >= 1 && $d <= $numeroDias) {
                $idEq      = $evento['id_equipe'];
                $nomeEq    = isset($dadosEquipes[$idEq]) ? e($dadosEquipes[$idEq]['nome']) : 'Equipe';
                $membrosEq = isset($dadosEquipes[$idEq]) ? e($dadosEquipes[$idEq]['membros_str']) : '';

                $nomeEqLower = strtolower($nomeEq);
                if (strpos($nomeEqLower, 'noite') !== false || strpos($nomeEqLower, 'noturno') !== false) {
                    $rotuloTurno = 'NOTURNO';
                } elseif (strpos($nomeEqLower, 'dia') !== false || strpos($nomeEqLower, 'diurno') !== false) {
                    $rotuloTurno = 'DIURNO';
                } else {
                    $hora        = (int)$evStart->format('H');
                    $rotuloTurno = ($hora >= 17 || $hora < 5) ? 'NOTURNO' : 'DIURNO';
                }

                $eventosPorDia[$d][] = "
                    <div style='color:#000000; font-weight:bold; font-size: 8.5pt; line-height: 1.1;'>{$nomeEq}</div>
                    <div style='font-size: 4pt; line-height: 4pt;'>&nbsp;</div>
                    <div style='color:#333333; font-size: 6.5pt; line-height: 1.2;'>({$membrosEq}) - <strong style='color:#1a365d;'>{$rotuloTurno}</strong></div>
                ";
            }
        }
    }

    $primeiroDiaDoMes  = new DateTime("$anoAlvo-$mesAlvo-01");
    $diaDaSemanaInicio = (int)$primeiroDiaDoMes->format('w');
    
    // --- CONFIGURAÇÃO DO MPDF ---
    try {

        $mpdf = new Mpdf([
            'format'        => 'A4-L',
            'orientation'   => 'L',
            'margin_left'   => 6,
            'margin_right'  => 6,
            'margin_top'    => 6,
            'margin_bottom' => 6
        ]);

    } catch (\Throwable $e) {

        $tempDir = sys_get_temp_dir() . '/mpdf';

        if (!is_dir($tempDir) && !mkdir($tempDir, 0755, true)) {
            throw new Exception("Não foi possível criar o diretório temporário do mPDF.");
        }

        $mpdf = new Mpdf([
            'format'        => 'A4-L',
            'orientation'   => 'L',
            'margin_left'   => 6,
            'margin_right'  => 6,
            'margin_top'    => 6,
            'margin_bottom' => 6,
            'tempDir'       => $tempDir
        ]);
    }

    $css = "
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333333; } 
        h2 { margin: 0; font-size: 15pt; color: #333333; text-transform: uppercase; letter-spacing: 1px; } 
        h3 { margin: 3px 0 0 0; font-size: 11pt; color: #666666; font-weight: normal; } 
        .equipes-legenda { font-size: 7.5pt; background-color: #f5f5f5; padding: 4px; border: 1px solid #d9d9d9; border-radius: 4px; margin-bottom: 8px; text-align: center; line-height: 1.4; } 
        
        table.calendario { width: 100%; border-collapse: collapse; table-layout: fixed; } 
        
        table.calendario th { 
            background-color: #007BFF; 
            color: #ffffff; 
            font-weight: bold; 
            font-size: 9pt; 
            padding: 6px 4px; 
            width: 14.28%; 
            text-transform: uppercase; 
            border: 1px solid #007BFF; 
        } 
        
        table.calendario td { border: 1px solid #cfcfcf; height: 110px; vertical-align: top; padding: 5px; font-size: 7pt; } 
        
        .dia-numero { font-weight: bold; font-size: 8pt; display: block; margin-bottom: 6px; color: #444444; background-color: #eeeeee; padding: 2px 4px; border-radius: 2px; text-align: left; width: 14px; } 
        
        .fim-semana { background-color: #f0f7ff; } 
        .fim-semana .dia-numero { color: #0a58ca; background-color: #d1e7dd; } 
        
        .event-item { padding: 0; margin: 0; }
    ";

    $mpdf->WriteHTML($css, HTMLParserMode::HEADER_CSS);

    $mesesNomes = [
        1  => 'Janeiro',   2 => 'Fevereiro', 3 => 'Março',    4 => 'Abril',
        5  => 'Maio',      6 => 'Junho',     7 => 'Julho',    8 => 'Agosto',
        9  => 'Setembro', 10 => 'Outubro',  11 => 'Novembro', 12 => 'Dezembro'
    ];

    $html = "
        <table style='width: 100%; border-collapse: collapse; margin-bottom: 6px;'>
            <tr>
                <td style='width: 25%; text-align: left; vertical-align: middle;'>
                    {$logoHtml}
                </td>
                <td style='width: 50%; text-align: center; vertical-align: middle;'>
                    <h2>Agenda Mensal - {$nomeAgenda}</h2>
                    <h3>" . $mesesNomes[$mesAlvo] . " de " . e((string)$anoAlvo) . "</h3>
                </td>
                <td style='width: 25%;'></td>
            </tr>
        </table>

        <div class='equipes-legenda'>" . implode(' &nbsp;|&nbsp; ', $legendasEquipes) . "</div>
        <table class='calendario'>
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
    ";

    for ($i = 0; $i < $diaDaSemanaInicio; $i++) {
        $html .= "<td class='hoje-vazio'></td>";
    }

    $diaAtual           = 1;
    $diaDaSemanaRodando = $diaDaSemanaInicio;

    while ($diaAtual <= $numeroDias) {
        $html .= ($diaDaSemanaRodando == 0 || $diaDaSemanaRodando == 6) ? "<td class='fim-semana'>" : "<td>";
        $html .= "<span class='dia-numero'>{$diaAtual}</span>";

        if (!empty($eventosPorDia[$diaAtual])) {
            $totalEventos = count($eventosPorDia[$diaAtual]);
            foreach ($eventosPorDia[$diaAtual] as $index => $txtEvento) {
                $html .= "<div class='event-item'>{$txtEvento}</div>";
                if ($index < $totalEventos - 1) {
                    $html .= "<div style='font-size: 5pt; line-height: 5pt; border-bottom: 1px dashed #bdc3c7; margin-top: 2px;'>&nbsp;</div>";
                }
            }
        }

        $html .= "</td>";
        $diaAtual++;
        $diaDaSemanaRodando++;

        if ($diaDaSemanaRodando > 6 && $diaAtual <= $numeroDias) {
            $html .= "</tr><tr>";
            $diaDaSemanaRodando = 0;
        }
    }

    while ($diaDaSemanaRodando <= 6) {
        $html .= "<td class='hoje-vazio'></td>";
        $diaDaSemanaRodando++;
    }
    $html .= "</tr></tbody></table>";

    $usuario = isset($_SESSION['nome']) ? e($_SESSION['nome']) : 'Usuário';
    $html .= "
        <div style='margin-top: 25px; width: 100%;'>
            <table style='width:100%; border-collapse: collapse;'>
                <tr>
                    <td style='width:50%; text-align:left; font-size:9pt;'>
                        <strong>Feito por:</strong> {$usuario}
                    </td>
                    <td style='width:50%; text-align:center; font-size:9pt;'>
                        ___________________________________<br>Assinatura
                    </td>
                </tr>
            </table>
        </div>
    ";

    $mpdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

    $mpdf->Output("agenda_mensal_{$anoAlvo}_{$mesAlvo}.pdf", 'I');

} catch (Exception $e) {
    echo "
        <div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>
            <h1 style='color: #e74c3c;'>Erro ao gerar agenda</h1>
            <p style='color: #333;'>" . e($e->getMessage()) . "</p>
        </div>
    ";
}