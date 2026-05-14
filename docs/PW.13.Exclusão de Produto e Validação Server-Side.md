# Gabarito do Laboratório: Excluindo Produtos e Segurança Server-Side

Este documento contém o roteiro resumido da implementação e o código-fonte completo (gabarito) dos arquivos modificados durante a aula prática.

---

## 1. Roteiro de Implementação

### Passo 1: Interface de Exclusão (Front-end)
No arquivo `admin/admin.js`, modificamos a função `renderRows()` que constrói a tabela HTML. Adicionamos o botão "Excluir" com a classe `btn-excluir` e o atributo `data-id` para identificar qual produto o usuário deseja apagar.

### Passo 2: O Evento de Exclusão e Confirmação
Como a tabela é montada dinamicamente, aplicamos a **Delegação de Eventos** anexando um evento de `click` no `tbody`. Ao interceptar o clique no botão de exclusão, utilizamos `confirm()` para prevenir exclusões acidentais. Após a confirmação, acionamos a Fetch API enviando o método `DELETE` para nossa rota no back-end e, em caso de sucesso, recarregamos a listagem de produtos.

### Passo 3: Rota DELETE na API (Back-end)
Dentro de `api/produtos.php`, adicionamos um bloco lógico `if ($method === 'DELETE')`. O código força a conversão do parâmetro `id` em um inteiro (casting) e, se o ID for válido, executa uma instrução SQL segura (Prepared Statement) pelo PDO. Em seguida, validamos se o banco afetou alguma linha através do `$stmt->rowCount()`.

### Passo 4: Segurança no POST e PUT (Sanitização e Validação)
Para prevenir falhas de integridade e vulnerabilidades XSS, refatoramos os fluxos de criação e edição:
1. **Validação**: Aplicamos `filter_var(..., FILTER_VALIDATE_FLOAT)` para o campo de preço.
2. **Sanitização**: Passamos os campos de entrada textual (`nome`, `descricao`, `categoria`) pela função `htmlentities()` para transformar marcações HTML em texto inofensivo.
3. **Confirmação**: Usamos `rowCount()` logo após os métodos `$stmt->execute()` para confirmar que os registros foram criados ou manipulados com sucesso no banco de dados.

---

## 2. Código-Fonte Completo dos Arquivos

### Arquivo: `admin/admin.js`

```javascript
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('produtos-tbody');
    const alertBox = document.getElementById('admin-alert');
  
    const formatBRL = (value) =>
      Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  
    function showAlert(type, message) {
      alertBox.innerHTML = `
        <div class="alert alert-${type}">${message}</div>
      `;
    }
  
    function renderRows(produtos) {
      if (!Array.isArray(produtos) || produtos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-muted">Nenhum produto cadastrado.</td></tr>';
        return;
      }
  
      tbody.innerHTML = produtos
        .map(
          (p) => `
          <tr>
            <td>${p.id}</td>
            <td>${p.nome}</td>
            <td>${p.categoria}</td>
            <td class="text-end">${formatBRL(p.preco)}</td>
            <td>
              <a class="btn btn-sm btn-outline-primary" href="/admin/editar.php?id=${p.id}">
                <i class="bi bi-pencil-square"></i> Editar
              </a>
              <button class="btn btn-sm btn-outline-danger btn-excluir" data-id="${p.id}">
                <i class="bi bi-trash"></i> Excluir
              </button>
            </td>
          </tr>
        `
        )
        .join('');
    }
  
    // Função para encapsular a listagem (reaproveitável)
    function loadProdutos() {
      fetch('/api/produtos.php')
        .then((r) => r.json())
        .then(renderRows)
        .catch((err) => {
          console.error(err);
          showAlert('danger', 'Falha ao carregar produtos via API.');
        });
    }

    // Carregamento inicial da tabela
    loadProdutos();

    // Delegação de evento para exclusão de produto
    tbody.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-excluir');
      if (!btn) return;
      
      const id = btn.getAttribute('data-id');
      if (!confirm(`Tem certeza que deseja excluir o produto ID ${id}?`)) return;
      
      // Disparo da requisição HTTP DELETE
      fetch(`/api/produtos.php?id=${id}`, { method: 'DELETE' })
        .then(r => r.json())
        .then(data => {
          if (data.error) throw new Error(data.error);
          showAlert('success', 'Produto excluído com sucesso!');
          loadProdutos(); // Recarrega os dados para sumir com o produto apagado
        })
        .catch(err => showAlert('danger', err.message));
    });
});
```

---

### Arquivo: `api/produtos.php`

