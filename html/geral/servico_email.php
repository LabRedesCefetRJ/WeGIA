<?php
/**
 * Serviço de Email usando PHPMailer
 * 
 * Esta classe gerencia o envio de emails através de SMTP configurado no sistema.
 * As configurações são armazenadas na tabela selecao_paragrafo (mudar depois) do banco de dados.
 */

$autoload_paths = [
    __DIR__ . '/../contribuicao/vendor/autoload.php',
    dirname(__DIR__) . '/contribuicao/vendor/autoload.php',
    dirname(dirname(__DIR__)) . '/html/contribuicao/vendor/autoload.php'
];

$autoload_loaded = false;
foreach ($autoload_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoload_loaded = true;
        break;
    }
}

if (!$autoload_loaded) {
    throw new Exception('PHPMailer autoload não encontrado. Verifique se o Composer foi executado no diretório html/contribuicao/');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $pdo;
    private $config;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->carregarConfiguracoes();
    }
    
    /**
     * Carrega as configurações SMTP do banco de dados
     */
    private function carregarConfiguracoes() {
        $campos = [
            'smtp_enabled',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
            'smtp_from_email',
            'smtp_from_name'
        ];
        
        $this->config = [];
        
        foreach ($campos as $campo) {
            $stmt = $this->pdo->prepare("SELECT paragrafo FROM selecao_paragrafo WHERE nome_campo = ?");
            $stmt->execute([$campo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->config[$campo] = $result ? $result['paragrafo'] : '';
        }
        
        // Valores padrão
        if (empty($this->config['smtp_port'])) {
            $this->config['smtp_port'] = '587';
        }
        if (empty($this->config['smtp_encryption'])) {
            $this->config['smtp_encryption'] = 'tls';
        }
    }
    
    /**
     * Verifica se o SMTP está habilitado
     */
    public function isEnabled() {
        return $this->config['smtp_enabled'] === '1';
    }
    
    /**
     * Verifica se as configurações básicas estão preenchidas
     */
    public function isConfigured() {
        return !empty($this->config['smtp_host']) && 
               !empty($this->config['smtp_username']) && 
               !empty($this->config['smtp_password']) &&
               !empty($this->config['smtp_from_email']) &&
               filter_var($this->config['smtp_from_email'], FILTER_VALIDATE_EMAIL);
    }
    public function enviarEmail($destinatario, $assunto, $mensagem, $nomeDestinatario = '', $anexos = []) {
        try {
            //Verificar se está habilitado
            if (!$this->isEnabled()) {
                return [
                    'success' => false,
                    'message' => 'O envio de emails está desabilitado no sistema'
                ];
            }
            
            //Verificar se está configurado
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Configurações SMTP incompletas. Verifique as configurações de email.'
                ];
            }
            
            //Criar instância do PHPMailer
            $mail = new PHPMailer(true);
            
            //Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->Port = (int)$this->config['smtp_port'];
            
            //Configurar criptografia
            if ($this->config['smtp_encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($this->config['smtp_encryption'] === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            //Configurações de charset e encoding
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            
            //Configurações do remetente
            $mail->setFrom(
                $this->config['smtp_from_email'], 
                $this->config['smtp_from_name'] ?: 'WeGIA'
            );
            
            //Configurações do destinatário
            $mail->addAddress($destinatario, $nomeDestinatario);
            
            //Configurações da mensagem
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $this->formatarMensagem($mensagem);
            
            //Adicionar anexos se fornecidos
            foreach ($anexos as $anexo) {
                if (file_exists($anexo)) {
                    $mail->addAttachment($anexo);
                }
            }
            
            $mail->send();
            
            return [
                'success' => true,
                'message' => 'Email enviado com sucesso'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao enviar email: ' . $e->getMessage()
            ];
        }
    }
    private function formatarMensagem($mensagem) {
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Email</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #dc2626;
                    color: white;
                    padding: 20px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                }
                .content {
                    background-color: #f8f9fa;
                    padding: 30px;
                    border-radius: 0 0 5px 5px;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    padding: 10px;
                    font-size: 12px;
                    color: #666;
                }
                a {
                    color: #dc2626;
                    text-decoration: none;
                }
                a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>' . ($this->config['smtp_from_name'] ?: 'WeGIA') . '</h1>
            </div>
            <div class="content">
                ' . $mensagem . '
            </div>
            <div class="footer">
                <p>Este email foi enviado automaticamente pelo sistema WeGIA.</p>
                <p>Data: ' . date('d/m/Y H:i:s') . '</p>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    public function enviarEmailMultiplo($destinatarios, $assunto, $mensagem) {
        $enviados = 0;
        $falhas = 0;
        $erros = [];
        
        foreach ($destinatarios as $email => $nome) {
            if (is_numeric($email)) {
                $email = $nome;
                $nome = '';
            }
            
            $resultado = $this->enviarEmail($email, $assunto, $mensagem, $nome);
            
            if ($resultado['success']) {
                $enviados++;
            } else {
                $falhas++;
                $erros[] = $email . ': ' . $resultado['message'];
            }
        }
        
        return [
            'success' => $falhas === 0,
            'message' => "Enviados: $enviados, Falhas: $falhas" . 
                        ($falhas > 0 ? "\nErros: " . implode('; ', $erros) : ''),
            'enviados' => $enviados,
            'falhas' => $falhas
        ];
    }

    public function getConfiguracoes() {
        $config = $this->config;
        unset($config['smtp_password']); //Não retornar a senha
        return $config;
    }
}
?>