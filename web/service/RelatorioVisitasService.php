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
require_once $baseDir . '/classes/EstatisticaVisitas.php';

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

    return '<img src="data:image/' . $tipo . ';base64,' . $base64 . '" style="height:45px;">';
}

try {
    $filtros = VisitaControle::lerFiltrosHistorico($_GET);

    $pdo = Conexao::connect();
    $visitaDAO = new VisitaDAO($pdo);
    $grupos = $visitaDAO->listarHistorico($filtros);
    $estatisticas = EstatisticaVisitas::calcular($grupos, $filtros);

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

    // Estatísticas
    $semMedia = '<span class="observacao">%s</span>';
    if ($estatisticas['media_diaria'] === null) {
        $mensagem = $estatisticas['total_entradas'] === 0 && $estatisticas['inicio'] === null
            ? 'Sem visitas para calcular'
            : 'Período ainda não iniciado';
        $mediaDiaria = $mediaSemanal = $mediaMensal = sprintf($semMedia, $mensagem);
    } else {
        $mediaDiaria = EstatisticaVisitas::formatarMedia($estatisticas['media_diaria']);
        $mediaSemanal = EstatisticaVisitas::formatarMedia($estatisticas['media_semanal']) ?? sprintf($semMedia, 'Período menor que uma semana');
        $mediaMensal = EstatisticaVisitas::formatarMedia($estatisticas['media_mensal']) ?? sprintf($semMedia, 'Período menor que um mês');
    }

    $periodoCalculo = '';
    if ($estatisticas['inicio'] !== null && $estatisticas['dias'] > 0) {
        $periodoCalculo = 'Calculado sobre ' . $estatisticas['dias'] . ($estatisticas['dias'] === 1 ? ' dia' : ' dias')
            . ', de ' . $estatisticas['inicio']->format('d/m/Y') . ' a ' . $estatisticas['fim']->format('d/m/Y')
            . (empty($filtros['data_inicio']) ? ' (a partir da primeira visita encontrada)' : '')
            . ($estatisticas['fim_limitado_a_hoje'] ? ' (dias futuros não entram na média)' : '')
            . '. Cada entrada registrada conta como uma visita, no dia em que entrou, independentemente de quantos visitantes e visitados ela tenha.';

        if ($estatisticas['entradas_anteriores'] > 0) {
            $periodoCalculo .= ' ' . ($estatisticas['entradas_anteriores'] === 1
                ? '1 visita começou antes do período e continuava em andamento nele: ela aparece no resultado, mas não entra nos totais nem nas médias.'
                : $estatisticas['entradas_anteriores'] . ' visitas começaram antes do período e continuavam em andamento nele: elas aparecem no resultado, mas não entram nos totais nem nas médias.');
        }
    }

    // Linhas da tabela
    $linhas = '';
    foreach ($grupos as $grupo) {
        if ($grupo['em_andamento']) {
            $dias = (int) ($grupo['dias_em_andamento'] ?? 0);
            $saida = $dias === 0
                ? '<span class="andamento">Em andamento</span>'
                : '<span class="andamento">Em andamento há ' . $dias . ($dias === 1 ? ' dia' : ' dias') . '</span><br><span class="observacao">Saída não registrada?</span>';
        } elseif (relatorioVisitasData($grupo['horario_saida'], 'd/m/Y') === relatorioVisitasData($grupo['horario_entrada'], 'd/m/Y')) {
            $saida = relatorioVisitasData($grupo['horario_saida'], 'H:i');
        } else {
            // Saída em outro dia: mostra a data para não parecer que saiu antes de entrar.
            $saida = relatorioVisitasData($grupo['horario_saida'], 'd/m/Y H:i');
        }

        $descricao = trim((string) $grupo['descricao']);

        $linhas .= '<tr>'
            . '<td>' . relatorioVisitasData($grupo['horario_entrada'], 'd/m/Y') . '</td>'
            . '<td>' . relatorioVisitasData($grupo['horario_entrada'], 'H:i') . '</td>'
            . '<td>' . $saida . '</td>'
            . '<td>' . relatorioVisitasNomes($grupo['visitantes'], []) . '</td>'
            . '<td>' . relatorioVisitasNomes($grupo['visitados'], $rotulosCategoria) . '</td>'
            . '<td>' . ($descricao === '' ? '' : relatorioVisitasEscapar($descricao)) . '</td>'
            . '</tr>';
    }

    if ($linhas === '') {
        $linhas = '<tr><td colspan="6" class="vazio">Nenhuma visita encontrada com os filtros selecionados.</td></tr>';
    }

    $html = '
    <style>
        body { font-family: sans-serif; font-size: 9pt; color: #222; }
        .cabecalho td { vertical-align: middle; }
        .instituicao { font-size: 12pt; font-weight: bold; }
        h3 { font-size: 14pt; margin: 10px 0 6px; }
        h4 { font-size: 11pt; margin: 14px 0 6px; }
        .resumo { margin: 0; padding-left: 14px; }
        .resumo li { margin-bottom: 2px; }
        .geracao { color: #555; margin-top: 6px; }
        table.dados { width: 100%; border-collapse: collapse; }
        table.dados th { background: #343a40; color: #fff; text-align: left; padding: 5px; font-size: 8.5pt; }
        table.dados td { padding: 5px; border-bottom: 1px solid #dee2e6; vertical-align: top; font-size: 8.5pt; }
        table.dados tr.par td { background: #f2f2f2; }
        table.estatisticas td { padding: 4px 10px 4px 0; }
        .categoria, .observacao { color: #666; font-size: 7.5pt; }
        .andamento { color: #b71c1c; font-weight: bold; }
        .vazio { text-align: center; color: #666; padding: 14px; }
        .nota { color: #555; font-size: 7.5pt; margin-top: 4px; }
    </style>

    <table class="cabecalho" width="100%"><tr>
        <td width="60">' . relatorioVisitasLogo($pdo) . '</td>
        <td class="instituicao">' . relatorioVisitasEscapar($nomeInstituicao) . '</td>
    </tr></table>

    <h3>Relatório de visitas</h3>
    <ul class="resumo">
        <li>Período: ' . relatorioVisitasEscapar($periodo) . '</li>
        <li>Visitado: ' . relatorioVisitasEscapar($visitadoFiltro) . '</li>
        <li>Tipo de visitado: ' . relatorioVisitasEscapar($categoriaFiltro) . '</li>
        <li>Situação: ' . relatorioVisitasEscapar($situacaoFiltro) . '</li>
    </ul>
    <div class="geracao">Gerado em ' . $geradoEm->format('d/m/Y') . ' às ' . $geradoEm->format('H:i') . ' por ' . relatorioVisitasEscapar($usuario) . '</div>

    <h4>Estatísticas</h4>
    <table class="estatisticas">
        <tr><td><strong>Visitas (entradas registradas):</strong></td><td>' . $estatisticas['total_entradas'] . '</td></tr>
        <tr><td><strong>Visitantes recebidos:</strong></td><td>' . $estatisticas['total_visitantes'] . '</td></tr>
        <tr><td><strong>Média diária:</strong></td><td>' . $mediaDiaria . '</td></tr>
        <tr><td><strong>Média semanal:</strong></td><td>' . $mediaSemanal . '</td></tr>
        <tr><td><strong>Média mensal:</strong></td><td>' . $mediaMensal . '</td></tr>
    </table>
    ' . ($periodoCalculo !== '' ? '<div class="nota">' . relatorioVisitasEscapar($periodoCalculo) . '</div>' : '') . '

    <h4>Resultado</h4>
    <table class="dados">
        <thead><tr>
            <th width="9%">Data</th>
            <th width="7%">Entrada</th>
            <th width="11%">Saída</th>
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
