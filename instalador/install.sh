#!/bin/bash
LOCALIP=`hostname -I | xargs`
ADDRESS="http://$LOCALIP/WeGIA/instalador/"

check_debian_12() {
    # Obtém a versão do Debian do arquivo /etc/os-release
    local VERSION_CODENAME=$(cat /etc/os-release | grep VERSION_CODENAME | cut -d'=' -f2)

    # Verifica se a versão é "bookworm"
    if [[ "$VERSION_CODENAME" != "bookworm" ]]; then
        echo "Este script só pode ser executado no Debian 12 (Bookworm)."
        exit 1
    else
        echo "Debian 12 [ok]"
    fi
}

add_backports_repo() {
    local BACKPORTS="deb http://deb.debian.org/debian bookworm-backports main contrib non-free non-free-firmware"

    if ! grep -q "bookworm-backports" /etc/apt/sources.list; then
        echo "$BACKPORTS" | tee -a /etc/apt/sources.list
        echo "Adding Backport Repository [ok]"
    else
        echo "Debian 12 Backports Repository [ok]"
    fi
}

install_deps(){
    apt update
    apt install sudo git curl dialog -y
    apt install python3-certbot-apache -y
    apt install mariadb-server -y
    apt install apache2  php8.2 php8.2-cli php8.2-common php8.2-curl php8.2-gd php8.2-intl php8.2-mbstring php8.2-mysql php8.2-opcache php8.2-readline php8.2-soap php8.2-xml php8.2-xmlrpc php8.2-zip -y
    apt install -t bookworm-backports libapache2-mod-qos libpcre3 libpcre3-dev libapache2-mod-evasive -y
}

download_wegia(){
    sudo -u www-data git -C /tmp clone https://github.com/LabRedesCefetRJ/WeGIA.git
    mv /tmp/WeGIA /var/www/

    mkdir -p /var/www/bkpWeGIA
    chown www-data:www-data /var/www/bkpWeGIA -R
}

conf_wegia_internet(){
    echo "Configurando"
cat <<EOF > /etc/apache2/sites-available/wegia.conf
<VirtualHost *:80>
    ServerName              wegia.instituicao.org 
    DocumentRoot            /var/www/WeGIA
    #ErrorDocument 404       http://wegia.instituicao.org/
    #ErrorDocument 403       http://wegia.instituicao.org/

    <Directory /var/www/WeGIA>
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>

    <IfModule mod_ratelimit.c>
    <Location />
        SetOutputFilter RATE_LIMIT
        SetEnv rate-limit 500
    </Location>
    </IfModule>

    <IfModule mod_evasive20.c>
        DOSHashTableSize    2048
        DOSPageCount        5
        DOSSiteCount        10
        DOSPageInterval     3
        DOSSiteInterval     3
        DOSBlockingPeriod   10
        DOSLogDir           "/var/log/apache2"
    </IfModule>

    <IfModule mod_qos.c>
        QS_SrvMaxConn 100
        QS_SrvMaxConnClose 120
        QS_SrvMaxConnPerIP 10
    </IfModule>
</VirtualHost>
EOF

    echo "Apache VirtualHost configurado"

    NEW_SERVER_NAME=$(dialog  --title "Configuração do Virtual Host" --inputbox "Informe o endereço na internet do servidor (ex: wegia.minhainstituicao.org.br)" 10 60  3>&1 1>&2 2>&3)
    if [[ -z "$NEW_SERVER_NAME" ]]; then
        dialog --msgbox "Nenhum endereço informado. A instalação será abortada." 6 60
        exit 1
    fi

    sed -i "s/wegia.instituicao.org/$NEW_SERVER_NAME/" /etc/apache2/sites-available/wegia.conf

    a2enmod evasive
    a2enmod ratelimit
    a2enmod qos
    a2ensite wegia.conf
        
    ADDRESS="http://$NEW_SERVER_NAME/instalador/"
    systemctl reload apache2


    dialog --title "Certificados HTTPS" --yesno "Instalar certificado HTTPS (altamente recomendado)" 6 60

    # Verifica se o usuário aceitou os termos
    if [ $? -eq 0 ]; then
        certbot --apache -d $NEW_SERVER_NAME
        ADDRESS="https://$NEW_SERVER_NAME/instalador/"
        systemctl reload apache2
    else
        clear
    fi
}

conf_wegia_local(){
    ln -s /var/www/WeGIA /var/www/html/WeGIA
    a2enmod ssl
    a2ensite default-ssl.conf 
    systemctl restart apache2
}

create_database(){
    DB_USER=$(dialog --title "Configuração do Banco de Dados" --inputbox "Digite um nome de usuário:" 8 40 3>&1 1>&2 2>&3)
    DB_PASSWORD=$(dialog --title "Configuração do Banco de Dados" --insecure --passwordbox "Digite uma senha de usuário:" 8 40 3>&1 1>&2 2>&3)
    clear

    mysql -u root -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost'; ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS wegia;"
    mysql -u root -e "GRANT ALL PRIVILEGES ON wegia.* TO '$DB_USER'@'localhost' WITH GRANT OPTION;"
    mysql -u root -e "FLUSH PRIVILEGES;"
}


dialog --title "Aceitação de Licença" --yesno "\
Bem-vindo ao instalador do WeGIA! Este software está licenciado sob a Licença Pública Geral GNU (GPL).
Você está prestes a instalar o WeGIA. Ao continuar, você concorda com os termos de licenciamento e aceita as responsabilidades pelo uso do software.\n
O WeGIA é fornecido no estado em que se encontra, sem garantias de qualquer tipo.\n
Você aceita os termos de licenciamento do WeGIA?" 15 60

# Verifica se o usuário aceitou os termos
if [ $? -eq 0 ]; then
    clear
else
    dialog --msgbox "Você não aceitou os termos. A instalação será abortada." 6 60
    clear
    exit 1
fi

dialog --title "Escolha o Tipo de Instalação" --menu "Escolha a opção desejada:" 15 60 2 \
    1 "Instalação Local" \
    2 "Instalação em Servidor de Internet" 2>/tmp/menu_choice

CHOICE=$(< /tmp/menu_choice)

# Verifica a escolha do usuário
case $CHOICE in
    1)
        dialog --msgbox "Você escolheu Instalação Local. Continuando..." 6 60
        clear
        check_debian_12
        add_backports_repo
        install_deps
        download_wegia
        conf_wegia_local
        create_database
        ;;
    2)
        dialog --msgbox "Você escolheu Instalação em Servidor de Internet. Continuando..." 6 60
        clear
        check_debian_12
        add_backports_repo
        install_deps
        download_wegia
        conf_wegia_internet
        create_database
        ;;
    *)
        dialog --msgbox "Opção inválida. A instalação será abortada." 6 60
        exit 1
        ;;
esac

echo "acesse: $ADDRESS e termine a instalação!"
