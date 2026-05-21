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

  function loadProdutos() {
    fetch('/api/produtos.php')
      .then((r) => r.json())
      .then(renderRows)
      .catch((err) => {
        console.error(err);
        showAlert('danger', 'Falha ao carregar produtos via API.');
      });
  }
  loadProdutos();

  // Delegação de evento para exclusão de produto
  tbody.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-excluir');
    if (!btn) return;

    const id = btn.getAttribute('data-id');
    const nome = btn.closest('tr').querySelector('td:nth-child(2)').textContent;

    Swal.fire({
      title: 'Você tem certeza?',
      text: `Deseja realmente excluir o produto "${nome}"? Esta ação não pode ser desfeita.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sim, excluir!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        // Disparo da requisição HTTP DELETE
        fetch(`/api/produtos.php?id=${id}`, { method: 'DELETE' })
          .then(r => r.json())
          .then(data => {
            if (data.error) throw new Error(data.error);
            showAlert('success', 'Produto excluído com sucesso!');
            loadProdutos(); // Recarrega os dados para sumir com o produto apagado
          })
          .catch(err => showAlert('danger', err.message));
      }
    });
  });
});