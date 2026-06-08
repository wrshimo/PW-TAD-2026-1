#!/bin/bash

# Script manual para iniciar serviços
# Execute este script se o postStartCommand não funcionar automaticamente
# Uso: bash .devcontainer/start-services.sh

# Exportar variáveis de ambiente para os processos filhos
export CODESPACE_VSCODE_FOLDER=${CODESPACE_VSCODE_FOLDER:-.}

echo "=== Iniciando serviços manualmente ==="

# Dar permissões de execução aos scripts
chmod +x $CODESPACE_VSCODE_FOLDER/.devcontainer/*.sh

# Executar script de inicialização
bash $CODESPACE_VSCODE_FOLDER/.devcontainer/startup.sh

echo "=== Serviços iniciados manualmente ==="
echo "Teste: curl http://localhost:3000/index.php"