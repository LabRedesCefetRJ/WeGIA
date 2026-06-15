<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once dirname(__DIR__) . '/dao/ImagemDAO.php';
require_once dirname(__DIR__) . '/dao/ConexaoDAO.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SelecaoParagrafoDAO.php';

use setasign\Fpdi\Fpdi;

class PdfService
{

    /**
     * Gerar recibo em PDF
     */
    public function gerarRecibo(Recibo $recibo, Socio $socio, $diretorio = null)
    {
        try {
            if (isset($diretorio)) {
                // Garantir que termina com separador
                if (substr($diretorio, -1) !== DIRECTORY_SEPARATOR) {
                    $diretorio .= DIRECTORY_SEPARATOR;
                }

                // Criar diretório se não existir
                if (!is_dir($diretorio)) {
                    mkdir($diretorio, 0755, true);
                }
            }

            // Criar PDF
            $pdf = new Fpdi();
            $pdf->AddPage('P', 'A4');
            $pdf->SetMargins(25, 25, 25); // Margens ajustadas para layout mais limpo

            // Buscar logo do banco de dados
            $pdo = \ConexaoDAO::conectar();
            $imagemDAO = new \ImagemDAO($pdo);
            $logo = $imagemDAO->getImagem();
            if ($logo) {
                $logoData = gzuncompress($logo->getConteudo());
                $logoPath = tempnam(sys_get_temp_dir(), 'logo');
                // Detecta extensão para garantir compatibilidade
                $ext = strtolower($logo->getExtensao());
                if ($ext === 'png' || $ext === 'jpg' || $ext === 'jpeg') {
                    file_put_contents($logoPath, base64_decode($logoData));
                    $pdf->Image($logoPath, 85, 20, 40, 0, strtoupper($ext)); // Centraliza logo no topo
                    unlink($logoPath);
                    $pdf->Ln(60);
                }
            } else {
                $pdf->Ln(30); // Espaço caso não tenha logo
            }

            // Configurações de estilo
            $corAzul = [0, 70, 160]; // Azul MSF
            $pdf->SetTextColor(...$corAzul);
            $pdf->SetFont('Arial', 'B', 22);
            $pdf->Cell(0, 18, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'COMPROVANTE DE DOAÇÕES'), 0, 1, 'C');
            $pdf->Ln(2);

            // Divisor azul
            $pdf->SetDrawColor(...$corAzul);
            $pdf->SetLineWidth(1.2);
            $pdf->Line(25, $pdf->GetY(), 185, $pdf->GetY());
            $pdf->Ln(10);

            // Mensagem de agradecimento dinâmica
            $pdf->SetFont('Arial', '', 15);
            $pdf->SetTextColor(...$corAzul);

            //insere o nome da instituição dinamicamente
            require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'EnderecoDAO.php';
            $enderecoDao = new EnderecoDAO();
            $retorno = $enderecoDao->listarInstituicao();         // recebe o JSON
            $endereco = json_decode($retorno, true)[0];         // vira array PHP
            $nomeInstituicao = $endereco['nome'] ?? null; // pega o nome do primeiro registro

            if (!isset($nomeInstituicao) || empty($nomeInstituicao) || strlen($nomeInstituicao) === 0) {
                $nomeInstituicao = 'nossa instituição';
            } else {

                // Lista de substantivos femininos mais comuns
                $femininos = [
                    'instituição',
                    'associação',
                    'fundação',
                    'escola',
                    'empresa',
                    'igreja',
                    'universidade',
                    'faculdade'
                ];

                // Normaliza para comparação
                $primeiraPalavra = strtolower(strtok($nomeInstituicao, ' '));

                // Verifica se é feminino
                $isFeminino = false;

                if (in_array($primeiraPalavra, $femininos)) {
                    $isFeminino = true;
                } elseif (str_ends_with($primeiraPalavra, 'a')) {
                    // Heurística simples: termina com 'a'
                    $isFeminino = true;
                }

                // Define o artigo
                if ($isFeminino) {
                    $nomeInstituicao = 'a ' . $nomeInstituicao;
                } else {
                    $nomeInstituicao = 'o ' . $nomeInstituicao;
                }
            }

            //CNPJ
            $cnpj = SelecaoParagrafoDAO::getSelecao(SelecaoParagrafo::Cnpj);

            if (isset($cnpj))
                $nomeInstituicao .= " (CNPJ: $cnpj";

            //Endereço
            if (isset($endereco['cep']) && strlen($endereco['cep']) != 0) {
                if (isset($cnpj))
                    $nomeInstituicao .= " | CEP: {$endereco['cep']}";
                else
                    $nomeInstituicao .= " (CEP: {$endereco['cep']}";

                if (isset($endereco['numero_endereco']) && strlen($endereco['numero_endereco']) != 0 && (trim(strtolower($endereco['numero_endereco'])) != 'sem número'))
                    $nomeInstituicao .= ", n°: {$endereco['numero_endereco']}";
            } else {
                if (isset($endereco['cidade']) && strlen($endereco['cidade']) != 0) {
                    if (isset($cnpj))
                        $nomeInstituicao .= " | Endereço: {$endereco['cidade']}";
                    else
                        $nomeInstituicao .= "(Endereço: {$endereco['cidade']}";

                    if (isset($endereco['bairro']) && strlen($endereco['bairro']) != 0)
                        $nomeInstituicao .= ", {$endereco['bairro']}";

                    if (isset($endereco['logradouro']) && strlen($endereco['logradouro']) != 0)
                        $nomeInstituicao .= ", {$endereco['logradouro']}";

                    if ((isset($endereco['numero_endereco']) && strlen($endereco['numero_endereco']) != 0) && (trim(strtolower($endereco['numero_endereco'])) != 'sem número'))
                        $nomeInstituicao .= ", n°: {$endereco['numero_endereco']}";

                    if (isset($endereco['estado']) && strlen($endereco['estado']) != 0)
                        $nomeInstituicao .= ", {$endereco['estado']}";
                }
            }

            if (preg_match('/\([^)]*$/', $nomeInstituicao))
                $nomeInstituicao .= ')';

            //mensagem de agradecimento ao doador.
            $agradecimento = SelecaoParagrafoDAO::getSelecao(SelecaoParagrafo::Agradecimento);

            if (is_null($agradecimento))
                $agradecimento = 'Sua contribuição é fundamental para a nossa organização!';

            $mensagem = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', sprintf(
                "Agradecemos a %s (Código de Doador: %s) pela doação de R$ %s para %s no ano de %d. %s",
                $socio->getFullName(),
                $recibo->getCodigo(),
                number_format($recibo->getValorTotal(), 2, ',', '.'),
                $nomeInstituicao,
                date('Y', strtotime($recibo->getDataFim()->format('Y-m-d'))),
                $agradecimento
            ));

            $pdf->MultiCell(0, 12, $mensagem, 0, 'C');
            $pdf->Ln(12);

            // Divisor azul
            $pdf->SetDrawColor(...$corAzul);
            $pdf->SetLineWidth(1.2);
            $pdf->Line(25, $pdf->GetY(), 185, $pdf->GetY());
            $pdf->Ln(10);

            // Informações adicionais
            $pdf->SetFont('Arial', '', 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Código do Comprovante: ' . $recibo->getCodigo()), 0, 1, 'L');
            $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Data de Emissão: ' . date('d/m/Y H:i:s')), 0, 1, 'L');
            $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Período: ' . $recibo->getDataInicio()->format('d/m/Y') . ' a ' . $recibo->getDataFim()->format('d/m/Y')), 0, 1, 'L');
            $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'CPF: ' . $this->formatarCPF($socio->getDocumento())), 0, 1, 'L');
            $pdf->Ln(10);

            //Lista de doações
            $this->renderTabelaContribuicoes($pdf, $recibo->getContribuicoes());

            if (isset($diretorio)) {
                $nomeArquivo = 'recibo_' . $recibo->getCodigo() . '.pdf';
                return $this->salvarPdf($pdf, $nomeArquivo);
            } else {
                return $pdf->Output('S');
            }
        } catch (Exception $e) {
            error_log("Erro ao gerar PDF: " . $e->getMessage());
            throw new Exception('Erro ao gerar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Gerar extrato em PDF para a lista de contribuições de um sócio
     *
     * @param array $contribuicoes
     * @param array $socio
     * @param string|null $diretorio
     * @return string
     */
    public function gerarExtratoContribuicoes(array $contribuicoes, array $socio, $diretorio = null)
    {
        try {
            if (isset($diretorio)) {
                if (substr($diretorio, -1) !== DIRECTORY_SEPARATOR) {
                    $diretorio .= DIRECTORY_SEPARATOR;
                }

                if (!is_dir($diretorio)) {
                    mkdir($diretorio, 0755, true);
                }
            }

            $pdf = new Fpdi();
            $pdf->AddPage('P', 'A4');
            $pdf->SetMargins(25, 25, 25);

            $this->aplicarLogoInstitucional($pdf);

            $corAzul = [0, 70, 160];
            $titulo = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'EXTRATO DE CONTRIBUIÇÕES');
            $subtitulo = iconv(
                'UTF-8',
                'ISO-8859-1//TRANSLIT',
                sprintf(
                    'Sócio: %s | CPF: %s',
                    $this->obterNomeCompleto($socio),
                    $this->formatarCPF($socio['cpf'] ?? '')
                )
            );

            $pdf->SetTextColor(...$corAzul);
            $pdf->SetFont('Arial', 'B', 22);
            $pdf->Cell(0, 18, $titulo, 0, 1, 'C');
            $pdf->Ln(2);

            $pdf->SetDrawColor(...$corAzul);
            $pdf->SetLineWidth(1.2);
            $pdf->Line(25, $pdf->GetY(), 185, $pdf->GetY());
            $pdf->Ln(10);

            $pdf->SetFont('Arial', '', 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell(0, 8, $subtitulo, 0, 'C');
            $pdf->Ln(3);

            $resumo = $this->calcularResumoContribuicoes($contribuicoes);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Resumo do período'), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total de contribuições: ' . $resumo['total']), 0, 1, 'L');
            $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Contribuições pagas: ' . $resumo['pagas']), 0, 1, 'L');
            $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Contribuições pendentes: ' . $resumo['pendentes']), 0, 1, 'L');
            $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total pago: R$ ' . number_format($resumo['valor_pago'], 2, ',', '.')), 0, 1, 'L');
            $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total pendente: R$ ' . number_format($resumo['valor_pendente'], 2, ',', '.')), 0, 1, 'L');
            $pdf->Ln(8);

            $this->renderTabelaContribuicoes($pdf, $contribuicoes, [
                'titulo' => 'Detalhamento das Contribuições',
                'headers' => ['Código', 'Status', 'D. Emissão', 'D. Pagamento', 'Valor'],
                'larguras' => [42, 34, 28, 28, 28],
                'rowFormatter' => function (array $contribuicao): array {
                    return [
                        (string)($contribuicao['codigo'] ?? ''),
                        $this->formatarStatusPagamento($contribuicao['status_pagamento'] ?? null),
                        $this->formatarDataContribuicao($contribuicao['data_geracao'] ?? ($contribuicao['dataGeracao'] ?? null)),
                        $this->formatarDataContribuicao($contribuicao['data_pagamento'] ?? ($contribuicao['dataPagamento'] ?? null)),
                        'R$ ' . number_format((float)($contribuicao['valor'] ?? 0), 2, ',', '.')
                    ];
                }
            ]);

            if (isset($diretorio)) {
                $nomeArquivo = 'extrato_contribuicoes_' . ($socio['id'] ?? 'socio') . '.pdf';
                return $this->salvarPdf($pdf, $diretorio . $nomeArquivo);
            }

            return $pdf->Output('S');
        } catch (Exception $e) {
            error_log("Erro ao gerar PDF: " . $e->getMessage());
            throw new Exception('Erro ao gerar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Cria um arquivo pdf no destino informado
     */
    public function salvarPdf(Fpdi $pdf, string $path): string
    {
        // Salvar arquivo
        $pdf->Output('F', $path);
        return $path;
    }

    /**
     * Formatar CPF
     */
    private function formatarCPF($cpf)
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Aplica máscara se tiver 11 dígitos
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' .
                substr($cpf, 3, 3) . '.' .
                substr($cpf, 6, 3) . '-' .
                substr($cpf, 9, 2);
        }

        return $cpf;
    }

    /**
     * Utiliza a biblioteca do Fpdi para criar um nova página no PDF com uma tabela de detalhamento das contribuições passadas como parâmetro.
     */
    private function renderTabelaContribuicoes(Fpdi $pdf, array $contribuicoes, array $config = [])
    {
        // Estilo título
        $corAzul = $config['corAzul'] ?? [0, 70, 160];
        $titulo = $config['titulo'] ?? 'Detalhamento das Doações';
        $headers = $config['headers'] ?? ['Código', 'M. Pagamento', 'D. Emissão', 'D. Pagamento', 'Valor'];
        $larguras = $config['larguras'] ?? [35, 35, 28, 28, 30];
        $rowFormatter = $config['rowFormatter'] ?? null;

        $pdf->AddPage();
        $pdf->SetTextColor(...$corAzul);
        $pdf->SetFont('Arial', 'B', 22);
        $pdf->Ln(5);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $titulo), 0, 1, 'C');
        $pdf->Ln(5);

        // Cabeçalho
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(...$corAzul);
        $pdf->SetTextColor(255, 255, 255);

        foreach ($headers as $i => $header) {
            $pdf->Cell(
                $larguras[$i],
                10,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $header),
                1,
                0,
                'C',
                true
            );
        }
        $pdf->Ln();

        // Corpo
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(0, 0, 0);

        $fill = false;

        foreach ($contribuicoes as $c) {
            $linha = is_callable($rowFormatter) ? $rowFormatter($c) : $this->formatarLinhaContribuicaoPadrao($c);
            if (count($linha) !== count($headers)) {
                throw new Exception('Quantidade de colunas inválida para a tabela de contribuições.');
            }

            // MultiCell apenas para o código
            $textoCodigo = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$linha[0]);
            $numLinhasCodigo = ceil($pdf->GetStringWidth($textoCodigo) / ($larguras[0] - 2));
            $alturaLinha = max(8, $numLinhasCodigo * 6);

            // Quebra de página
            if ($pdf->GetY() + $alturaLinha > 270) {
                $pdf->AddPage();

                // Cabeçalho novamente
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->SetFillColor(...$corAzul);
                $pdf->SetTextColor(255, 255, 255);

                foreach ($headers as $i => $header) {
                    $pdf->Cell(
                        $larguras[$i],
                        10,
                        iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $header),
                        1,
                        0,
                        'C',
                        true
                    );
                }
                $pdf->Ln();

                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(0, 0, 0);
            }

            // Fundo zebra
            $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
            $fill = !$fill;

            /* --------------- Celula 1: Código (MultiCell) ---------------- */
            $xIni = $pdf->GetX();
            $yIni = $pdf->GetY();

            $pdf->MultiCell($larguras[0], 6, $textoCodigo, 1, 'L', true);

            // Reposicionar para continuar a linha
            $pdf->SetXY($xIni + $larguras[0], $yIni);

            /* --------------- Demais colunas (altura fixa) ---------------- */
            $pdf->Cell($larguras[1], $alturaLinha, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$linha[1]), 1, 0, 'L', true);
            $pdf->Cell($larguras[2], $alturaLinha, (string)$linha[2], 1, 0, 'C', true);
            $pdf->Cell($larguras[3], $alturaLinha, (string)$linha[3], 1, 0, 'C', true);
            $pdf->Cell($larguras[4], $alturaLinha, (string)$linha[4], 1, 1, 'R', true);
        }

        $pdf->Ln(5);
    }

    /**
     * Layout padrão da linha de contribuições legado.
     */
    private function formatarLinhaContribuicaoPadrao(array $contribuicao): array
    {
        $meio = $contribuicao['meio'] ?? ($contribuicao['meioPagamento'] ?? '');

        switch ($meio) {
            case 'Carne':
                $meio = 'Carnê';
                break;

            case 'Recorrencia':
                $meio = 'Recorrência';
                break;

            case 'CartaoCredito':
                $meio = 'Cartão de crédito';
                break;

            default:
                break;
        }

        return [
            (string)($contribuicao['codigo'] ?? ''),
            (string)$meio,
            $this->formatarDataContribuicao($contribuicao['data_geracao'] ?? ($contribuicao['dataGeracao'] ?? null)),
            $this->formatarDataContribuicao($contribuicao['data_pagamento'] ?? ($contribuicao['dataPagamento'] ?? null)),
            'R$ ' . number_format((float)($contribuicao['valor'] ?? 0), 2, ',', '.')
        ];
    }

    /**
     * Formata a data para exibição no PDF.
     */
    private function formatarDataContribuicao($data): string
    {
        if (!$data) {
            return '-';
        }

        return date('d/m/Y', strtotime($data));
    }

    /**
     * Formata o status de pagamento para exibição.
     */
    private function formatarStatusPagamento($status): string
    {
        if ($status === null || $status === '') {
            return '-';
        }

        return ((int)$status === 1) ? 'Pago' : 'Pendente';
    }

    /**
     * Calcula um resumo simples das contribuições.
     */
    private function calcularResumoContribuicoes(array $contribuicoes): array
    {
        $resumo = [
            'total' => count($contribuicoes),
            'pagas' => 0,
            'pendentes' => 0,
            'valor_pago' => 0,
            'valor_pendente' => 0
        ];

        foreach ($contribuicoes as $contribuicao) {
            $valor = (float)($contribuicao['valor'] ?? 0);
            $status = $contribuicao['status_pagamento'] ?? ($contribuicao['statusPagamento'] ?? null);

            if ((int)$status === 1) {
                $resumo['pagas']++;
                $resumo['valor_pago'] += $valor;
                continue;
            }

            $resumo['pendentes']++;
            $resumo['valor_pendente'] += $valor;
        }

        return $resumo;
    }

    /**
     * Retorna o nome completo a partir dos dados de sócio/pessoa.
     */
    private function obterNomeCompleto(array $socio): string
    {
        $nome = trim(($socio['nome'] ?? '') . ' ' . ($socio['sobrenome'] ?? ''));

        if ($nome !== '') {
            return $nome;
        }

        return (string)($socio['nome_completo'] ?? $socio['nomeCompleto'] ?? 'Sócio');
    }

    /**
     * Aplica o logo institucional no cabeçalho do PDF.
     */
    private function aplicarLogoInstitucional(Fpdi $pdf): void
    {
        $pdo = \ConexaoDAO::conectar();
        $imagemDAO = new \ImagemDAO($pdo);
        $logo = $imagemDAO->getImagem();

        if ($logo) {
            $logoData = gzuncompress($logo->getConteudo());
            $logoPath = tempnam(sys_get_temp_dir(), 'logo');
            $ext = strtolower($logo->getExtensao());

            if ($ext === 'png' || $ext === 'jpg' || $ext === 'jpeg') {
                file_put_contents($logoPath, base64_decode($logoData));
                $pdf->Image($logoPath, 85, 20, 40, 0, strtoupper($ext));
                unlink($logoPath);
                $pdf->Ln(60);
                return;
            }
        }

        $pdf->Ln(30);
    }
}
