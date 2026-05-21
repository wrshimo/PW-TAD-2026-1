<?php
require_once __DIR__ . '/../includes/layout.php';

render_head('Painel Admin - Produtos');
render_header('admin');
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/admin/admin.js"></script>

<?php render_footer(); ?>