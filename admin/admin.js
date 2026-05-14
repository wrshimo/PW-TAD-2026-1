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