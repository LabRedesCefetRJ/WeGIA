<?php
require_once '../vendor/autoload.php';
require_once dirname(__DIR__).'/dao/ImagemDAO.php';
require_once dirname(__DIR__).'/dao/ConexaoDAO.php';

use setasign\Fpdi\Fpdi;

class PdfService {
    
    /**
     * Gerar recibo em PDF
     */
    public function gerarRecibo(Recibo $recibo, Socio $socio, $diretorio = null) {
        try {
            // Configurar diretório
            if ($diretorio === null) {
                $diretorio = '../pdfs/';
            }
            
            // Garantir que termina com separador
            if (substr($diretorio, -1) !== DIRECTORY_SEPARATOR) {
                $diretorio .= DIRECTORY_SEPARATOR;
            }
            
            // Criar diretório se não existir
            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0755, true);
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
                    $pdf->Ln(18);
                }
            } else {
                $pdf->Ln(30); // Espaço caso não tenha logo
            }

            // Configurações de estilo
            $corAzul = [0, 70, 160]; // Azul MSF
            $pdf->SetTextColor(...$corAzul);
            $pdf->SetFont('Arial', 'B', 22);
            $pdf->Cell(0, 18, 'RECIBO DE DOAÇÕES', 0, 1, 'C');
            $pdf->Ln(2);

            // Divisor azul
            $pdf->SetDrawColor(...$corAzul);
            $pdf->SetLineWidth(1.2);
            $pdf->Line(25, $pdf->GetY(), 185, $pdf->GetY());
            $pdf->Ln(10);

            // Mensagem de agradecimento dinâmica
            $pdf->SetFont('Arial', '', 15);
            $pdf->SetTextColor(...$corAzul);
            $mensagem = sprintf(
                "Agradecemos a %s (Código de Doador: %s) pela doação de R$ %s para NOME ORGANIZAÇÃO no ano de %d. Sua contribuição é fundamental para a nossa organização!",
                mb_convert_encoding($socio->getNome(), 'UTF-8'),
                $recibo->getCodigo(),
                number_format($recibo->getValorTotal(), 2, ',', '.'),
                date('Y', strtotime($recibo->getDataFim()->format('Y-m-d')))
            );
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
            $pdf->Cell(0, 8, 'Código do Recibo: ' . $recibo->getCodigo(), 0, 1, 'L');
            $pdf->Cell(0, 8, 'Data de Emissão: ' . date('d/m/Y H:i:s'), 0, 1, 'L');
            $pdf->Cell(0, 8, 'Período: ' . $recibo->getDataInicio()->format('d/m/Y') . ' a ' . $recibo->getDataFim()->format('d/m/Y'), 0, 1, 'L');
            $pdf->Cell(0, 8, 'CPF: ' . $this->formatarCPF($socio->getDocumento()), 0, 1, 'L');
            $pdf->Ln(10);
            // ...não há campo de assinatura...

            // Salvar arquivo
            $nomeArquivo = 'recibo_' . $recibo->getCodigo() . '.pdf';
            $caminho = $diretorio . $nomeArquivo;
            $pdf->Output('F', $caminho);
            return $caminho;
            
        } catch (Exception $e) {
            error_log("Erro ao gerar PDF: " . $e->getMessage());
            throw new Exception('Erro ao gerar PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Formatar CPF
     */
    private function formatarCPF($cpf) {
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