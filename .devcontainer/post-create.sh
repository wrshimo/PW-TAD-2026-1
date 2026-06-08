#!/bin/bash

# Script de configuração inicial do container
# Executado após a criação do container

# Exportar variáveis de ambiente para os processos filhos
export CODESPACE_VSCODE_FOLDER=${CODESPACE_VSCODE_FOLDER:-.}

echo "=== Iniciando configuração do container ==="

# Atualizar lista de pacotes
echo "Atualizando lista de pacotes..."
sudo apt update

# Instalar MariaDB e PHP
echo "Instalando MariaDB e PHP..."
sudo apt install -y mariadb-server php php-pdo php-mysql

# Configurar diretório de dados do MariaDB
echo "Configurando diretório de dados do MariaDB..."
sudo mkdir -p $CODESPACE_VSCODE_FOLDER/.data
sudo chown mysql:mysql $CODESPACE_VSCODE_FOLDER/.data

# Configurar MariaDB para usar diretório personalizado
echo "Configurando MariaDB..."
sudo sed -i "s|datadir.*=.*|datadir = $CODESPACE_VSCODE_FOLDER/.data|" /etc/mysql/mariadb.conf.d/50-server.cnf

# Iniciar MariaDB para configuração
echo "Iniciando MariaDB para configuração..."
sudo service mariadb start
sleep 3

# Criar banco de dados e configurar usuário
echo "Criando banco de dados e configurando usuário..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS loja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# Criar/ajustar user para localhost, 127.0.0.1 e wildcard
sudo mysql -e "CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('');"
sudo mysql -e "CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING PASSWORD('');"
sudo mysql -e "CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED VIA mysql_native_password USING PASSWORD('');"
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('');"
sudo mysql -e "ALTER USER 'root'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING PASSWORD('');"
sudo mysql -e "ALTER USER 'root'@'%' IDENTIFIED VIA mysql_native_password USING PASSWORD('');"
sudo mysql -e "GRANT ALL PRIVILEGES ON loja.* TO 'root'@'localhost';"
sudo mysql -e "GRANT ALL PRIVILEGES ON loja.* TO 'root'@'127.0.0.1';"
sudo mysql -e "GRANT ALL PRIVILEGES ON loja.* TO 'root'@'%';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Importar dados da SQL se o arquivo existir
if [ -f $CODESPACE_VSCODE_FOLDER/docs/schema.sql ]; then
    echo "Importando $CODESPACE_VSCODE_FOLDER/docs/schema.sql..."
    sudo mysql loja < $CODESPACE_VSCODE_FOLDER/docs/schema.sql
else
    echo "Arquivo $CODESPACE_VSCODE_FOLDER/docs/schema.sql não encontrado. Pulando import";
fi
if [ -f $CODESPACE_VSCODE_FOLDER/docs/data.sql ]; then
    echo "Importando $CODESPACE_VSCODE_FOLDER/docs/data.sql..."
    sudo mysql loja < $CODESPACE_VSCODE_FOLDER/docs/data.sql
else
    echo "Arquivo $CODESPACE_VSCODE_FOLDER/docs/data.sql não encontrado. Pulando import";
fi

# Parar MariaDB (será reiniciado pelo startup.sh)
echo "Parando MariaDB..."
sudo service mariadb stop

echo "=== Configuração inicial concluída ==="