```php
<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/http_json.php';

// api/produtos.php
// GET    /api/produtos.php            -> lista
// GET    /api/produtos.php?id=1       -> detalhe
// POST   /api/produtos.php            -> cria (campos via $_POST)
// PUT    /api/produtos.php?id=1       -> atualiza (campos via body)
// DELETE /api/produtos.php?id=1       -> exclui

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
  if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id > 0) {
      $stmt = $pdo->prepare('SELECT id, nome, preco, categoria, imagem, descricao FROM produtos WHERE id = :id');
      $stmt->execute([':id' => $id]);
      $produto = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$produto) {
        json_response(['error' => 'Produto não encontrado'], 404);
      }

      json_response($produto);
    }

    $stmt = $pdo->query('SELECT id, nome, preco, categoria, imagem, descricao FROM produtos ORDER BY id DESC');
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_response($produtos);
  }

  if ($method === 'POST') {
    $data = $_POST;

    $missing = require_fields($data, ['nome', 'descricao', 'categoria', 'preco', 'imagem']);
    if ($missing) {
      json_response(['error' => 'Campos obrigatórios ausentes', 'missing' => $missing], 422);
    }

    // 1. Validando o Preço (float obrigatório)
    $precoFiltrado = filter_var($data['preco'], FILTER_VALIDATE_FLOAT);
    if ($precoFiltrado === false) {
      json_response(['error' => 'Preço inválido'], 422);
    }

    // 2. Sanitizando contra XSS (Protegendo textos de código HTML)
    $nome = htmlentities(trim($data['nome']), ENT_QUOTES, 'UTF-8');
    $descricao = htmlentities(trim($data['descricao']), ENT_QUOTES, 'UTF-8');
    $categoria = htmlentities(trim($data['categoria']), ENT_QUOTES, 'UTF-8');

    $stmt = $pdo->prepare(
      'INSERT INTO produtos (nome, descricao, categoria, preco, imagem)
       VALUES (:nome, :descricao, :categoria, :preco, :imagem)'
    );

    $stmt->execute([
      ':nome' => $nome,
      ':descricao' => $descricao,
      ':categoria' => $categoria,
      ':preco' => (float)$precoFiltrado,
      ':imagem' => trim($data['imagem']),
    ]);

    // 3. Confirmando a inserção no banco
    if ($stmt->rowCount() === 0) {
      json_response(['error' => 'Falha ao inserir o produto'], 500);
    }

    $id = (int)$pdo->lastInsertId();
    json_response(['message' => 'Produto criado', 'id' => $id], 201);
  }

  if ($method === 'PUT') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
      json_response(['error' => 'Parâmetro id é obrigatório'], 400);
    }

    // Para PUT, PHP não preenche $_POST.
    $data = get_request_body_params();

    $missing = require_fields($data, ['nome', 'descricao', 'categoria', 'preco', 'imagem']);
    if ($missing) {
      json_response(['error' => 'Campos obrigatórios ausentes', 'missing' => $missing], 422);
    }

    // 1. Validando o Preço
    $precoFiltrado = filter_var($data['preco'], FILTER_VALIDATE_FLOAT);
    if ($precoFiltrado === false) {
      json_response(['error' => 'Preço inválido'], 422);
    }

    // 2. Sanitizando contra XSS
    $nome = htmlentities(trim($data['nome']), ENT_QUOTES, 'UTF-8');
    $descricao = htmlentities(trim($data['descricao']), ENT_QUOTES, 'UTF-8');
    $categoria = htmlentities(trim($data['categoria']), ENT_QUOTES, 'UTF-8');

    $stmt = $pdo->prepare(
      'UPDATE produtos
         SET nome = :nome,
             descricao = :descricao,
             categoria = :categoria,
             preco = :preco,
             imagem = :imagem
       WHERE id = :id'
    );

    $stmt->execute([
      ':nome' => $nome,
      ':descricao' => $descricao,
      ':categoria' => $categoria,
      ':preco' => (float)$precoFiltrado,
      ':imagem' => trim($data['imagem']),
      ':id' => $id,
    ]);

    // 3. Confirmando a alteração
    if ($stmt->rowCount() === 0) {
      // Retorna sucesso para UX pois o usuário pode ter salvo sem mudar valores reais
      json_response(['message' => 'Nenhuma alteração detectada', 'id' => $id]);
    }

    json_response(['message' => 'Produto atualizado com sucesso', 'id' => $id]);
  }

  // Novo Bloco: O método DELETE do CRUD
  if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Validação se o ID é razoável para o banco de dados
    if ($id <= 0) {
      json_response(['error' => 'Parâmetro ID é inválido ou ausente'], 400);
    }

    // Operação via PDO Prepared Statements (segurança contra SQLi)
    $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
    $stmt->execute([':id' => $id]);

    // Validando se o produto de fato existia para ser deletado
    if ($stmt->rowCount() === 0) {
      json_response(['error' => 'Produto não encontrado para exclusão'], 404);
    }

    // Resposta HTTP de Sucesso para o Front-end
    json_response(['message' => 'Produto excluído com sucesso']);
  }

  json_response(['error' => 'Método não suportado'], 405);
} catch (PDOException $e) {
  error_log('API erro: ' . $e->getMessage());
  json_response(['error' => 'Erro interno ao acessar o banco'], 500);
}
```
