<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

render_head('Editar Produto');
render_header('admin');
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
        <input class="form-control" type="url" name="imagem" required>
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