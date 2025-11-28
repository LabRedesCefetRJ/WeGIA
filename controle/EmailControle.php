<?php
/**
 * Controle de Email usando PHPMailer
 * 
 * Esta classe gerencia o envio de emails através de SMTP configurado no sistema.
 * As configurações são obtidas através do EmailConfigDAO.
 */

$config_path = "config.php";
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once($config_path);
}

$autoload_paths = [
    __DIR__ . '/vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(dirname(__DIR__)) . '/vendor/autoload.php'
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
    throw new Exception('PHPMailer autoload não encontrado. Verifique se o Composer foi executado no diretório');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once ROOT . '/dao/EmailConfigDAO.php';

class EmailControle {
    private $emailConfigDAO;
    private $config;
    
    public function __construct($pdo = null) {
        $this->emailConfigDAO = new EmailConfigDAO($pdo);
        $this->carregarConfiguracoes();
    }
    
    /**
     * Obter configurações SMTP ativas do banco de dados
     */
    public function obterConfiguracoesBanco() {
        return $this->emailConfigDAO->obterConfiguracaoAtiva();
    }
    
    /**
     * Salvar configurações SMTP no banco de dados
     */
    public function salvarConfiguracoesBanco($config) {
        try {
            $resultado = $this->emailConfigDAO->salvarConfiguracao($config);
            
            // Recarrega as configurações após salvar
            $this->carregarConfiguracoes();
            
            return $resultado;
        } catch (Exception $e) {
            throw new Exception("Erro ao salvar configurações SMTP: " . $e->getMessage());
        }
    }
    
    /**
     * Validar dados de configuração SMTP
     */
    public function validarConfiguracao($dados) {
        $erros = [];
        
        $smtp_enabled = isset($dados['smtp_enabled']) ? 1 : 0;
        
        if ($smtp_enabled) {
            // Validações obrigatórias quando SMTP está habilitado
            if (empty(trim($dados['smtp_host'] ?? ''))) {
                $erros[] = 'Servidor SMTP é obrigatório';
            }
            
            $port = $dados['smtp_port'] ?? '';
            if (empty($port) || !is_numeric($port) || $port < 1 || $port > 65535) {
                $erros[] = 'Porta deve ser um número válido entre 1 e 65535';
            }
            
            $username = trim($dados['smtp_username'] ?? '');
            if (empty($username) || !filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $erros[] = 'Email de usuário deve ser um email válido';
            }
            
            if (empty(trim($dados['smtp_password'] ?? ''))) {
                $erros[] = 'Senha é obrigatória';
            }
            
            $from_email = trim($dados['smtp_from_email'] ?? '');
            if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
                $erros[] = 'Email do remetente deve ser um email válido';
            }
            
            if (empty(trim($dados['smtp_from_name'] ?? ''))) {
                $erros[] = 'Nome do remetente é obrigatório';
            }
        }
        
        return $erros;
    }
    
    /**
     * Processar dados do formulário e preparar para salvamento
     */
    public function processarDadosFormulario($dados) {
        return [
            'host' => trim($dados['smtp_host'] ?? ''),
            'port' => $dados['smtp_port'] ?? '587',
            'user' => trim($dados['smtp_username'] ?? ''),
            'password' => $dados['smtp_password'] ?? '',
            'secure' => $dados['smtp_encryption'] ?? 'tls',
            'from_email' => trim($dados['smtp_from_email'] ?? ''),
            'from_name' => trim($dados['smtp_from_name'] ?? ''),
            'smtp_ativo' => isset($dados['smtp_enabled']) ? 1 : 0
        ];
    }
    
    /**
     * Função para carregar as configurações da tabela smtp no bd
     */
    private function carregarConfiguracoes() {
        try {
            $config = $this->emailConfigDAO->obterConfiguracaoAtiva();
            
            if (!$config) {
                // Configuração padrão se não houver ativa
                $this->config = [
                    'smtp_enabled' => '0',
                    'smtp_host' => '',
                    'smtp_port' => '587',
                    'smtp_username' => '',
                    'smtp_password' => '',
                    'smtp_encryption' => 'tls',
                    'smtp_from_email' => '',
                    'smtp_from_name' => ''
                ];
                return;
            }

            $this->config = [
                'smtp_enabled' => $config['smtp_ativo'] ? '1' : '0',
                'smtp_host' => trim($config['smtp_host']),
                'smtp_port' => $config['smtp_port'],
                'smtp_username' => trim($config['smtp_user']),
                'smtp_password' => $config['smtp_password'],
                'smtp_encryption' => $config['smtp_secure'] ?: 'tls',
                'smtp_from_email' => trim($config['smtp_from_email']),
                'smtp_from_name' => trim($config['smtp_from_name'])
            ];
            
            // Valores padrão e validações
            if (empty($this->config['smtp_port']) || !is_numeric($this->config['smtp_port'])) {
                $this->config['smtp_port'] = '587';
            }
            if (empty($this->config['smtp_encryption'])) {
                $this->config['smtp_encryption'] = 'tls';
            }
            
        } catch (Exception $e) {
            // Em caso de erro, usar configuração padrão
            $this->config = [
                'smtp_enabled' => '0',
                'smtp_host' => '',
                'smtp_port' => '587',
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_encryption' => 'tls',
                'smtp_from_email' => '',
                'smtp_from_name' => ''
            ];
        }
    }
    
    public function isEnabled() {
        return $this->config['smtp_enabled'] === '1';
    }
    
    public function isConfigured() {
        return !empty($this->config['smtp_host']) && 
               !empty($this->config['smtp_username']) && 
               !empty($this->config['smtp_password']) &&
               !empty($this->config['smtp_from_email']) &&
               filter_var($this->config['smtp_from_email'], FILTER_VALIDATE_EMAIL);
    }
    
    public function enviarEmail($destinatario, $assunto, $mensagem, $nomeDestinatario = '', $anexos = []) {
        try {
            // Validações básicas
            if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Email de destinatário inválido: ' . $destinatario
                ];
            }
            
            if (empty(trim($assunto))) {
                return [
                    'success' => false,
                    'message' => 'Assunto do email não pode estar vazio'
                ];
            }
            
            if (empty(trim($mensagem))) {
                return [
                    'success' => false,
                    'message' => 'Mensagem do email não pode estar vazia'
                ];
            }
            
            if (!$this->isEnabled()) {
                return [
                    'success' => false,
                    'message' => 'O envio de emails está desabilitado no sistema'
                ];
            }
            
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Configurações SMTP incompletas. Verifique as configurações de email.'
                ];
            }
            
            $mail = new PHPMailer(true);
            
            // Configurações de debug (desabilitado em produção)
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            
            // CFG do servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->Port = (int)$this->config['smtp_port'];
            
            // Timeout para conexão
            $mail->Timeout = 30;
            $mail->SMTPKeepAlive = true;
            
            // Configurar criptografia
            if ($this->config['smtp_encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($this->config['smtp_encryption'] === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $fromName = $this->config['smtp_from_name'] ?: 'WeGIA';
            $mail->setFrom($this->config['smtp_from_email'], $fromName);

            $mail->addReplyTo($this->config['smtp_from_email'], $fromName);
            
            $mail->addAddress($destinatario, $nomeDestinatario);
            
            // Adicionar anexos se fornecidos
            if (!empty($anexos) && is_array($anexos)) {
                foreach ($anexos as $anexo) {
                    if (is_string($anexo)) {
                        $mail->addStringAttachment($anexo, 'recibo.pdf', 'base64', 'application/pdf'); // addStringAttachment() mudar para enviar um e-mail a partir da memória do servidor, ao invés de realizar uma gravação física no disco
                    }
                }
            }

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $this->formatarMensagem($mensagem);
            
            // Versão texto alternativa
            $mail->AltBody = strip_tags($mensagem);
            
            $mail->send();
            
            return [
                'success' => true,
                'message' => 'Email enviado com sucesso para ' . $destinatario
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
                    color: #2657dcff;
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
                <p>Este email foi enviado automaticamente pelo sistema.</p>
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
        unset($config['smtp_password']); // Não retornar a senha
        return $config;
    }
    
    /**
     * Recarrega as configurações do banco de dados
     * Útil quando as configurações são alteradas durante a execução
     */
    public function recarregarConfiguracoes() {
        $this->carregarConfiguracoes();
    }
}
?>