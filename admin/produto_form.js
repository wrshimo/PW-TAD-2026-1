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