# Roteiro de Laboratório: Autenticação e Gerenciamento de Estado (PHP + REST)

Este roteiro contém o passo a passo detalhado para implementar segurança e controle de estado no projeto de e-commerce **Loja Virtual**.

---

## 1. Banco de Dados: Preparando a Segurança
**Objetivo:** Criar a tabela de credenciais e o primeiro acesso administrativo.

### 1.1 Criar Tabela `usuarios`
No MariaDB/phpMyAdmin, execute:
```sql
USE loja;

CREATE TABLE usuarios (
  id INT NOT NULL AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL, -- Espaço para o Hash de 60+ caracteres
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 1.2 Inserir Usuário Administrador
Vamos cadastrar o usuário `admin` com a senha `admin123` (criptografada com BCRYPT):
```sql
INSERT INTO usuarios (nome, usuario, senha)
VALUES (
  'Administrador',
  'admin',
  '$2y$10$8v8z9u7K6z8/Y9X9y8v7u6mNqFfG5hJ4k3l2m1n0o9p8q7r6s5t4u'
);
```

---

## 2. O "Cadeado": Middleware de Autenticação
**Objetivo:** Criar uma trava centralizada para reaproveitamento de código.

### 2.1 Criar `includes/auth.php`
```php
<?php
// Arquivo: includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Bloqueio: redireciona para o login se não houver sessão ativa
    header('Location: /admin/login.php');
    exit;
}
```

---

## 3. Interface de Acesso: Página de Login
**Objetivo:** Validar credenciais e iniciar a sessão do usuário.

### 3.1 Criar `admin/login.php`
```php
<?php
require_once '../conexao.php';
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Busca segura com Prepared Statements
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :u");
    $stmt->execute([':u' => $usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        // SEGURANÇA: Regenera o ID para evitar Session Hijacking
        session_regenerate_id(true);
        
        $_SESSION['logado'] = true;
        $_SESSION['admin_nome'] = $user['nome'];
        $_SESSION['admin_id'] = $user['id'];
        
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['flash'] = ['msg' => 'Usuário ou senha incorretos!', 'tipo' => 'danger'];
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <?php 
                // Exibe erro se houver flash message
                if (isset($_SESSION['flash'])) {
                    $f = $_SESSION['flash'];
                    echo "<div class='alert alert-{$f['tipo']}'>{$f['msg']}</div>";
                    unset($_SESSION['flash']);
                }
                ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-4">Acesso Administrativo</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Usuário</label>
                                <input type="text" name="usuario" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <input type="password" name="senha" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

---

## 4. Proteção de Rotas: Aplicando o Cadeado
**Objetivo:** Impedir acesso direto via URL às funções de gerenciamento.

### 4.1 Modificar arquivos na pasta `admin/`
Abra os arquivos `index.php`, `novo.php` e `editar.php` e insira o código abaixo na **primeira linha**:
```php
<?php
require_once '../includes/auth.php'; // Garante que apenas logados entrem
require_once '../conexao.php';
// ... restante do código original
```

### 4.2 Modificar a API em `api/produtos.php`
A API deve permitir leitura pública (GET), mas restringir escrita. Adicione no topo:
```php
<?php
// api/produtos.php
session_start();
$metodo = $_SERVER['REQUEST_METHOD'];

// Protege métodos de alteração
if (in_array($metodo, ['POST', 'PUT', 'DELETE'])) {
    if (!isset($_SESSION['logado'])) {
        http_response_code(401);
        echo json_encode(['erro' => 'Autenticação necessária']);
        exit;
    }
}
// ... restante da lógica da API
```

---

## 5. Experiência do Usuário: Mensagens Flash
**Objetivo:** Informar o sucesso ou erro de ações CRUD no layout.

### 5.1 Modificar `includes/layout.php`
Localize o final da tag `</nav>` dentro da função `render_header` e insira:
```php
      </div> <!-- fim do collapse navbar -->
    </div> <!-- fim do container -->
  </nav>
  
  <!-- BLOCO DE MENSAGENS FLASH -->
  <div class="container mt-3">
    <?php if (isset($_SESSION['flash'])): 
        $f = $_SESSION['flash']; ?>
        <div class="alert alert-<?= $f['tipo'] ?> alert-dismissible fade show" role="alert">
            <?= $f['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['flash']); endif; ?>
  </div>
```

---

## 6. Encerramento: Logout
**Objetivo:** Destruir o estado e sair com segurança.

### 6.1 Criar `admin/logout.php`
```php
<?php
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit;
```

---

## Resumo de Competências Atendidas:
1. **Conhecer**: Entendeu o ciclo de vida da sessão (Login -> Estado -> Logout).
2. **Fazer**: Criou a interface de login, middleware de proteção e mensagens flash.
3. **Ser**: Aplicou boas práticas de segurança (Hashing, `session_regenerate_id` e Proteção de API).
