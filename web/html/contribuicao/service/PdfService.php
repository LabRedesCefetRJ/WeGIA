<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once dirname(__DIR__) . '/dao/ImagemDAO.php';
require_once dirname(__DIR__) . '/dao/ConexaoDAO.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SelecaoParagrafoDAO.php';

use setasign\Fpdi\Fpdi;

class PdfService
{

    /**
     * Normaliza e prepara o diretório para salvar arquivos PDF.
     */
    private function prepararDiretorio(?string $diretorio): ?string
    {
        if ($diretorio === null) {
            return null;
        }

        if (substr($diretorio, -1) !== DIRECTORY_SEPARATOR) {
            $diretorio .= DIRECTORY_SEPARATOR;
        }

        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        return $diretorio;
    }

    /**
     * Inicia o PDF no padrão da aplicação com margem e cabeçalho.
     */
    private function criarPdfPadrao(string $titulo): Fpdi
    {
        $pdf = new Fpdi();
        $pdf->AddPage('P', 'A4');
        $pdf->SetMargins(25, 25, 25);
        $this->aplicarLogoInstitucional($pdf);
        $this->renderTituloPrincipal($pdf, $titulo);
        return $pdf;
    }

    private function renderTituloPrincipal(Fpdi $pdf, string $titulo): void
    {
        $corAzul = [0, 70, 160];
        $pdf->SetTextColor(...$corAzul);
        $pdf->SetFont('Arial', 'B', 22);
        $pdf->Cell(0, 18, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $titulo), 0, 1, 'C');
        $pdf->Ln(2);
        $this->renderDivisorAzul($pdf);
        $pdf->Ln(10);
    }

    private function renderDivisorAzul(Fpdi $pdf): void
    {
        $corAzul = [0, 70, 160];
        $pdf->SetDrawColor(...$corAzul);
        $pdf->SetLineWidth(1.2);
        $pdf->Line(25, $pdf->GetY(), 185, $pdf->GetY());
    }

    private function renderMensagemAgradecimento(Fpdi $pdf, string $mensagem): void
    {
        $pdf->SetFont('Arial', '', 15);
        $pdf->SetTextColor(0, 70, 160);
        $pdf->MultiCell(0, 12, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mensagem), 0, 'C');
        $pdf->Ln(12);
    }

    private function renderInformacoesBasicas(Fpdi $pdf, array $linhas): void
    {
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0);

        foreach ($linhas as $linha) {
            $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $linha), 0, 1, 'L');
        }

        $pdf->Ln(10);
    }

    private function obterInstituicaoFormatada(): string
    {
        require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'EnderecoDAO.php';

        $enderecoDao = new EnderecoDAO();
        $retorno = $enderecoDao->listarInstituicao();
        $dados = json_decode($retorno, true);
        $endereco = [];

        if (is_array($dados) && count($dados) > 0 && is_array($dados[0])) {
            $endereco = $dados[0];
        }

        $nomeInstituicao = trim($endereco['nome'] ?? '');
        if ($nomeInstituicao === '') {
            $nomeInstituicao = 'nossa instituição';
        } else {
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

            $primeiraPalavra = strtolower(strtok($nomeInstituicao, ' '));
            $isFeminino = in_array($primeiraPalavra, $femininos, true) || str_ends_with($primeiraPalavra, 'a');
            $nomeInstituicao = ($isFeminino ? 'a ' : 'o ') . $nomeInstituicao;
        }

        $cnpj = SelecaoParagrafoDAO::getSelecao(SelecaoParagrafo::Cnpj);
        if (isset($cnpj) && trim($cnpj) !== '') {
            $nomeInstituicao .= " (CNPJ: $cnpj";
        }

        if (isset($endereco['cep']) && strlen(trim((string)$endereco['cep'])) !== 0) {
            if (isset($cnpj) && trim($cnpj) !== '') {
                $nomeInstituicao .= " | CEP: {$endereco['cep']}";
            } else {
                $nomeInstituicao .= " (CEP: {$endereco['cep']}";
            }

            if (isset($endereco['numero_endereco']) && strlen(trim((string)$endereco['numero_endereco'])) !== 0 && trim(strtolower($endereco['numero_endereco'])) !== 'sem número') {
                $nomeInstituicao .= ", n°: {$endereco['numero_endereco']}";
            }
        } else {
            if (isset($endereco['cidade']) && strlen(trim((string)$endereco['cidade'])) !== 0) {
                if (isset($cnpj) && trim($cnpj) !== '') {
                    $nomeInstituicao .= " | Endereço: {$endereco['cidade']}";
                } else {
                    $nomeInstituicao .= "(Endereço: {$endereco['cidade']}";
                }

                if (isset($endereco['bairro']) && strlen(trim((string)$endereco['bairro'])) !== 0) {
                    $nomeInstituicao .= ", {$endereco['bairro']}";
                }

                if (isset($endereco['logradouro']) && strlen(trim((string)$endereco['logradouro'])) !== 0) {
                    $nomeInstituicao .= ", {$endereco['logradouro']}";
                }

                if (isset($endereco['numero_endereco']) && strlen(trim((string)$endereco['numero_endereco'])) !== 0 && trim(strtolower($endereco['numero_endereco'])) !== 'sem número') {
                    $nomeInstituicao .= ", n°: {$endereco['numero_endereco']}";
                }

                if (isset($endereco['estado']) && strlen(trim((string)$endereco['estado'])) !== 0) {
                    $nomeInstituicao .= ", {$endereco['estado']}";
                }
            }
        }

        if (preg_match('/\([^)]*$/', $nomeInstituicao)) {
            $nomeInstituicao .= ')';
        }

        return $nomeInstituicao;
    }

    private function obterAgradecimento(): string
    {
        $agradecimento = SelecaoParagrafoDAO::getSelecao(SelecaoParagrafo::Agradecimento);
        if (is_null($agradecimento) || trim($agradecimento) === '') {
            return 'Sua contribuição é fundamental para a nossa organização!';
        }

        return $agradecimento;
    }

    private function obterPeriodoContribuicoes(array $contribuicoes): string
    {
        $datas = [];
        foreach ($contribuicoes as $contribuicao) {
            $data = $contribuicao['data_geracao'] ?? ($contribuicao['dataGeracao'] ?? null);
            if ($data && strtotime($data) !== false) {
                $datas[] = strtotime($data);
            }
        }

        if (empty($datas)) {
            return '-';
        }

        sort($datas);
        return date('d/m/Y', reset($datas)) . ' a ' . date('d/m/Y', end($datas));
    }

    /**
     * Gerar recibo em PDF
     */
    public function gerarRecibo(Recibo $recibo, Socio $socio, $diretorio = null)
    {
        try {
            $diretorio = $this->prepararDiretorio($diretorio);
            $pdf = $this->criarPdfPadrao('COMPROVANTE DE DOAÇÕES');

            $nomeInstituicao = $this->obterInstituicaoFormatada();
            $agradecimento = $this->obterAgradecimento();

            $mensagem = sprintf(
                'Agradecemos a %s (Código de Doador: %s) pela doação de R$ %s para %s no ano de %d. %s',
                $socio->getFullName(),
                $recibo->getCodigo(),
                number_format($recibo->getValorTotal(), 2, ',', '.'),
                $nomeInstituicao,
                date('Y', strtotime($recibo->getDataFim()->format('Y-m-d'))),
                $agradecimento
            );

            $this->renderMensagemAgradecimento($pdf, $mensagem);
            $this->renderDivisorAzul($pdf);

            $this->renderInformacoesBasicas($pdf, [
                'Código do Comprovante: ' . $recibo->getCodigo(),
                'Data de Emissão: ' . date('d/m/Y H:i:s'),
                'Período: ' . $recibo->getDataInicio()->format('d/m/Y') . ' a ' . $recibo->getDataFim()->format('d/m/Y'),
                'CPF: ' . $this->formatarCPF($socio->getDocumento())
            ]);

            $this->renderTabelaContribuicoes($pdf, $recibo->getContribuicoes());

            if (isset($diretorio)) {
                $nomeArquivo = 'recibo_' . $recibo->getCodigo() . '.pdf';
                return $this->salvarPdf($pdf, $diretorio . $nomeArquivo);
            }

            return $pdf->Output('S');
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
    private function renderResumoContribuicoes(Fpdi $pdf, array $resumo): void
    {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Resumo do período'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total de contribuições: ' . $resumo['total']), 0, 1, 'L');
        $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Contribuições pagas: ' . $resumo['pagas']), 0, 1, 'L');
        $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Contribuições pendentes: ' . $resumo['pendentes']), 0, 1, 'L');
        $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total pago: R$ ' . number_format($resumo['valor_pago'], 2, ',', '.')), 0, 1, 'L');
        $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total pendente: R$ ' . number_format($resumo['valor_pendente'], 2, ',', '.')), 0, 1, 'L');
        $pdf->Ln(8);
    }

    public function gerarExtratoContribuicoes(array $contribuicoes, array $socio, $diretorio = null)
    {
        try {
            $diretorio = $this->prepararDiretorio($diretorio);
            $pdf = $this->criarPdfPadrao('EXTRATO DE CONTRIBUIÇÕES');

            $nomeInstituicao = $this->obterInstituicaoFormatada();
            $agradecimento = $this->obterAgradecimento();
            $resumo = $this->calcularResumoContribuicoes($contribuicoes);

            $mensagem = sprintf(
                'Agradecemos a %s pelas contribuições feitas para %s. %s',
                $this->obterNomeCompleto($socio),
                $nomeInstituicao,
                $agradecimento
            );

            $this->renderMensagemAgradecimento($pdf, $mensagem);
            $this->renderDivisorAzul($pdf);
            $this->renderInformacoesBasicas($pdf, [
                'Sócio: ' . $this->obterNomeCompleto($socio),
                'CPF: ' . $this->formatarCPF($socio['cpf'] ?? ''),
                'Período registrado: ' . $this->obterPeriodoContribuicoes($contribuicoes)
            ]);

            $this->renderResumoContribuicoes($pdf, $resumo);

            $this->renderTabelaContribuicoes($pdf, $contribuicoes, [
                'titulo' => 'Detalhamento das Contribuições',
                'headers' => ['Código', 'Status', 'D. Emissão', 'D. Pagamento', 'Valor'],
                'larguras' => [50, 26, 28, 28, 28],
                'rowFormatter' => function (array $contribuicao): array {
                    return [
                        (string)($contribuicao['codigo'] ?? ''),
                        $this->formatarStatusPagamento($contribuicao['status_pagamento'] ?? ($contribuicao['statusPagamento'] ?? null)),
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
     * Gerar comprovante em PDF para uma contribuição específica.
     *
     * @param array $contribuicao
     * @param array $socio
     * @param string|null $diretorio
     * @return string
     */
    public function gerarComprovanteContribuicao(array $contribuicao, array $socio, $diretorio = null)
    {
        try {
            $diretorio = $this->prepararDiretorio($diretorio);
            $pdf = $this->criarPdfPadrao('COMPROVANTE DE CONTRIBUIÇÃO');

            $codigo = (string)($contribuicao['codigo'] ?? '');
            $valor = (float)($contribuicao['valor'] ?? 0);
            $status = $this->formatarStatusPagamento($contribuicao['status_pagamento'] ?? ($contribuicao['statusPagamento'] ?? null));
            $dataGeracao = $this->formatarDataContribuicao($contribuicao['data_geracao'] ?? ($contribuicao['dataGeracao'] ?? null));
            $dataVencimento = $this->formatarDataContribuicao($contribuicao['data_vencimento'] ?? ($contribuicao['dataVencimento'] ?? null));
            $dataPagamento = $this->formatarDataContribuicao($contribuicao['data_pagamento'] ?? ($contribuicao['dataPagamento'] ?? null));
            $plataforma = (string)($contribuicao['plataforma'] ?? '-');
            $meioPagamento = (string)($contribuicao['meioPagamento'] ?? $contribuicao['meio_pagamento'] ?? '-');

            $mensagem = sprintf(
                'Este documento confirma a contribuição vinculada a(o) doador(a) %s.',
                $this->obterNomeCompleto($socio)
            );

            $this->renderMensagemAgradecimento($pdf, $mensagem);
            $this->renderDivisorAzul($pdf);
            $this->renderInformacoesBasicas($pdf, [
                'Nome: ' . $this->obterNomeCompleto($socio),
                'CPF: ' . $this->formatarCPF($socio['cpf'] ?? ''),
                'E-mail: ' . ($socio['email'] ?? '-')
            ]);
            $this->renderInformacoesBasicas($pdf, [
                'Código: ' . $codigo,
                'Valor: R$ ' . number_format($valor, 2, ',', '.'),
                'Status: ' . $status,
                'Meio de pagamento: ' . $meioPagamento,
                'Plataforma: ' . $plataforma,
                'Data de geração: ' . $dataGeracao,
                'Data de vencimento: ' . $dataVencimento,
                'Data de pagamento: ' . $dataPagamento
            ]);

            $pdf->SetFont('Arial', 'I', 10);
            $pdf->MultiCell(
                0,
                6,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Guarde este comprovante para fins de controle e conferência do pagamento realizado.'),
                0,
                'C'
            );

            if (isset($diretorio)) {
                $nomeArquivo = 'comprovante_contribuicao_' . ($contribuicao['id'] ?? 'contribuicao') . '.pdf';
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
        $larguras = $config['larguras'] ?? [45, 31, 28, 28, 28];
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

            $linhaFormatada = [];
            $numeroLinhas = [];
            $alturaMinima = 8;
            $alturaLinhaBase = 6;

            foreach ($linha as $indice => $valor) {
                $texto = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$valor);
                $linhaFormatada[$indice] = $texto;
                $larguraCelula = $larguras[$indice] - 2;
                $linhas = max(1, ceil($pdf->GetStringWidth($texto) / max(1, $larguraCelula)));
                $numeroLinhas[] = $linhas;
            }

            $alturaLinha = max($alturaMinima, max($numeroLinhas) * $alturaLinhaBase);

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

            $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
            $fill = !$fill;

            $xBase = $pdf->GetX();
            $yIni = $pdf->GetY();
            $xAtual = $xBase;

            // Desenha o contorno da linha inteira com células vazias e preenchimento.
            foreach ($larguras as $largura) {
                $pdf->SetXY($xAtual, $yIni);
                $pdf->Cell($largura, $alturaLinha, '', 1, 0, '', true);
                $xAtual += $largura;
            }

            $xAtual = $xBase;
            $pdf->SetXY($xAtual, $yIni);

            // Preenche o texto em cada coluna dentro da célula já desenhada.
            foreach ($linhaFormatada as $indice => $texto) {
                $pdf->SetXY($xAtual + 2, $yIni + 2);
                $pdf->SetFont('Arial', '', 10);
                $pdf->MultiCell($larguras[$indice] - 4, $alturaLinhaBase, $texto, 0, $indice === 4 ? 'R' : ($indice === 2 || $indice === 3 ? 'C' : 'L'));
                $xAtual += $larguras[$indice];
            }

            $pdf->SetXY($xBase, $yIni + $alturaLinha);
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
