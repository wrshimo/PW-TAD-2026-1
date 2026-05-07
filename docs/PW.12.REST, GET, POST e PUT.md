# Roteiro da Aula: Laboratório de CRUD de Produtos (PHP, MySQL e Fetch API)

## 1. Conceitos, Termos e Tecnologias Envolvidas

Neste laboratório prático, implementamos um sistema de gerenciamento de produtos (CRUD: *Create, Read, Update, Delete*) utilizando as seguintes tecnologias e conceitos:

- **Separação de Front-end e Back-end (Arquitetura Client-Server):** O servidor PHP (back-end) não gera mais o HTML misturado com o banco de dados. Em vez disso, ele processa as regras de negócio e entrega apenas os dados "puros" em formato JSON. O navegador (front-end) se responsabiliza por consumir esses dados e desenhar a interface na tela do usuário.
- **API REST:** Um padrão arquitetural de comunicação via web. Nossa aplicação back-end expõe endpoints (rotas) que respondem a diferentes **Métodos HTTP**:
  - `GET`: Para consultar e ler os produtos.
  - `POST`: Para criar/inserir um novo produto.
  - `PUT`: Para atualizar um produto existente.
- **MariaDB / MySQL e PDO:** O banco de dados relacional onde as informações são persistidas de forma permanente. Para nos comunicarmos com ele via PHP, utilizamos a extensão PDO (PHP Data Objects), fazendo uso de *Prepared Statements* que garantem imunidade contra falhas de segurança como SQL Injection.
- **Fetch API e Assincronismo:** Função nativa do JavaScript moderno (`fetch()`) usada no front-end para enviar ou buscar dados do back-end em "background", permitindo que as tabelas e listas sejam atualizadas sem que a página inteira precise ser recarregada.
- **Bootstrap 5:** Framework CSS usado para estruturar o layout responsivo, criar formulários bonitos e padronizados sem precisar escrever folhas de estilo do zero.

---

## 2. Ilustrações Didáticas

### Arquitetura de uma API REST
A imagem abaixo ilustra como o fluxo da nossa aplicação acontece. O cliente (navegador executando nosso JS e HTML) faz uma requisição HTTP via internet (ex: POST ou GET) para o nosso Servidor (API feita em PHP). A API por sua vez comunica-se com a Base de Dados (MariaDB), processa a solicitação e retorna uma resposta, geralmente formatada em JSON.

