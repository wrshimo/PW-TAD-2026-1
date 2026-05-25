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

function render_header(string $active = 'home'): void {
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

          <li class="nav-item dropdown">
            <a class="nav-link <?= $isAdmin ?> dropdown-toggle" href="#" id="navbarDropdown" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="/admin/">Produtos</a></li>
              <li><div class="dropdown-divider"></div></li>
              <li><a class="dropdown-item" href="/admin/logout.php">Logout</a></li>
            </ul>
          </li>
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