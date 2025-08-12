<?php
/**
 * DAO para gerenciamento das configurações de email SMTP
 * 
 * Esta classe é responsável apenas pelas operações de banco de dados
 * relacionadas às configurações de email SMTP.
 */

require_once 'Conexao.php';

class EmailConfigDAO {
    private $pdo;
    
    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: Conexao::connect();
    }
    
    /**
     * Obter configurações SMTP ativas do banco de dados
     */
    public function obterConfiguracaoAtiva() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM smtp_config WHERE smtp_ativo = 1 LIMIT 1");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao obter configurações SMTP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Salvar configurações SMTP no banco de dados
     */
    public function salvarConfiguracao($config) {
        try {
            $this->pdo->beginTransaction();
            
            // Verifica se já existe uma configuração ativa
            $stmt = $this->pdo->prepare("SELECT smtp_id FROM smtp_config WHERE smtp_ativo = 1 LIMIT 1");
            $stmt->execute();
            $configExistente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($configExistente) {
                // Atualiza a configuração existente
                if ($config['smtp_ativo']) {
                    $stmt = $this->pdo->prepare("
                        UPDATE smtp_config SET 
                            smtp_host = :host,
                            smtp_port = :port,
                            smtp_user = :user,
                            smtp_password = :password,
                            smtp_secure = :secure,
                            smtp_from_email = :from_email,
                            smtp_from_name = :from_name,
                            smtp_ativo = 1
                        WHERE smtp_id = :id
                    ");
                    
                    $stmt->execute([
                        ':host' => $config['host'],
                        ':port' => $config['port'],
                        ':user' => $config['user'],
                        ':password' => $config['password'],
                        ':secure' => $config['secure'],
                        ':from_email' => $config['from_email'],
                        ':from_name' => $config['from_name'],
                        ':id' => $configExistente['smtp_id']
                    ]);
                } else {
                    // Desativa a configuração existente
                    $stmt = $this->pdo->prepare("UPDATE smtp_config SET smtp_ativo = 0 WHERE smtp_id = :id");
                    $stmt->execute([':id' => $configExistente['smtp_id']]);
                }
            } else {
                // Não existe configuração, cria uma nova apenas se SMTP estiver habilitado
                if ($config['smtp_ativo']) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO smtp_config (
                            smtp_host, smtp_port, smtp_user, smtp_password, 
                            smtp_secure, smtp_from_email, smtp_from_name, smtp_ativo
                        ) VALUES (
                            :host, :port, :user, :password, :secure, 
                            :from_email, :from_name, 1
                        )
                    ");
                    
                    $stmt->execute([
                        ':host' => $config['host'],
                        ':port' => $config['port'],
                        ':user' => $config['user'],
                        ':password' => $config['password'],
                        ':secure' => $config['secure'],
                        ':from_email' => $config['from_email'],
                        ':from_name' => $config['from_name']
                    ]);
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erro ao salvar configurações SMTP: " . $e->getMessage());
        }
    }
    
    /**
     * Desativar todas as configurações SMTP
     */
    public function desativarTodasConfiguracoes() {
        try {
            $stmt = $this->pdo->prepare("UPDATE smtp_config SET smtp_ativo = 0");
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao desativar configurações SMTP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se existe alguma configuração ativa
     */
    public function existeConfiguracaoAtiva() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM smtp_config WHERE smtp_ativo = 1");
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar configuração ativa: " . $e->getMessage());
            return false;
        }
    }
}
?>