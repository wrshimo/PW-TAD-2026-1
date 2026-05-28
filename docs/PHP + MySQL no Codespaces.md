# PHP + MariaDB no GitHub Codespaces

Este tutorial explica como configurar um projeto PHP simples com MariaDB no GitHub Codespaces, começando de um repositório em branco.

## Estrutura do projeto

- `/index.php` - exemplo de conexão PDO e página de teste
- `/.devcontainer/` - scripts de inicialização do codespace
  - `devcontainer.json` - configurações do container
  - `post-create.sh` - install e setup inicial (MariaDB, PHP, appdb, appuser)
  - `startup.sh` - start de MariaDB e servidor PHP
  - `start-services.sh` - fallback manual (opcional)
- `/database/appdb.sql` - tabela inicial para `appdb`

---

## 1) Conteúdo dos arquivos `.devcontainer`

### `.devcontainer/devcontainer.json`

```json
{
    "postCreateCommand": "bash .devcontainer/post-create.sh",
    "postStartCommand": "bash .devcontainer/startup.sh",
    "forwardPorts": [3306, 8080]
}
```

- `postCreateCommand`: executado na primeira criação do codespace. Faz setup inicial (instala MariaDB, cria banco e usuário).
- `postStartCommand`: executado sempre que o codespace inicia. Inicia MariaDB e servidor PHP.
- `forwardPorts`: mapeia as portas expostas `3306` (MariaDB) e `8080` (servidor web embutido PHP).

### `.devcontainer/post-create.sh`

```bash
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
sudo mysql -e "CREATE DATABASE IF NOT EXISTS appdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# Criar/ajustar user para localhost, 127.0.0.1 e wildcard
sudo mysql -e "CREATE USER IF NOT EXISTS 'appuser'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('app_pass');"
sudo mysql -e "CREATE USER IF NOT EXISTS 'appuser'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING PASSWORD('app_pass');"
sudo mysql -e "CREATE USER IF NOT EXISTS 'appuser'@'%' IDENTIFIED VIA mysql_native_password USING PASSWORD('app_pass');"
sudo mysql -e "ALTER USER 'appuser'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('app_pass');"
sudo mysql -e "ALTER USER 'appuser'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING PASSWORD('app_pass');"
sudo mysql -e "ALTER USER 'appuser'@'%' IDENTIFIED VIA mysql_native_password USING PASSWORD('app_pass');"
sudo mysql -e "GRANT ALL PRIVILEGES ON appdb.* TO 'appuser'@'localhost';"
sudo mysql -e "GRANT ALL PRIVILEGES ON appdb.* TO 'appuser'@'127.0.0.1';"
sudo mysql -e "GRANT ALL PRIVILEGES ON appdb.* TO 'appuser'@'%';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Importar dados da SQL se o arquivo existir
if [ -f $CODESPACE_VSCODE_FOLDER/database/appdb.sql ]; then
    echo "Importando $CODESPACE_VSCODE_FOLDER/database/appdb.sql..."
    sudo mysql appdb < $CODESPACE_VSCODE_FOLDER/database/appdb.sql
else
    echo "Arquivo $CODESPACE_VSCODE_FOLDER/database/appdb.sql não encontrado. Pulando import";
fi

# Parar MariaDB (será reiniciado pelo startup.sh)
echo "Parando MariaDB..."
sudo service mariadb stop

echo "=== Configuração inicial concluída ==="
```

- faz instalação de pacotes e preparação do MariaDB.
- cria `appdb` e `appuser` com privilégios apropriados (localhost, 127.0.0.1 e `%`).
- importa o `database/appdb.sql`, se existir.
- no final, para MariaDB; `startup.sh` fará start ao abrir o container.

### `.devcontainer/startup.sh`

