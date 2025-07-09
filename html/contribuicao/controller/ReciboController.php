<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Novo controller único para geração e download de recibos
session_start();
require_once '../../../config.php';
require_once '../vendor/setasign/fpdf/fpdf.php';
require_once '../../geral/email_service.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function json_response($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// Classe personalizada para PDF com melhor formatação
class ReciboPDF extends FPDF {
    
    function Header() {
        // Adicionar logo se existir
        $logo_path = dirname(__DIR__) . '/assets/logo.png'; // Ajuste o caminho conforme necessário
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 150, 10, 30); // Logo no canto superior direito
        }
        
        // Faixa vermelha no topo
        $this->SetFillColor(220, 38, 38); // Cor vermelha
        $this->Rect(0, 0, 210, 15, 'F'); // Faixa vermelha de 15mm de altura
        
        // Espaçamento após header
        $this->Ln(20);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
    
    // Função para texto com quebra de linha melhorada
    function MultiCellUTF8($w, $h, $txt, $border=0, $align='J', $fill=false) {
        // Converter para UTF-8 se necessário
        if (!mb_check_encoding($txt, 'UTF-8')) {
            $txt = utf8_encode($txt);
        }
        $this->MultiCell($w, $h, $txt, $border, $align, $fill);
    }
}

// Geração do token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['csrf'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo json_encode(['token' => $_SESSION['csrf_token']]);
    exit;
}

// Download seguro do recibo
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download'])) {
    $codigo = $_GET['download'];
    $stmt = $pdo->prepare('SELECT caminho_pdf, expirado, criado_em FROM recibo_emitido WHERE codigo = ?');
    $stmt->execute([$codigo]);
    $recibo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$recibo) {
        http_response_code(404); die('Recibo não encontrado.');
    }
    if ($recibo['expirado'] || strtotime($recibo['criado_em']) < strtotime('-7 days')) {
        http_response_code(410); die('Recibo expirado.');
    }
    $file = $recibo['caminho_pdf'];
    if (!file_exists($file)) {
        http_response_code(404); die('Arquivo não encontrado.');
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="recibo_'.$codigo.'.pdf"');
    header('Content-Length: '.filesize($file));
    readfile($file);
    exit;
}