![Arquitetura REST](https://blog.iron.io/wp-content/uploads/2020/11/REST-API.jpg)

### Tecnologias do Projeto
Nosso projeto integra tanto tecnologias de Front-end (HTML, CSS/Bootstrap, JS) quanto de Back-end (PHP e SQL/MySQL). O JS atua como a ponte conversando com o PHP.

![Tecnologias](https://res.cloudinary.com/upwork-cloud/image/upload/c_scale,w_400/v1709854605/catalog/1389884204735262720/oo11ogl7ekgmmy5x85ql.webp)

---

## 3. Códigos-fontes Completos

Abaixo estão todos os códigos-fontes finais, contendo a lógica de negócios no back-end, layout e scripts do painel administrativo, e do lado público (home).

### 3.1. API e Back-end

**`api/produtos.php`** (Controlador e Rotas REST)
```php
<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/http_json.php';

// api/produtos.php
// GET  /api/produtos.php            -> lista
// GET  /api/produtos.php?id=1       -> detalhe
// POST /api/produtos.php            -> cria (campos via $_POST)
// PUT  /api/produtos.php?id=1       -> atualiza (campos via body)

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
    // Para cumprir a competência: processar dados de formulário via POST
    $data = $_POST;

    $missing = require_fields($data, ['nome', 'descricao', 'categoria', 'preco', 'imagem']);
    if ($missing) {
      json_response(['error' => 'Campos obrigatórios ausentes', 'missing' => $missing], 422);
    }

    $stmt = $pdo->prepare(
      'INSERT INTO produtos (nome, descricao, categoria, preco, imagem)
       VALUES (:nome, :descricao, :categoria, :preco, :imagem)'
    );

    $stmt->execute([
      ':nome' => trim($data['nome']),
      ':descricao' => trim($data['descricao']),
      ':categoria' => trim($data['categoria']),
      ':preco' => (float)$data['preco'],
      ':imagem' => trim($data['imagem']),
    ]);

    $id = (int)$pdo->lastInsertId();
    json_response(['message' => 'Produto criado', 'id' => $id], 201);
  }

  if ($method === 'PUT') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
      json_response(['error' => 'Parâmetro id é obrigatório'], 400);
    }

    // Para PUT, PHP não preenche $_POST. Precisamos ler php://input.
    $data = get_request_body_params();

    $missing = require_fields($data, ['nome', 'descricao', 'categoria', 'preco', 'imagem']);
    if ($missing) {
      json_response(['error' => 'Campos obrigatórios ausentes', 'missing' => $missing], 422);
    }

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
      ':nome' => trim($data['nome']),
      ':descricao' => trim($data['descricao']),
      ':categoria' => trim($data['categoria']),
      ':preco' => (float)$data['preco'],
      ':imagem' => trim($data['imagem']),
      ':id' => $id,
    ]);

    json_response(['message' => 'Produto atualizado', 'id' => $id]);
  }

  json_response(['error' => 'Método não suportado'], 405);
} catch (PDOException $e) {
  error_log('API erro: ' . $e->getMessage());
  json_response(['error' => 'Erro interno ao acessar o banco'], 500);
}
```

**`includes/http_json.php`** (Funções auxiliares para leitura/escrita)
```php
<?php
// includes/http_json.php
// Funções utilitárias para respostas JSON e leitura de payload bruto.

function json_response($data, int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
  exit;
}

function get_request_body_params(): array {
  // Suporta: application/x-www-form-urlencoded (PUT) e JSON
  $raw = file_get_contents('php://input');
  $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

  if (str_contains($contentType, 'application/json')) {
    $parsed = json_decode($raw, true);
    return is_array($parsed) ? $parsed : [];
  }

  // default: tenta parse_str (x-www-form-urlencoded)
  $data = [];
  parse_str($raw, $data);
  return is_array($data) ? $data : [];
}

function require_fields(array $data, array $required): array {
  $missing = [];
  foreach ($required as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
      $missing[] = $field;
    }
  }
  return $missing;
}
```

**`includes/layout.php`** (Reaproveitamento do layout em todo o sistema)
```php
<?php
// includes/layout.php

function render_head(string $title): void {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php
}

function render_nav(string $active = 'home'): void {
  $isHome = $active === 'home' ? 'active' : '';
  $isAdmin = $active === 'admin' ? 'active' : '';
?>
<header>
  <nav class="navbar navbar-expand-lg bg-dark" data-bs-theme="dark">
    <div class="container">
      <a class="navbar-brand" href="/">Loja do Shimo</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"
        aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="nav">
        <ul class="nav navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link <?= $isHome ?>" href="/">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="/produtos">Produtos</a></li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">Categorias</a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item category-filter" href="#" data-category="all">Todas</a></li>
              <li><a class="dropdown-item category-filter" href="#" data-category="Eletrônicos">Eletrônicos</a></li>
              <li><a class="dropdown-item category-filter" href="#" data-category="Roupas">Roupas</a></li>
              <li><a class="dropdown-item category-filter" href="#" data-category="Livros">Livros</a></li>
              <li><a class="dropdown-item category-filter" href="#" data-category="Casa e Jardim">Casa e Jardim</a></li>
            </ul>
          </li>

          <li class="nav-item"><a class="nav-link" href="/contato">Contato</a></li>
          <li class="nav-item"><a class="nav-link <?= $isAdmin ?>" href="/admin/">Admin</a></li>
        </ul>

        <form class="d-flex" role="search" onsubmit="return false;">
          <input class="form-control me-2" type="search" id="name-search" placeholder="Buscar por nome" aria-label="Buscar">
        </form>

        <div class="d-flex align-items-center ms-3">
          <a href="#" class="text-white text-decoration-none" id="cart-icon" data-bs-toggle="popover"
            data-bs-placement="bottom" title="Carrinho">
            <i class="bi bi-cart fs-4"></i>
            <span id="cart-count" class="badge rounded-pill bg-danger position-absolute translate-middle">0</span>
          </a>
          <button id="clear-cart-btn" class="btn btn-outline-danger ms-2" data-bs-toggle="tooltip"
            title="Limpar carrinho"><i class="bi bi-trash"></i></button>
        </div>
      </div>
    </div>
  </nav>
</header>
<?php
}

function render_footer(): void {
?>
<footer class="bg-dark text-white text-center p-3 mt-4">
  <p class="mb-0">&copy; 2024 Minha Loja Virtual. Todos os direitos reservados.</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
```

### 3.2. Painel Administrativo

**`admin/index.php`** (Tela de listagem)
```php
<?php
require_once __DIR__ . '/../includes/layout.php';

render_head('Painel Admin - Produtos');
render_nav('admin');
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Painel Administrativo</h1>
    <a class="btn btn-primary" href="/admin/novo.php"><i class="bi bi-plus-lg"></i> Novo produto</a>
  </div>

  <div id="admin-alert"></div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nome</th>
              <th>Categoria</th>
              <th class="text-end">Preço</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody id="produtos-tbody">
            <tr><td colspan="5" class="text-muted">Carregando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="/admin/admin.js"></script>

<?php render_footer(); ?>
```

**`admin/admin.js`** (Lógica de busca e montagem da tabela do Admin)
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
          </td>
        </tr>
      `
      )
      .join('');
  }

  fetch('/api/produtos.php')
    .then((r) => r.json())
    .then(renderRows)
    .catch((err) => {
      console.error(err);
      showAlert('danger', 'Falha ao carregar produtos via API.');
    });
});
```

**`admin/novo.php`** (Tela de criação)
```php
<?php
require_once __DIR__ . '/../includes/layout.php';

render_head('Novo Produto');
render_nav('admin');
?>

<div class="container mt-4" style="max-width: 900px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Criar produto</h1>
    <a class="btn btn-outline-secondary" href="/admin/">Voltar</a>
  </div>

  <div id="form-alert"></div>

  <form id="produto-form" method="post">
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Nome</label>
        <input class="form-control" name="nome" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Preço</label>
        <input class="form-control" name="preco" type="number" step="0.01" min="0" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Categoria</label>
        <input class="form-control" name="categoria" placeholder="Ex.: Eletrônicos" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">URL da imagem</label>
        <input class="form-control" name="imagem" placeholder="https://..." required>
      </div>
      <div class="col-12">
        <label class="form-label">Descrição</label>
        <textarea class="form-control" name="descricao" rows="4" required></textarea>
      </div>
    </div>

    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Salvar</button>
      <button class="btn btn-outline-secondary" type="reset">Limpar</button>
    </div>
  </form>
</div>

<script src="/admin/produto_form.js"></script>
<script>
  window.__FORM_MODE__ = 'create';
</script>

<?php render_footer(); ?>
```

**`admin/editar.php`** (Tela de edição)
```php
<?php
require_once __DIR__ . '/../includes/layout.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

render_head('Editar Produto');
render_nav('admin');
?>

<div class="container mt-4" style="max-width: 900px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Editar produto</h1>
    <a class="btn btn-outline-secondary" href="/admin/">Voltar</a>
  </div>

  <div id="form-alert"></div>

  <form id="produto-form" method="post">
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Nome</label>
        <input class="form-control" name="nome" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Preço</label>
        <input class="form-control" name="preco" type="number" step="0.01" min="0" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Categoria</label>
        <input class="form-control" name="categoria" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">URL da imagem</label>
        <input class="form-control" name="imagem" required>
      </div>
      <div class="col-12">
        <label class="form-label">Descrição</label>
        <textarea class="form-control" name="descricao" rows="4" required></textarea>
      </div>
    </div>

    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Atualizar</button>
      <a class="btn btn-outline-secondary" href="/admin/">Cancelar</a>
    </div>
  </form>
</div>

<script src="/admin/produto_form.js"></script>
<script>
  window.__FORM_MODE__ = 'edit';
  window.__PRODUTO_ID__ = <?= (int)$id ?>;
</script>

<?php render_footer(); ?>
```

**`admin/produto_form.js`** (Lógica unificada de POST/PUT dos formulários de Admin)
```javascript
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('produto-form');
  const alertBox = document.getElementById('form-alert');

  function showAlert(type, message) {
    alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
  }

  function fillForm(produto) {
    form.elements.nome.value = produto.nome ?? '';
    form.elements.preco.value = produto.preco ?? '';
    form.elements.categoria.value = produto.categoria ?? '';
    form.elements.imagem.value = produto.imagem ?? '';
    form.elements.descricao.value = produto.descricao ?? '';
  }

  async function loadProduto(id) {
    const r = await fetch(`/api/produtos.php?id=${id}`);
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
  }

  // Modo edição: pré-preenche chamando a API (GET com ID)
  if (window.__FORM_MODE__ === 'edit') {
    const id = Number(window.__PRODUTO_ID__);
    if (!Number.isFinite(id) || id <= 0) {
      showAlert('warning', 'ID inválido. Volte para a listagem.');
      form.querySelector('button[type="submit"]').disabled = true;
      return;
    }

    loadProduto(id)
      .then(fillForm)
      .catch(() => showAlert('danger', 'Não foi possível carregar o produto.'));
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);

    try {
      // Create / POST
      if (window.__FORM_MODE__ === 'create') {
        const r = await fetch('/api/produtos.php', {
          method: 'POST',
          body: fd
        });
        const data = await r.json();
        if (!r.ok) throw new Error(data?.error || 'Erro');

        showAlert('success', `Produto criado com ID ${data.id}.`);
        form.reset();
        return;
      }

      // Update / PUT
      if (window.__FORM_MODE__ === 'edit') {
        const id = Number(window.__PRODUTO_ID__);
        const params = new URLSearchParams();
        for (const [k, v] of fd.entries()) {
          if (k === 'id') continue;
          params.append(k, String(v));
        }

        const r = await fetch(`/api/produtos.php?id=${id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: params.toString()
        });

        const data = await r.json();
        if (!r.ok) throw new Error(data?.error || 'Erro');

        showAlert('success', 'Produto atualizado com sucesso.');
        return;
      }

    } catch (err) {
      console.error(err);
      showAlert('danger', 'Falha ao salvar. Verifique os campos e tente novamente.');
    }
  });
});
```

### 3.3. Loja Virtual (Visão do Cliente)

**`index.php`** (Tela Principal)
```php
<?php
require_once __DIR__ . '/includes/layout.php';

