document.addEventListener('DOMContentLoaded', () => {
    // =====================================================
    // 1) ESTADO (arrays e objetos)
    // =====================================================
    /** @type {Array<{id:number, nome:string, preco:number, imagem:string, categoria:string, descricao:string}>} */
    let allProducts = [];
    let selectedCategory = 'all';
  
    /** @type {{items: Array<{id:number, nome:string, preco:number, qty:number}>}} */
    let cart = loadCart();
  
    // =====================================================
    // 2) DOM: seleção de elementos
    // =====================================================
    const cartIcon = document.getElementById('cart-icon');
    const cartCountElement = document.getElementById('cart-count');
    const nameSearch = document.getElementById('name-search');
    const priceFilter = document.getElementById('price-filter');
    const sortOrder = document.getElementById('sort-order');
    const filterBtn = document.getElementById('filter-btn');
    const productList = document.getElementById('product-list');
    const categoryFilters = document.querySelectorAll('.category-filter');
    const contactForm = document.getElementById('contact-form');
    const clearCartBtn = document.getElementById('clear-cart-btn');
    const confirmClearBtn = document.getElementById('confirm-clear-btn');
  
    // =====================================================
    // 3) Bootstrap components
    // =====================================================
    const popover = new bootstrap.Popover(cartIcon, {
      html: true,
      trigger: 'click', // Alterado para 'click' para melhor usabilidade
      placement: 'bottom',
      title: 'Resumo do Carrinho',
      content: 'Seu carrinho está vazio.',
      sanitize: false, // Permite HTML customizado (botões)
      customClass: 'cart-popover', // Adiciona classe customizada para CSS
    });
    const confirmModal = new bootstrap.Modal(
      document.getElementById('confirm-clear-cart-modal')
    );
    const tooltip = new bootstrap.Tooltip(clearCartBtn);
  
    // =====================================================
    // 4) FUNÇÕES (declaração, parâmetros, retorno)
    // =====================================================
  
    /**
     * Converte "R$ 3.599,00" -> 3599
     * @param {string} priceString O preço formatado (string).
     * @returns {number} Representação numérica do proeço formatado.
     */
    function parsePriceBRL(priceString) {
      const n = parseFloat(
        priceString
          .replace(/[^0-9,-]+/g, '')
          .replaceAll('.', '')
          .replace(',', '.')
      );
      return Number.isFinite(n) ? n : 0;
    }
  
    /**
     * Formata número em BRL.
     * Exemplo de função utilitária (reuso).
     * @param {number} value
     * @returns {string}
     */
    const formatPriceBRL = (value) =>
      value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  
    function cartCount() {
      return cart.items.reduce((sum, item) => sum + item.qty, 0);
    }
  
    function cartTotal() {
      return cart.items.reduce((sum, item) => sum + item.preco * item.qty, 0);
    }
  
    function saveCart() {
      localStorage.setItem('cart', JSON.stringify(cart));
    }
  
    function loadCart() {
      const raw = localStorage.getItem('cart');
      if (!raw) return { items: [] };
  
      try {
        const parsed = JSON.parse(raw);
        if (!parsed || !Array.isArray(parsed.items)) return { items: [] };
        return parsed;
      } catch {
        return { items: [] };
      }
    }
  
    function clearCart() {
      cart = { items: [] };
      saveCart();
      updateCartDisplay();
      confirmModal.hide();
    }
  
    function addToCart(productId) {
      const product = allProducts.find((p) => p.id === productId);
      if (!product) return;
  
      const existing = cart.items.find((i) => i.id === productId);
      if (existing) {
        existing.qty += 1;
      } else {
        cart.items.push({
          id: product.id,
          nome: product.nome,
          preco: product.preco,
          qty: 1
        });
      }
  
      saveCart();
      updateCartDisplay();
    }
  
    function removeFromCart(productId) {
      cart.items = cart.items
        .map((i) => (i.id === productId ? { ...i, qty: i.qty - 1 } : i))
        .filter((i) => i.qty > 0);
  
      saveCart();
      updateCartDisplay();
    }
  
    function renderCartPopoverHtml() {
      if (cart.items.length === 0) {
        return '<div class="text-muted">Seu carrinho está vazio.</div>';
      }
  
      const itemsHtml = cart.items
        .map(
          (item) => `
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div style="max-width:220px; overflow-wrap: break-word;">
                <div class="fw-semibold" style="font-size:0.9rem">${item.nome}</div>
                <div class="text-muted" style="font-size:0.8rem">${item.qty} × ${formatPriceBRL(item.preco)}</div>
              </div>
              <button class="btn btn-sm btn-outline-danger remove-item" data-id="${item.id}">
                <i class="bi bi-dash"></i>
              </button>
            </div>
          `
        )
        .join('');
  
      return `
        <div>
          ${itemsHtml}
          <hr class="my-2" />
          <div class="d-flex justify-content-between">
            <span class="fw-semibold">Total</span>
            <span class="fw-bold">${formatPriceBRL(cartTotal())}</span>
          </div>
          <div class="text-muted" style="font-size:0.8rem">Clique no “-” para remover 1 unidade.</div>
        </div>
      `;
    }
  
    function updateCartDisplay() {
      cartCountElement.textContent = cartCount();
      popover.setContent({ '.popover-body': renderCartPopoverHtml() });
      clearCartBtn.disabled = cart.items.length === 0;
    }
  
    // =====================================================
    // 5) Renderização (map/forEach) + DOM
    // =====================================================
  
    function createProductCard(product) {
      return `
        <div class="col">
          <div class="card h-100 shadow-sm" data-product-id="${product.id}">
            <img src="${product.imagem}" class="card-img-top" alt="${product.nome}">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">${product.nome}</h5>
              <p class="card-text fw-semibold">${formatPriceBRL(product.preco)}</p>
  
              <div class="product-details" id="details-${product.id}">
                <p class="mb-2">${product.descricao}</p>
                <span class="badge text-bg-light border">Categoria: ${product.categoria}</span>
              </div>
  
              <div class="mt-auto pt-3 d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-primary btn-details" type="button">Ver detalhes</button>
                <button class="btn btn-success btn-add" type="button">Adicionar ao carrinho</button>
              </div>
            </div>
          </div>
        </div>
      `;
    }
  
    function renderCards(products) {
      productList.innerHTML = products.map(createProductCard).join('');
    }
  
    function toggleDetails(productId) {
      const details = document.getElementById(`details-${productId}`);
      if (!details) return;
  
      details.classList.toggle('is-open');
    }
  
    // =====================================================
    // 6) Filtros (arrays: filter/sort)
    // =====================================================
  
    function applyFilters() {
      let filteredProducts = [...allProducts];
  
      // categoria
      if (selectedCategory !== 'all') {
        filteredProducts = filteredProducts.filter(
          (product) => product.categoria === selectedCategory
        );
      }
  
      // nome
      const searchTerm = nameSearch.value.toLowerCase();
      if (searchTerm) {
        filteredProducts = filteredProducts.filter((product) =>
          product.nome.toLowerCase().includes(searchTerm)
        );
      }
  
      // preço máximo
      const maxPrice = parseFloat(priceFilter.value);
      if (!Number.isNaN(maxPrice)) {
        filteredProducts = filteredProducts.filter((product) => product.preco <= maxPrice);
      }
  
      // ordenação
      const sortValue = sortOrder.value;
      if (sortValue !== 'default') {
        filteredProducts.sort((a, b) =>
          sortValue === 'asc' ? a.preco - b.preco : b.preco - a.preco
        );
      }
  
      renderCards(filteredProducts);
    }
  
    // =====================================================
    // 7) Carregamento inicial (fetch + map)
    // =====================================================
  
    function loadFromJson() {
      fetch('produtos.json')
        .then((response) => response.json())
        .then((data) => {
          // transforma preço string -> number
          allProducts = data.map((p) => ({
            ...p,
            preco: typeof p.preco === 'string' ? parsePriceBRL(p.preco) : p.preco
          }));
  
          applyFilters();
        });
    }
  
    loadFromJson();
  
    // =====================================================
    // 8) Eventos (addEventListener)
    // =====================================================

    // Garante que o popover seja atualizado sempre antes de ser exibido.
    cartIcon.addEventListener('show.bs.popover', updateCartDisplay);
  
    nameSearch.addEventListener('input', applyFilters);
    filterBtn.addEventListener('click', applyFilters);
    sortOrder.addEventListener('change', applyFilters);
  
    categoryFilters.forEach((filter) => {
      filter.addEventListener('click', (e) => {
        e.preventDefault();
        selectedCategory = e.target.getAttribute('data-category');
        applyFilters();
      });
    });
  
    // Delegação de eventos nos cards (melhor para listas dinâmicas)
    productList.addEventListener('click', (event) => {
      const target = event.target;
      const card = target.closest('.card');
      if (!card) return;
  
      // seleção visual do card
      document.querySelectorAll('.card').forEach((c) => c.classList.remove('selected'));
      card.classList.add('selected');
  
      const productId = Number(card.dataset.productId);
  
      if (target.closest('.btn-details')) {
        event.stopPropagation();
        toggleDetails(productId);
        return;
      }
  
      if (target.closest('.btn-add')) {
        event.stopPropagation();
        addToCart(productId);
        return;
      }
    });
  
    // remover item direto no popover
    document.body.addEventListener('click', (event) => {
      const btn = event.target.closest('.remove-item');
      if (!btn) return;
  
      event.preventDefault();
      const id = Number(btn.dataset.id);
      removeFromCart(id);
  
      // garante que o popover recalcule o conteúdo já aberto
      popover.update();
    });
  
    clearCartBtn.addEventListener('click', () => {
      confirmModal.show();
    });
  
    confirmClearBtn.addEventListener('click', clearCart);
  
    // formulário
    if (contactForm) {
      contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const nameInput = document.getElementById('nome');
        const emailInput = document.getElementById('email');
        const messageTextarea = document.getElementById('mensagem');
        const messageDiv = document.getElementById('form-message');
  
        messageDiv.innerHTML = '';
        messageDiv.classList.remove('alert', 'alert-danger', 'alert-success');
  
        if (
          nameInput.value.trim() === '' ||
          emailInput.value.trim() === '' ||
          messageTextarea.value.trim() === ''
        ) {
          messageDiv.textContent = 'Preencha todos os campos!';
          messageDiv.classList.add('alert', 'alert-danger');
        } else {
          messageDiv.textContent = 'Enviado com sucesso!';
          messageDiv.classList.add('alert', 'alert-success');
          contactForm.reset();
        }
  
        setTimeout(() => {
          messageDiv.innerHTML = '';
          messageDiv.classList.remove('alert', 'alert-danger', 'alert-success');
        }, 3000);
      });
    }
  
    // =====================================================
    // 9) Promoção do dia (exemplo: switch)
    // =====================================================
  
    function promotionOfDay() {
      const date = new Date();
      const dayOfWeek = date.getDay();
  
      switch (dayOfWeek) {
        case 1:
          return { title: 'Segunda Tech', desc: '10% OFF' };
        case 4:
          return { title: 'Quinta do Look', desc: '30% OFF na segunda peça' };
        default:
          return { title: 'Promoção do Dia', desc: 'Nenhuma promoção disponível' };
      }
    }
  
    const promo = promotionOfDay();
    document.getElementById('promo-title').textContent = promo.title;
    document.getElementById('promo-desc').textContent = promo.desc;
  
    // =====================================================
    // 10) Gerador de produtos fake (laboratório)
    // =====================================================
  
    function generateFakeProducts(n) {
      const produtos = [];
  
      for (let i = 1; i <= n; i++) {
        produtos.push({
          id: 1000 + i,
          nome: `Produto ${i}`,
          preco: Math.round(Math.random() * 5000 * 100) / 100,
          imagem: `https://placehold.co/400x400?text=${i}`,
          categoria: i % 2 === 0 ? 'Eletrônicos' : 'Roupas',
          descricao: 'Produto gerado para treino de arrays/DOM/eventos.'
        });
      }
  
      return produtos;
    }
  
    document.getElementById('generate-fake-btn').addEventListener('click', () => {
      const n = Number(document.getElementById('fake-count').value);
      allProducts = generateFakeProducts(n);
      applyFilters();
    });
  
    document.getElementById('load-from-json-btn').addEventListener('click', () => {
      loadFromJson();
    });
  
    // =====================================================
    // 11) Exibição inicial
    // =====================================================
  
    updateCartDisplay();
  });