// Geração do recibo via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        json_response(false, 'Token de segurança inválido.');
    }
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';
    if (strlen($cpf) !== 11 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$data_inicio || !$data_fim) {
        json_response(false, 'Dados inválidos.');
    }
    if ($data_fim < $data_inicio) {
        json_response(false, 'A data final não pode ser anterior à inicial.');
    }
    // Buscar sócio
    $stmt = $pdo->prepare('SELECT s.id_socio, p.nome FROM pessoa p INNER JOIN socio s ON p.id_pessoa = s.id_pessoa WHERE REPLACE(REPLACE(p.cpf, ".", ""), "-", "") = ?');
    $stmt->execute([$cpf]);
    $socio = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$socio) json_response(false, 'Sócio não encontrado.');
    // Buscar contribuições
    $stmt = $pdo->prepare('SELECT SUM(c.valor) as total_valor, COUNT(*) as total_contribuicoes FROM contribuicao_log c WHERE c.id_socio = ? AND c.data_pagamento IS NOT NULL AND c.data_pagamento BETWEEN ? AND ? AND c.status_pagamento = 1');
    $stmt->execute([$socio['id_socio'], $data_inicio, $data_fim]);
    $contrib = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$contrib || !$contrib['total_valor']) json_response(false, 'Nenhuma contribuição paga no período.');
    $valor_total = (float)$contrib['total_valor'];
    $total_contribuicoes = (int)$contrib['total_contribuicoes'];
    // Gerar código único
    $codigo = bin2hex(random_bytes(8));
    $pdf_dir = dirname(__DIR__) . '/pdfs/';
    if (!is_dir($pdf_dir)) {
        mkdir($pdf_dir, 0777, true);
    }
    if (!is_writable($pdf_dir)) {
        json_response(false, 'PDF directory not writable');
    }
    $pdf_path = $pdf_dir. 'recibo_' .$codigo. '.pdf';
    
    // Gerar PDF com formatação melhorada
    $pdf = new ReciboPDF();
    $pdf->AddPage();
    
    // Título principal
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 15, 'RECIBO DE', 0, 1, 'C');
    
    // Subtítulo "DOAÇÕES" em vermelho
    $pdf->SetFont('Arial', 'B', 28);
    $pdf->SetTextColor(220, 38, 38); // Vermelho
    $pdf->Cell(0, 15, 'DOAÇÕES', 0, 1, 'C');
    
    $pdf->Ln(10);
    
    // Formatação do CPF
    $cpf_formatado = substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    
    // Texto principal do recibo
    $pdf->SetFont('Arial', '', 14);
    $pdf->SetTextColor(0, 0, 0);
    
    // Quebrar o texto em partes para melhor formatação
    $ano = date('Y', strtotime($data_inicio));
    $data_inicio_formatada = date('d/m/Y', strtotime($data_inicio));
    $data_fim_formatada = date('d/m/Y', strtotime($data_fim));
    
    $texto_principal = "Recebemos de " . mb_strtoupper($socio['nome'], 'UTF-8') . " - CPF: " . $cpf_formatado . 
                      " a importância de R$ " . number_format($valor_total, 2, ',', '.') . 
                      " em doação para [NOME DA ORGANIZAÇÃO] no período de " . 
                      $data_inicio_formatada . " a " . $data_fim_formatada . ".";
    
    // Usar MultiCellUTF8 para garantir codificação correta
    $pdf->MultiCellUTF8(0, 8, $texto_principal, 0, 'J');
    
    $pdf->Ln(15);
    
    // Informações adicionais em caixa
    $pdf->SetDrawColor(220, 38, 38);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(20, $pdf->GetY(), 170, 40);
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetX(25);
    $pdf->Cell(0, 8, 'DETALHES DA DOAÇÃO:', 0, 1);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetX(25);
    $pdf->Cell(0, 6, 'Total de contribuições: ' . $total_contribuicoes, 0, 1);
    $pdf->SetX(25);
    $pdf->Cell(0, 6, 'Valor total: R$ ' . number_format($valor_total, 2, ',', '.'), 0, 1);
    $pdf->SetX(25);
    $pdf->Cell(0, 6, 'Período: ' . $data_inicio_formatada . ' a ' . $data_fim_formatada, 0, 1);
    $pdf->SetX(25);
    $pdf->Cell(0, 6, 'Código do recibo: ' . strtoupper($codigo), 0, 1);
    
    $pdf->Ln(20);
    
    // Data de emissão
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, 'Data de emissão: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    
    $pdf->Ln(10);
    
    // Mensagem de agradecimento
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(0, 8, 'Agradecemos sua valiosa contribuição!', 0, 1, 'C');
    
    $pdf->Ln(5);
    
    // Rodapé com informações legais (se aplicável)
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(128, 128, 128);
    $pdf->MultiCellUTF8(0, 4, 'Este recibo é válido como comprovante de doação. Mantenha-o em seus arquivos para fins de declaração de imposto de renda, se aplicável.', 0, 'C');
    
    // Salvar PDF
    $pdf->Output('F', $pdf_path);
    
    // Salvar registro
    $stmt = $pdo->prepare('INSERT INTO recibo_emitido (codigo, id_socio, email, data_inicio, data_fim, valor_total, total_contribuicoes, data_geracao, caminho_pdf) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)');
    $stmt->execute([$codigo, $socio['id_socio'], $email, $data_inicio, $data_fim, $valor_total, $total_contribuicoes, $pdf_path]);
    
    // Enviar email usando o EmailService
    try {
        $emailService = new EmailService($pdo);
        
        $link = WWW."html/contribuicao/controller/ReciboController.php?download=$codigo";
        $assunto = "Seu recibo de doação - " . ($emailService->getConfiguracoes()['smtp_from_name'] ?: 'WeGIA');
        
        $mensagemHtml = "
        <h2>Recibo de Doação</h2>
        <p>Olá, <strong>" . htmlspecialchars($socio['nome']) . "</strong>!</p>
        
        <p>Seu recibo de doação foi gerado com sucesso. Segue abaixo o link para download:</p>
        
        <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc2626;'>
            <h3>Detalhes da Doação:</h3>
            <ul>
                <li><strong>Período:</strong> " . date('d/m/Y', strtotime($data_inicio)) . " a " . date('d/m/Y', strtotime($data_fim)) . "</li>
                <li><strong>Total de contribuições:</strong> " . $total_contribuicoes . "</li>
                <li><strong>Valor total:</strong> R$ " . number_format($valor_total, 2, ',', '.') . "</li>
                <li><strong>Código do recibo:</strong> " . strtoupper($codigo) . "</li>
            </ul>
        </div>
        
        <p style='text-align: center; margin: 30px 0;'>
            <a href='$link' style='background-color: #dc2626; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                📄 BAIXAR RECIBO
            </a>
        </p>
        
        <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
            <p><strong>⚠️ Importante:</strong></p>
            <ul>
                <li>Este link expira em <strong>7 dias</strong></li>
                <li>Mantenha este recibo em seus arquivos</li>
                <li>Pode ser usado para declaração de imposto de renda, se aplicável</li>
            </ul>
        </div>
        
        <p>Agradecemos imensamente sua valiosa contribuição! 💚</p>
        
        <hr style='margin: 30px 0;'>
        <p style='font-size: 12px; color: #666;'>
            <em>Este email foi gerado automaticamente. Em caso de dúvidas, entre em contato conosco.</em>
        </p>
        ";
        
        $resultadoEmail = $emailService->enviarEmail($email, $assunto, $mensagemHtml, $socio['nome']);
        
        if ($resultadoEmail['success']) {
            json_response(true, "Recibo gerado e enviado com sucesso para $email. Código: $codigo");
        } else {
            // Se falhou o envio do email, ainda retorna sucesso mas informa sobre o problema
            json_response(true, "Recibo gerado com sucesso (Código: $codigo), mas houve um problema no envio do email: " . $resultadoEmail['message']);
        }
        
    } catch (Exception $e) {
        // Se houve erro no email, ainda retorna sucesso da geração do recibo
        json_response(true, "Recibo gerado com sucesso (Código: $codigo), mas houve um erro no envio do email: " . $e->getMessage());
    }
}

json_response(false, 'Requisição inválida.');
?>