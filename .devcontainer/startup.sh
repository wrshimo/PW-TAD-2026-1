#!/bin/bash

# Script para garantir que os serviços estejam sempre rodando
# Pode ser executado múltiplas vezes sem problemas

# Exportar variáveis de ambiente para os processos filhos
export CODESPACE_VSCODE_FOLDER=${CODESPACE_VSCODE_FOLDER:-.}

echo "=== Iniciando serviços ==="

# Verificar se MariaDB está rodando
if ! sudo service mariadb status > /dev/null 2>&1; then
    echo "Iniciando MariaDB..."
    sudo service mariadb start
    sleep 2

    echo "MariaDB iniciado"
else
    echo "MariaDB já está rodando"
fi

# Verificar se PHP está rodando
if ! pgrep -f "php -S localhost:3000" > /dev/null; then
    echo "Iniciando servidor PHP..."
    cd "$CODESPACE_VSCODE_FOLDER"
    nohup env CODESPACE_VSCODE_FOLDER="$CODESPACE_VSCODE_FOLDER" /usr/bin/php -S localhost:3000 -t ./ > /tmp/php.log 2>&1 &
    sleep 1
    echo "Servidor PHP iniciado"
else
    echo "Servidor PHP já está rodando"
fi

echo "=== Serviços iniciados ==="