document.addEventListener('DOMContentLoaded', () => {
    // --- GERENCIAMENTO DE ESTADO ---
    let allProducts = [];
    let selectedCategory = 'all'; // Categoria selecionada, 'all' é o padrão
    let cartCount = parseInt(localStorage.getItem('cartCount')) || 0;
    let cartTotal = parseFloat(localStorage.getItem('cartTotal')) || 0;

    // --- SELEÇÃO DE ELEMENTOS DO DOM ---
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

    // --- INICIALIZAÇÃO DE COMPONENTES BOOTSTRAP ---
    const popover = new bootstrap.Popover(cartIcon, {
        html: true,
        trigger: 'hover focus',
        placement: 'bottom',
        title: 'Resumo do Carrinho',
        content: 'Seu carrinho está vazio.' // Conteúdo inicial
    });
    const confirmModal = new bootstrap.Modal(document.getElementById('confirm-clear-cart-modal'));
    const tooltip = new bootstrap.Tooltip(clearCartBtn);


    // --- FUNÇÕES DO CARRINHO ---
    // Salva o estado atual do carrinho (quantidade e total) no Local Storage.
    function saveCart() {
        localStorage.setItem('cartCount', cartCount);
        localStorage.setItem('cartTotal', cartTotal);
    }

    // Atualiza a exibição do carrinho na interface do usuário.
    function updateCartDisplay() {
        cartCountElement.textContent = cartCount;
        const formattedTotal = cartTotal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        const popoverContent = cartCount > 0 ? `Total: <strong>${formattedTotal}</strong>` : 'Seu carrinho está vazio.';
        popover.setContent({ '.popover-body': popoverContent });

        // Desabilita o botão de limpar se o carrinho estiver vazio.
        clearCartBtn.disabled = cartCount === 0;
    }

    // Limpa o carrinho, redefinindo a contagem e o total.
    function clearCart() {
        cartCount = 0;
        cartTotal = 0;
        saveCart();
        updateCartDisplay();
        confirmModal.hide(); // Esconde o modal de confirmação.
    }

    // --- RENDERIZAÇÃO DE PRODUTOS E EVENTOS ---
    // Renderiza os cartões de produtos na página.
    function renderCards(products) {
        productList.innerHTML = ''; // Limpa a lista de produtos existente.
        products.forEach(product => {
            const card = `
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="${product.imagem}" class="card-img-top" alt="${product.nome}">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${product.nome}</h5>
                            <p class="card-text">${product.preco}</p>
                            <div class="mt-auto">
                                <button class="btn btn-primary">Detalhes</button>
                                <button class="btn btn-success">Comprar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            productList.innerHTML += card; // Adiciona o novo cartão de produto à lista.
        });
        attachProductEventListeners(); // Adiciona os ouvintes de eventos aos novos botões.
    }

    // Adiciona ouvintes de eventos de clique aos botões "Comprar".
    function attachProductEventListeners() {
        document.querySelectorAll('#product-list .btn-success').forEach(btn => {
            btn.addEventListener('click', (event) => {
                event.stopPropagation();
                const card = btn.closest('.card');
                const priceString = card.querySelector('.card-text').textContent;
                const price = parseFloat(priceString.replace(/[^0-9,-]+/g, "").replace(',', '.'));

                if (!isNaN(price)) {
                    cartCount++;
                    cartTotal += price;
                    saveCart();
                    updateCartDisplay();
                }
            });
        });
    }

    // --- LÓGICA DE FILTRAGEM E ORDENAÇÃO ---
    // Aplica os filtros e a ordenação selecionados à lista de produtos.
    function applyFilters() {
        let filteredProducts = [...allProducts];

        // Filtro por categoria
        if (selectedCategory !== 'all') {
            filteredProducts = filteredProducts.filter(product => product.categoria === selectedCategory);
        }

        // Filtro por nome
        const searchTerm = nameSearch.value.toLowerCase();
        if (searchTerm) {
            filteredProducts = filteredProducts.filter(product => product.nome.toLowerCase().includes(searchTerm));
        }

        // Filtro por preço
        const maxPrice = parseFloat(priceFilter.value);
        if (!isNaN(maxPrice)) {
            filteredProducts = filteredProducts.filter(product => {
                const price = parseFloat(product.preco.replace(/[^0-9,-]+/g, "").replace(',', '.'));
                return price <= maxPrice;
            });
        }

        // Ordenação
        const sortValue = sortOrder.value;
        if (sortValue !== 'default') {
            filteredProducts.sort((a, b) => {
                const priceA = parseFloat(a.preco.replace(/[^0-9,-]+/g, "").replace(',', '.'));
                const priceB = parseFloat(b.preco.replace(/[^0-9,-]+/g, "").replace(',', '.'));
                return sortValue === 'asc' ? priceA - priceB : priceB - priceA;
            });
        }

        renderCards(filteredProducts); // Renderiza os produtos filtrados e ordenados.
    }

    // --- CARGA INICIAL DE DADOS E CONFIGURAÇÃO DE EVENTOS ---
    // Busca os dados dos produtos do arquivo JSON.
    fetch('produtos.json')
        .then(response => response.json())
        .then(data => {
            allProducts = data; // Armazena todos os produtos na variável de estado.
            applyFilters(); // Renderiza os produtos na página pela primeira vez.
        });

    // Adiciona ouvintes de eventos aos elementos de filtro e ordenação.
    nameSearch.addEventListener('input', applyFilters);
    filterBtn.addEventListener('click', applyFilters);
    sortOrder.addEventListener('change', applyFilters);

    // Adiciona ouvintes de eventos aos links de filtro de categoria.
    categoryFilters.forEach(filter => {
        filter.addEventListener('click', (e) => {
            e.preventDefault();
            selectedCategory = e.target.getAttribute('data-category');
            applyFilters();
        });
    });

    // Adiciona ouvinte de evento para mostrar o modal de confirmação ao clicar em "Limpar Carrinho".
    clearCartBtn.addEventListener('click', () => {
        confirmModal.show();
    });

    // Adiciona ouvinte de evento para limpar o carrinho ao confirmar no modal.
    confirmClearBtn.addEventListener('click', clearCart);

    // --- VALIDAÇÃO DO FORMULÁRIO DE CONTATO ---
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const nameInput = document.getElementById('nome');
            const emailInput = document.getElementById('email');
            const messageTextarea = document.getElementById('mensagem');
            const messageDiv = document.getElementById('form-message');

            messageDiv.innerHTML = '';
            messageDiv.classList.remove('alert', 'alert-danger', 'alert-success');

            if (nameInput.value.trim() === '' || emailInput.value.trim() === '' || messageTextarea.value.trim() === '') {
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

    // --- EXIBIÇÃO INICIAL DO CARRINHO ---
    // Atualiza a exibição do carrinho assim que a página é carregada.
    updateCartDisplay();
});