render_head('Minha Loja Virtual');
render_nav('home');
?>

<div class="container mt-4">
  <section id="promo-box" class="mb-4 p-3 border rounded bg-light">
    <div>
      <h4 id="promo-title">Promoção do Dia</h4>
      <p id="promo-desc">Carregando...</p>
    </div>
  </section>

  <section id="lab-box" class="mb-4 p-3 border rounded bg-light">
    <label>Quantidade:</label>
    <input type="number" id="fake-count" value="12">
    <button id="generate-fake-btn" class="btn btn-primary">Gerar Cards</button>
    <button id="load-from-json-btn" class="btn btn-success">Recarregar do JSON</button>
  </section>

  <div class="row">
    <main class="col-md-12">
      <form id="filter-form" class="mb-4 p-3 border rounded bg-light" onsubmit="return false;">
        <div class="row align-items-end">
          <div class="col-md-3">
            <label for="price-filter" class="form-label">Preço Máximo</label>
            <input type="number" class="form-control" id="price-filter" placeholder="Digite o preço">
          </div>
          <div class="col-md-3">
            <label for="sort-order" class="form-label">Ordenar por</label>
            <select id="sort-order" class="form-select">
              <option value="default">Padrão</option>
              <option value="asc">Preço: Menor para Maior</option>
              <option value="desc">Preço: Maior para Menor</option>
            </select>
          </div>
          <div class="col-md-auto d-flex align-items-end">
            <button type="button" id="filter-btn" class="btn btn-primary">Aplicar Filtros</button>
          </div>
        </div>
      </form>

      <section id="product-list"
        class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 g-4">
        <!-- Os produtos serão inseridos aqui via JavaScript -->
      </section>

    </main>
  </div>