```bash
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
if ! pgrep -f "php -S localhost:8080" > /dev/null; then
    echo "Iniciando servidor PHP..."
    cd "$CODESPACE_VSCODE_FOLDER"
    nohup env CODESPACE_VSCODE_FOLDER="$CODESPACE_VSCODE_FOLDER" /usr/bin/php -S localhost:8080 -t ./ > /tmp/php.log 2>&1 &
    sleep 1
    echo "Servidor PHP iniciado"
else
    echo "Servidor PHP já está rodando"
fi

echo "=== Serviços iniciados ==="
```

- exporta a variável `$CODESPACE_VSCODE_FOLDER` para garantir que o PHP tenha acesso aos caminhos corretos.
- passa a variável explicitamente ao processo PHP com `env CODESPACE_VSCODE_FOLDER`.
- inicia MariaDB e servidor básico PHP embutido.
- evita reiniciar se já estiver rodando.

### `.devcontainer/start-services.sh` (fallback opcional)

```bash
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
echo "Teste: curl http://localhost:8080/index.php"
```

- útil para diagnósticos quando a automatização do Codespaces falha.

---

## 2) Conteúdo da pasta `database`

### `/database/appdb.sql`

```sql
create table test (
    id int primary key auto_increment,
    name varchar(255) not null
);
```

- cria uma tabela de exemplo para testes (dentro de `appdb`).
- é carregada automaticamente em `post-create.sh`.

---

## 3) `index.php` (aplicação PHP)

```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olá Mundo</title>
</head>
<body>
    <h1><?php echo "Olá Mundo"; ?></h1>
<?php
// Configurações do banco de dados usando variáveis de ambiente (devcontainer)
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'appdb';
$user = getenv('DB_USER') ?: 'appuser';
$password = getenv('DB_PASSWORD') ?: 'app_pass';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $password, $options);
    echo '<p style="color: green;">Conexão com o banco de dados estabelecida com sucesso via PDO.</p>';
} catch (PDOException $e) {
    echo '<p style="color: red;">Erro de conexão PDO: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}
?>
</body>
</html>
```

- tenta conectar no MariaDB usando PDO.
- exibe sucesso/erro na tela.

---

## 4) Passo a passo para criar o Codespaces do zero

1. Crie ou abra repositório no GitHub.
2. Na raiz, crie arquivo `index.php` e adicione o conteúdo acima.
3. Crie a pasta `.devcontainer` e os três scripts:
   - `devcontainer.json`
   - `post-create.sh`
   - `startup.sh`
4. Crie a pasta `database` e arquivo `appdb.sql` com a tabela de teste.
5. Faça commit dessas alterações.
6. No GitHub, clique em **Code > Codespaces > New codespace**.
7. Aguarde o provisioning terminar.
8. Após finalizado, verifique:
   - `php -S localhost:8080 -t ./` não precisa rodar manualmente (startup faz isso)
   - acesse `http://localhost:8080`
   - deve mostrar `Conexão com o banco de dados estabelecida com sucesso via PDO.`

---

## 5) Verificações pós-start

- Verificar banco:
  ```bash
  sudo mysql -e "SHOW DATABASES LIKE 'appdb';"
  ```
- Verificar usuário:
  ```bash
  sudo mysql -e "SELECT user, host FROM mysql.user WHERE user='appuser';"
  ```
- Testar conexão manual (caso necessário):
  ```bash
  mysql -u appuser -papp_pass -h 127.0.0.1 -D appdb -e "SHOW TABLES;"
  ```

---

## 6) Cuidados e dicas

- Se `postStartCommand` falhar, rode manualmente:
  ```bash
  bash .devcontainer/start-services.sh
  ```
- Se banco não carregar, veja log PHP:
  ```bash
  cat /tmp/php.log
  ```
- Ajuste no `.env` ou variables de projeto:
  - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`

---

## Resultado

Com este setup, você terá um Codespace preparado para desenvolver em PHP com MariaDB, com configuração automática e scripts replicáveis. Boa codificação!