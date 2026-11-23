<?php
/**
 * Relatório de visitas do módulo Recepção em PDF (mPDF).
 *
 * Recebe por GET os mesmos filtros do histórico de visitas (data_inicio, data_fim, id_visitado, categoria,
 * situacao), validados por VisitaControle::lerFiltrosHistorico.
 */
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'sessaoCookie.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

$baseDir = dirname(__DIR__);

require_once $baseDir . DIRECTORY_SEPARATOR . 'config.php';
require_once $baseDir . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

// Mesmo fuso do resto do sistema (o control.php faz o mesmo), para o "gerado em" e as contas de "hoje" baterem.
Util::definirFusoHorario();

if (!isset($_SESSION['usuario'], $_SESSION['id_pessoa'])) {
    header('Location: ' . WWW . 'index.php');
    exit();
}

require_once $baseDir . '/html/permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 20, 5);

require_once $baseDir . '/controle/VisitaControle.php';
require_once $baseDir . '/dao/VisitaDAO.php';

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
            $file = $path . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

require_once $baseDir . '/assets/vendor/mpdf/mpdf/src/functions.php';

use Mpdf\Mpdf;

function relatorioVisitasEscapar($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function relatorioVisitasData(?string $dataHora, string $formato): string
{
    return $dataHora ? (new DateTimeImmutable($dataHora))->format($formato) : '';
}

/** Nomes de uma lista de visitantes/visitados, um por linha, com a categoria do visitado entre parênteses. */
function relatorioVisitasNomes(array $itens, array $rotulosCategoria): string
{
    $linhas = array_map(function ($item) use ($rotulosCategoria) {
        $nome = relatorioVisitasEscapar(trim(($item['nome'] ?? '') . ' ' . ($item['sobrenome'] ?? '')));
        $categoria = $item['categoria'] ?? null;

        return isset($rotulosCategoria[$categoria])
            ? $nome . ' <span class="categoria">(' . relatorioVisitasEscapar($rotulosCategoria[$categoria]) . ')</span>'
            : $nome;
    }, $itens);

    return implode('<br>', $linhas);
}

/** Intervalo do filtro em forma curta ("21/10 a 19/11/2026"), para o quadro do período selecionado. */
function relatorioVisitasIntervaloCurto(array $filtros): string
{
    $inicio = !empty($filtros['data_inicio']) ? new DateTimeImmutable($filtros['data_inicio']) : null;
    $fim = !empty($filtros['data_fim']) ? new DateTimeImmutable($filtros['data_fim']) : null;

    if ($inicio && $fim) {
        if ($inicio == $fim) {
            return $inicio->format('d/m/Y');
        }
        return $inicio->format($inicio->format('Y') === $fim->format('Y') ? 'd/m' : 'd/m/Y') . ' a ' . $fim->format('d/m/Y');
    }
    if ($inicio) {
        return 'A partir de ' . $inicio->format('d/m/Y');
    }
    if ($fim) {
        return 'Até ' . $fim->format('d/m/Y');
    }
    return 'Sem limite de datas';
}

function relatorioVisitasLogo(PDO $pdo): string
{
    $stmt = $pdo->prepare("SELECT imagem.imagem, imagem.tipo
                           FROM imagem, campo_imagem, tabela_imagem_campo
                           WHERE campo_imagem.id_campo = tabela_imagem_campo.id_campo
                             AND imagem.id_imagem = tabela_imagem_campo.id_imagem
                             AND campo_imagem.nome_campo = 'Logo'");
    $stmt->execute();
    $logo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$logo || empty($logo['imagem'])) {
        return '';
    }

    $base64 = @gzuncompress($logo['imagem']);
    if ($base64 === false) {
        return '';
    }

    $tipo = preg_replace('/[^a-z0-9+]/', '', strtolower($logo['tipo'] ?? 'png')) ?: 'png';

    return '<img src="data:image/' . $tipo . ';base64,' . $base64 . '" style="height:38px;">';
}

try {
    $filtros = VisitaControle::lerFiltrosHistorico($_GET);

    $pdo = Conexao::connect();
    $visitaDAO = new VisitaDAO($pdo);
    $grupos = $visitaDAO->listarHistorico($filtros);

    $rotulosCategoria = [
        'atendido' => 'Atendido',
        'funcionario' => 'Funcionário',
        'voluntario' => 'Voluntário',
        'pessoa' => 'Pessoa',
        'pet' => 'Pet',
        'setor' => 'Setor',
        'instituicao' => 'Instituição',
    ];

    // Resumo dos filtros
    $periodo = 'Todo o histórico';
    if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
        $periodo = $filtros['data_inicio'] === $filtros['data_fim']
            ? relatorioVisitasData($filtros['data_inicio'], 'd/m/Y')
            : 'De ' . relatorioVisitasData($filtros['data_inicio'], 'd/m/Y') . ' até ' . relatorioVisitasData($filtros['data_fim'], 'd/m/Y');
    } elseif (!empty($filtros['data_inicio'])) {
        $periodo = 'A partir de ' . relatorioVisitasData($filtros['data_inicio'], 'd/m/Y');
    } elseif (!empty($filtros['data_fim'])) {
        $periodo = 'Até ' . relatorioVisitasData($filtros['data_fim'], 'd/m/Y');
    }

    $referencia = $filtros['referencia'] ?? VisitaDAO::REFERENCIA_PRESENCA;
    if (!empty($filtros['data_inicio']) || !empty($filtros['data_fim'])) {
        $periodo .= [
            VisitaDAO::REFERENCIA_PRESENCA => ' (visitas em andamento no período)',
            VisitaDAO::REFERENCIA_ENTRADA => ' (pela data de entrada)',
            VisitaDAO::REFERENCIA_SAIDA => ' (pela data de saída)',
        ][$referencia];
    }

    $visitadoFiltro = 'Todos';
    if (!empty($filtros['id_visitado'])) {
        $visitadoFiltro = 'Visitado não encontrado';
        foreach ($visitaDAO->listarVisitadosComVisitas() as $visitado) {
            if ($visitado['id_visitado'] === (int) $filtros['id_visitado']) {
                $visitadoFiltro = $visitado['nome'] . ' (' . ($rotulosCategoria[$visitado['categoria']] ?? '') . ')';
                break;
            }
        }
    }

    $categoriaFiltro = isset($filtros['categoria']) ? $rotulosCategoria[$filtros['categoria']] : 'Todos';
    $situacaoFiltro = [
        VisitaDAO::SITUACAO_EM_ANDAMENTO => 'Em andamento',
        VisitaDAO::SITUACAO_ENCERRADA => 'Encerradas',
    ][$filtros['situacao'] ?? ''] ?? 'Todas';

    // Usuário que gerou
    $stmtUsuario = $pdo->prepare("SELECT TRIM(CONCAT(nome, ' ', COALESCE(sobrenome, ''))) FROM pessoa WHERE id_pessoa = :id");
    $stmtUsuario->bindValue(':id', (int) $_SESSION['id_pessoa'], PDO::PARAM_INT);
    $stmtUsuario->execute();
    $usuario = $stmtUsuario->fetchColumn() ?: 'Usuário';

    $nomeInstituicao = trim((string) SelecaoParagrafoDAO::getSelecao(SelecaoParagrafo::Titulo));
    $geradoEm = new DateTimeImmutable();

    // Quantidade de visitas: o período escolhido e janelas fixas até hoje, com os demais filtros iguais.
    // Cada quadro conta o mesmo que a tabela mostraria com aquele período.
    $hoje = new DateTimeImmutable('today');
    $janelas = [
        [
            'titulo' => (!empty($filtros['data_inicio']) || !empty($filtros['data_fim'])) ? 'Período selecionado' : 'Todo o histórico',
            'intervalo' => relatorioVisitasIntervaloCurto($filtros),
            'grupos' => $grupos,
            'destaque' => true,
        ],
    ];
    foreach ([
        'Últimos 7 dias' => [$hoje->modify('-6 days'), $hoje],
        'Este mês' => [$hoje->modify('first day of this month'), $hoje],
        'Últimos 30 dias' => [$hoje->modify('-29 days'), $hoje],
    ] as $titulo => [$inicioJanela, $fimJanela]) {
        $filtrosJanela = array_merge($filtros, [
            'data_inicio' => $inicioJanela->format('Y-m-d'),
            'data_fim' => $fimJanela->format('Y-m-d'),
        ]);
        $janelas[] = [
            'titulo' => $titulo,
            'intervalo' => $inicioJanela == $fimJanela
                ? $inicioJanela->format('d/m/Y')
                : $inicioJanela->format('d/m') . ' a ' . $fimJanela->format('d/m/Y'),
            'grupos' => $visitaDAO->listarHistorico($filtrosJanela),
            'destaque' => false,
        ];
    }

    $quadros = '';
    foreach ($janelas as $janela) {
        $visitas = count($janela['grupos']);
        $visitantes = count(array_unique(array_merge(...array_map(
            fn($g) => array_map(fn($v) => (int) $v['id_visitante'], $g['visitantes']),
            $janela['grupos'] ?: [['visitantes' => []]]
        ))));
        $quadros .= '<td class="quadro' . ($janela['destaque'] ? ' quadro-destaque' : '') . '" width="25%">'
            . '<div class="quadro-titulo">' . relatorioVisitasEscapar($janela['titulo']) . '</div>'
            . '<div class="quadro-numero">' . $visitas . '</div>'
            . '<div class="quadro-rotulo">' . ($visitas === 1 ? 'visita' : 'visitas')
            . ' · ' . $visitantes . ($visitantes === 1 ? ' visitante' : ' visitantes') . '</div>'
            . '<div class="quadro-intervalo">' . relatorioVisitasEscapar($janela['intervalo']) . '</div>'
            . '</td>';
    }

    $notaQuadros = 'Cada entrada registrada conta como uma visita, independentemente de quantos visitantes e visitados ela tenha;'
        . ' visitantes contam uma vez por quadro. Os quadros usam os mesmos filtros de visitado, tipo, situação e referência do relatório.';

    // Linhas da tabela
    $linhas = '';
    foreach ($grupos as $grupo) {
        if ($grupo['em_andamento']) {
            $dias = (int) ($grupo['dias_em_andamento'] ?? 0);
            $saida = $dias === 0
                ? '<span class="andamento">Em andamento</span>'
                : '<span class="andamento">Em andamento há ' . $dias . ($dias === 1 ? ' dia' : ' dias') . '</span><br><span class="observacao">Saída não registrada?</span>';
        } else {
            $saida = relatorioVisitasData($grupo['horario_saida'], 'd/m/Y H:i');
        }

        $descricao = trim((string) $grupo['descricao']);

        $linhas .= '<tr>'
            . '<td>' . relatorioVisitasData($grupo['horario_entrada'], 'd/m/Y H:i') . '</td>'
            . '<td>' . $saida . '</td>'
            . '<td>' . relatorioVisitasNomes($grupo['visitantes'], []) . '</td>'
            . '<td>' . relatorioVisitasNomes($grupo['visitados'], $rotulosCategoria) . '</td>'
            . '<td>' . ($descricao === '' ? '' : relatorioVisitasEscapar($descricao)) . '</td>'
            . '</tr>';
    }

    if ($linhas === '') {
        $linhas = '<tr><td colspan="5" class="vazio">Nenhuma visita encontrada com os filtros selecionados.</td></tr>';
    }

    $html = '
    <style>
        body { font-family: sans-serif; font-size: 9pt; color: #222; }
        table.cabecalho { width: 100%; border-collapse: collapse; border-bottom: 2px solid #0088cc; }
        table.cabecalho td { vertical-align: middle; padding: 0 0 6px 0; }
        .instituicao { font-size: 8.5pt; color: #555; }
        .titulo { font-size: 15pt; font-weight: bold; color: #1d2127; }
        .geracao { text-align: right; font-size: 8pt; color: #555; }
        .filtros { margin: 6px 0 8px; padding: 5px 8px; background: #f2f6f9; font-size: 8pt; color: #333; }
        .filtros b { color: #1d2127; }
        table.quadros { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px; }
        td.quadro { border: 1px solid #dee2e6; padding: 6px 8px; vertical-align: top; }
        td.quadro-destaque { border: 1px solid #0088cc; background: #e6f3fa; }
        .quadro-titulo { font-size: 7.5pt; font-weight: bold; color: #555; text-transform: uppercase; }
        .quadro-numero { font-size: 18pt; font-weight: bold; color: #0088cc; line-height: 1.1; }
        .quadro-rotulo { font-size: 8pt; color: #333; }
        .quadro-intervalo { font-size: 7pt; color: #777; }
        h4 { font-size: 10.5pt; margin: 10px 0 4px; }
        table.dados { width: 100%; border-collapse: collapse; }
        table.dados th { background: #343a40; color: #fff; text-align: left; padding: 5px; font-size: 8.5pt; }
        table.dados td { padding: 5px; border-bottom: 1px solid #dee2e6; vertical-align: top; font-size: 8.5pt; }
        table.dados tr.par td { background: #f2f2f2; }
        .categoria, .observacao { color: #666; font-size: 7.5pt; }
        .andamento { color: #b71c1c; font-weight: bold; }
        .vazio { text-align: center; color: #666; padding: 14px; }
        .nota { color: #777; font-size: 7pt; margin-top: 4px; }
    </style>

    <table class="cabecalho"><tr>
        <td width="48">' . relatorioVisitasLogo($pdo) . '</td>
        <td>
            <div class="instituicao">' . relatorioVisitasEscapar($nomeInstituicao) . '</div>
            <div class="titulo">Relatório de visitas</div>
        </td>
        <td class="geracao" width="35%">Gerado em ' . $geradoEm->format('d/m/Y') . ' às ' . $geradoEm->format('H:i')
            . '<br>por ' . relatorioVisitasEscapar($usuario) . '</td>
    </tr></table>

    <div class="filtros">
        <b>Período:</b> ' . relatorioVisitasEscapar($periodo) . ' &nbsp;&nbsp;|&nbsp;&nbsp;
        <b>Visitado:</b> ' . relatorioVisitasEscapar($visitadoFiltro) . ' &nbsp;&nbsp;|&nbsp;&nbsp;
        <b>Tipo de visitado:</b> ' . relatorioVisitasEscapar($categoriaFiltro) . ' &nbsp;&nbsp;|&nbsp;&nbsp;
        <b>Situação:</b> ' . relatorioVisitasEscapar($situacaoFiltro) . '
    </div>

    <table class="quadros"><tr>' . $quadros . '</tr></table>
    <div class="nota">' . relatorioVisitasEscapar($notaQuadros) . '</div>

    <h4>Resultado</h4>
    <table class="dados">
        <thead><tr>
            <th width="12%">Entrada</th>
            <th width="15%">Saída</th>
            <th width="25%">Visitantes</th>
            <th width="28%">Visitados</th>
            <th width="20%">Descrição</th>
        </tr></thead>
        <tbody>' . $linhas . '</tbody>
    </table>';

    // Zebra feita no PHP: o mPDF não aplica :nth-child.
    $contador = 0;
    $html = preg_replace_callback('#<tr>(?=<td)#', function () use (&$contador) {
        return $contador++ % 2 === 1 ? '<tr class="par">' : '<tr>';
    }, $html);

    $configuracao = [
        'mode' => 'utf-8',
        'format' => 'A4-L',
        'margin_top' => 12,
        'margin_bottom' => 14,
        'margin_left' => 10,
        'margin_right' => 10,
    ];

    try {
        $mpdf = new Mpdf($configuracao);
    } catch (\Throwable $e) {
        $tempDir = sys_get_temp_dir() . '/mpdf';
        if (!is_dir($tempDir) && !mkdir($tempDir, 0755, true)) {
            throw new Exception('Não foi possível criar o diretório temporário do mPDF.');
        }
        $mpdf = new Mpdf($configuracao + ['tempDir' => $tempDir]);
    }

    $mpdf->SetTitle('Relatório de visitas');
    $mpdf->SetAuthor($nomeInstituicao !== '' ? $nomeInstituicao : 'WeGIA');
    $mpdf->SetFooter('Relatório de visitas - gerado em ' . $geradoEm->format('d/m/Y H:i') . '||Página {PAGENO} de {nbpg}');
    $mpdf->WriteHTML($html);

    $mpdf->Output('relatorio_visitas_' . $geradoEm->format('Y-m-d_His') . '.pdf', 'D');
} catch (Exception $e) {
    Util::tratarException($e);
}
