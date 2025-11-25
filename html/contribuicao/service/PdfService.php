<?php
require_once '../vendor/autoload.php';
require_once dirname(__DIR__) . '/dao/ImagemDAO.php';
require_once dirname(__DIR__) . '/dao/ConexaoDAO.php';

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
                    $pdf->Ln(40);
                }
            } else {
                $pdf->Ln(30); // Espaço caso não tenha logo
            }

            // Configurações de estilo
            $corAzul = [0, 70, 160]; // Azul MSF
            $pdf->SetTextColor(...$corAzul);
            $pdf->SetFont('Arial', 'B', 22);
            $pdf->Cell(0, 18, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'RECIBO DE DOAÇÕES'), 0, 1, 'C');
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
            $array = json_decode($retorno, true);         // vira array PHP
            $nomeInstituicao = $array[0]['nome'] ?? null; // pega o nome do primeiro registro

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

            $mensagem = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', sprintf(
                "Agradecemos a %s (Código de Doador: %s) pela doação de R$ %s para %s no ano de %d. Sua contribuição é fundamental para a nossa organização!",
                $socio->getNome(),
                $recibo->getCodigo(),
                number_format($recibo->getValorTotal(), 2, ',', '.'),
                $nomeInstituicao,
                date('Y', strtotime($recibo->getDataFim()->format('Y-m-d')))
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
            $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Código do Recibo: ' . $recibo->getCodigo()), 0, 1, 'L');
            $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Data de Emissão: ' . date('d/m/Y H:i:s')), 0, 1, 'L');
            $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Período: ' . $recibo->getDataInicio()->format('d/m/Y') . ' a ' . $recibo->getDataFim()->format('d/m/Y')), 0, 1, 'L');
            $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'CPF: ' . $this->formatarCPF($socio->getDocumento())), 0, 1, 'L');
            $pdf->Ln(10);
            // ...não há campo de assinatura...

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
}
