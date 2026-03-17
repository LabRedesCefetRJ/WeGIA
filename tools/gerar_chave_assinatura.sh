#!/bin/bash
# Script para gerar chave privada de assinatura de backup conforme padrão do sistema WeGIA
# Caminho padrão conforme BACKUP_SIGNING_KEY_FILE

BKP_DIR="/var/www/public_html/bkpWeGIA"
KEY_FILE="$BKP_DIR/.backup_signing_private.pem"

# Cria diretório se não existir
if [ ! -d "$BKP_DIR" ]; then
    mkdir -p "$BKP_DIR"
    echo "Diretório $BKP_DIR criado."
fi

# Verifica se já existe uma chave privada
if [ -f "$KEY_FILE" ]; then
    echo "[ERRO] Já existe uma chave privada em $KEY_FILE. Operação abortada para evitar sobrescrever a chave existente."
    exit 1
fi

# Gera chave privada RSA 2048 bits
openssl genpkey -algorithm RSA -out "$KEY_FILE" -pkeyopt rsa_keygen_bits:2048
chmod 600 "$KEY_FILE"
echo "Chave privada criada em $KEY_FILE."

# Permissão para www-data acessar (ajuste conforme usuário do webserver)
chown www-data:www-data "$KEY_FILE"
setfacl -m u:www-data:r "$KEY_FILE"

echo "Pronto. Chave de assinatura de backup configurada."