</div>

<!-- Modal de Confirmação (carrinho) -->
<div class="modal fade" id="confirm-clear-cart-modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Limpar Carrinho</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Tem certeza de que deseja limpar o carrinho?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirm-clear-btn">Limpar</button>
      </div>
    </div>
  </div>
</div>

<script>
  // A partir de agora, o front-end busca produtos via HTTP (API REST)
  // A variável foi mantida só para não quebrar o app.js (e para evidenciar a separação)
  const produtosDoBanco = [];
</script>
<script src="/app.js"></script>

<?php
render_footer();
```

**`app.js`** (Excerto de lógica de renderização da Home)
*(Nota: O código original de controle de carrinho e formatações foi mantido. Focaremos no carregamento da API.)*
```javascript
// app.js (trechos relevantes da requisição)

  // ... (código do carrinho, filtros e criação de card HTML omitido para brevidade) ...

  function processAndRenderProducts(products) {
    allProducts = products.map((p) => ({
      ...p,
      preco: typeof p.preco === 'string' ? parseFloat(p.preco) : p.preco
    }));
    applyFilters(); // renderiza os cards após formatar
  }

  // A Mágica de buscar do Back-end:
  function loadFromApi() {
    fetch('/api/produtos.php')
      .then((r) => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(processAndRenderProducts)
      .catch((err) => {
        console.warn('API indisponível', err);
        // Exibir fallback de erro ou carregar JSON estático
      });
  }

  // Inicializa o sistema ao carregar a página
  loadFromApi();
```

---

## 4. Desafio Final

Ao longo desta aula, você viu que transferimos a responsabilidade de "buscar os produtos no banco de dados" inteiramente para a nossa API REST (`api/produtos.php`), e nosso painel administrativo passou a consumir essa API através do `admin.js`.

**Pense e responda:**
O que precisa ser alterado para a **tela principal da loja (página inicial)** utilizar também a API para listar os produtos do banco de dados de maneira dinâmica, abandonando de vez o arquivo estático `produtos.json` ou as lógicas fixas baseadas no arquivo antigo?

Se você reparar no código do `app.js` exposto acima, já existe uma função `loadFromApi()` implementada e chamada no evento principal de carregamento da página. Verifique se compreende perfeitamente como a arquitetura client-server funciona ali e como os dados (em JSON) chegam até as funções de renderização do Bootstrap.

*Mão na massa e bons estudos!*
