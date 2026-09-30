<?php

if (session_status() === PHP_SESSION_NONE) session_start();

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

require_once $baseDir . '/dao/Conexao.php';
require_once $baseDir . '/dao/SaudeDAO.php';
require_once $baseDir . '/dao/AtendimentoPacienteDAO.php';
require_once $baseDir . '/dao/MedicamentoPacienteDAO.php';

use Mpdf\Mpdf;

$idFichaMedica = filter_input(INPUT_GET,'id_fichamedica',FILTER_VALIDATE_INT);

if (!$idFichaMedica || $idFichaMedica < 1) {
    http_response_code(400);
    exit('ID da ficha médica inválido.');
}

try {

    $saudeDAO = new SaudeDAO();
    $atendimentoDAO = new AtendimentoPacienteDAO();
    $medicamentoDAO = new MedicamentoPacienteDAO();

    $paciente = $saudeDAO->listarDadosPaciente($idFichaMedica);
    if (!$paciente) {
        http_response_code(404);
        exit('Paciente não encontrado.');
    }

    $prontuarioPublico = $saudeDAO->listarDescricoesProntuario($idFichaMedica);
    $atendimentos = $atendimentoDAO->listarAtendimentosPorFichaMedica($idFichaMedica);
    $medicamentos = $medicamentoDAO->listarMedicacoesPorFicha($idFichaMedica);


    $formatarData = static function ($data, $comHora = false): string {
        if (empty($data))
            return '';

        try {
            $objetoData = new DateTime($data);
            return $objetoData->format(
                $comHora ? 'd/m/Y H:i' : 'd/m/Y'
            );
        } catch (Throwable $e) {
            return '';
        }
    };

    $htmlSeguro = static function ($valor): string {
        return htmlspecialchars(
            (string)($valor ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    };
    
    $html = '
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">

        <style>

            @page {
                margin: 18mm 15mm 18mm 15mm;
            }

            body {
                font-family: sans-serif;
                font-size: 10pt;
                color: #222;
            }

            h1 {
                font-size: 18pt;
                text-align: center;
                margin-bottom: 4px;
            }

            h2 {
                font-size: 13pt;
                margin-top: 22px;
                margin-bottom: 8px;
                padding-bottom: 4px;
                border-bottom: 1px solid #555;
            }

            h3 {
                font-size: 11pt;
                margin-top: 14px;
                margin-bottom: 6px;
            }

            .cabecalho {
                text-align: center;
                margin-bottom: 20px;
            }

            .subtitulo {
                font-size: 10pt;
                color: #555;
            }

            .dados-paciente {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 12px;
            }

            .dados-paciente td {
                padding: 5px;
                border: 1px solid #ccc;
            }

            .label {
                font-weight: bold;
                width: 18%;
                background-color: #f2f2f2;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 6px;
            }

            th {
                background-color: #eaeaea;
                font-weight: bold;
                text-align: left;
                padding: 6px;
                border: 1px solid #bbb;
            }

            td {
                padding: 6px;
                border: 1px solid #ccc;
                vertical-align: top;
            }

            .status-ativo {
                font-weight: bold;
            }

            .status-anulado {
                font-weight: bold;
            }

            .vazio {
                color: #666;
                font-style: italic;
                padding: 8px 0;
            }

            .prontuario {
                border: 1px solid #ccc;
                padding: 10px;
                margin-bottom: 8px;
            }

            .descricao {
                white-space: normal;
            }

            .rodape {
                text-align: center;
                font-size: 8pt;
                color: #777;
            }

        </style>
    </head>

    <body>

        <div class="cabecalho">
            <h1>Prontuário do Paciente</h1>
            <div class="subtitulo">
                Documento gerado em ' . $htmlSeguro(
                    date('d/m/Y H:i')
                ) . '
            </div>
        </div>
    ';

    // ========================================================
    // 6. Dados do paciente
    // ========================================================

    $nomeCompleto = trim(
        ($paciente['nome'] ?? '') . ' ' .
        ($paciente['sobrenome'] ?? '')
    );
    if($paciente['sexo'] == 'f')
        $sexo = 'Feminino';
    if($paciente['sexo'] == 'm')
        $sexo = 'Masculino';

    $html .= '
        <h2>Dados do paciente</h2>

        <table class="dados-paciente">
            <tr>
                <td class="label">Nome</td>
                <td>' . $htmlSeguro($nomeCompleto) . '</td>

                <td class="label">Gênero</td>
                <td>' . $htmlSeguro($sexo ?? '') . '</td>
            </tr>

            <tr>
                <td class="label">Data de nascimento</td>
                <td>' . $htmlSeguro(
                    $formatarData($paciente['data_nascimento'] ?? '')
                ) . '</td>

                <td class="label">Tipo sanguíneo</td>
                <td>' . $htmlSeguro(
                    $paciente['tipo_sanguineo'] ?? ''
                ) . '</td>
            </tr>

            <tr>
                <td class="label">CNS</td>
                <td colspan="3">' . $htmlSeguro(
                    $paciente['cns'] ?? ''
                ) . '</td>
            </tr>
        </table>
    ';

    // ========================================================
    // 7. Prontuário público
    // ========================================================

    $html .= '<h2>Prontuário Público</h2>';

    if (!empty($prontuarioPublico)) {
        foreach ($prontuarioPublico as $item) {
            $descricao = $item['descricao'] ?? '';
            $html .= '
                <div class="prontuario descricao">
                    ' . $descricao . '
                </div>
            ';
        }
    } else {
        $html .= '
            <div class="vazio">
                Nenhuma informação registrada no prontuário público.
            </div>
        ';
    }

    // ========================================================
    // 8. Histórico de atendimentos
    // ========================================================

    $html .= '
        <h2>Histórico de atendimentos</h2>
    ';

    if (!empty($atendimentos)) {
        $html .= '
            <table>
                <thead>
                    <tr>
                        <th>Médico</th>
                        <th>Registro</th>
                        <th>Descrição</th>
                        <th>Data do atendimento</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
        ';

        foreach ($atendimentos as $atendimento) {
            $medico = $atendimento['medicoNome'] ?? '';
            $registro = $atendimento['registro'] ?? '';
            $descricao = $atendimento['descricao'] ?? '';
            $dataAtendimento = $formatarData(
                $atendimento['data_atendimento'] ?? ''
            );
            $anulado = (int)($atendimento['anulado'] ?? 0);

            if ($anulado === 1) {
                $status = 'Anulado';
                if (!empty($atendimento['data_anulacao'])) {
                    $status .= ' em ' . $formatarData(
                        $atendimento['data_anulacao'],
                        true
                    );
                }
            } else {
                $status = 'Ativo';
            }

            $classeStatus = $anulado === 1
                ? 'status-anulado'
                : 'status-ativo';

            $html .= '
                <tr>
                    <td>' . $htmlSeguro($medico) . '</td>

                    <td>' . $htmlSeguro($registro) . '</td>

                    <td class="descricao">
                        ' . $descricao . '
                    </td>

                    <td>' . $htmlSeguro($dataAtendimento) . '</td>

                    <td class="' . $classeStatus . '">
                        ' . $htmlSeguro($status) . '
                    </td>
                </tr>
            ';
        }
        $html .= '
                </tbody>
            </table>
        ';
    } else {
        $html .= '
            <div class="vazio">
                Nenhum atendimento registrado.
            </div>
        ';
    }

    // ========================================================
    // 9. Medicamentos
    // ========================================================

    $html .= '
        <h2>Medicamentos</h2>
    ';

    if (!empty($medicamentos)) {
        $html .= '
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Medicamento</th>
                        <th>Dosagem</th>
                        <th>Horários</th>
                        <th>Duração</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
        ';

        foreach ($medicamentos as $medicamento) {
            $data = $formatarData(
                $medicamento['data_atendimento'] ?? ''
            );
            $nome = $medicamento['medicamento'] ?? '';
            $dosagem = $medicamento['dosagem'] ?? '';
            $horarios = $medicamento['horarios'] ?? '';
            $duracao = $medicamento['duracao'] ?? '';
            $status = $medicamento['descricao'] ?? '';
            $html .= '
                <tr>
                    <td>' . $htmlSeguro($data) . '</td>
                    <td>' . $htmlSeguro($nome) . '</td>
                    <td>' . $htmlSeguro($dosagem) . '</td>
                    <td>' . $htmlSeguro($horarios) . '</td>
                    <td>' . $htmlSeguro($duracao) . '</td>
                    <td>' . $htmlSeguro($status) . '</td>
                </tr>
            ';
        }

        $html .= '
                </tbody>
            </table>
        ';
    } else {
        $html .= '
            <div class="vazio">
                Nenhum medicamento registrado.
            </div>
        ';
    }

    // ========================================================
    // 10. Geração do PDF
    // ========================================================

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_top' => 18,
        'margin_bottom' => 18,
        'margin_left' => 15,
        'margin_right' => 15
    ]);

    $mpdf->SetTitle(
        'Prontuário - ' . $nomeCompleto
    );

    $mpdf->SetAuthor(
        'Sistema'
    );

    $mpdf->WriteHTML($html);

    $nomeArquivo = 'prontuario_' .
        preg_replace(
            '/[^a-zA-Z0-9_-]/',
            '_',
            $nomeCompleto
        ) .
        '.pdf';

    $mpdf->Output(
        $nomeArquivo,
        'I'
    );
} catch (Throwable $e) {

    http_response_code(500);

    exit(
        'Erro ao gerar prontuário: ' .
        $e->getMessage() .
        '<br><br>Arquivo: ' .
        $e->getFile() .
        '<br>Linha: ' .
        $e->getLine()
    );
}
?>