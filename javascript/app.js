document.addEventListener('DOMContentLoaded', () => {
    // Carrega o carrinho do localStorage ou inicializa com valores padrão
    let cartCount = parseInt(localStorage.getItem('cartCount')) || 0;
    let cartTotal = parseFloat(localStorage.getItem('cartTotal')) || 0;

    const cartIcon = document.getElementById('cart-icon');
    const cartCountElement = document.getElementById('cart-count');
    const priceFilter = document.getElementById('price-filter');
    const filterBtn = document.getElementById('filter-btn');
    const productList = document.getElementById('product-list');

    const popover = new bootstrap.Popover(cartIcon, {
        html: true,
        trigger: 'hover focus',
        placement: 'bottom',
        title: 'Resumo do Carrinho',
        content: 'Seu carrinho está vazio.'
    });

    // Função para salvar o estado do carrinho no localStorage
    function saveCart() {
        localStorage.setItem('cartCount', cartCount);
        localStorage.setItem('cartTotal', cartTotal);
    }

    // Função para atualizar a exibição do carrinho na interface
    function updateCartDisplay() {
        cartCountElement.textContent = cartCount;
        const formattedTotal = cartTotal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        const popoverContent = cartCount > 0 ? `Total: <strong>${formattedTotal}</strong>` : 'Seu carrinho está vazio.';
        
        popover.setContent({
            '.popover-body': popoverContent
        });
    }

    function renderCards(products) {
        productList.innerHTML = '';
        products.forEach(product => {
            const card = `
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="${product.imagem}" class="card-img-top" alt="${product.nome}">
                        <div class="card-body">
                            <h5 class="card-title">${product.nome}</h5>
                            <p class="card-text">${product.preco}</p>
                            <button class="btn btn-primary">Detalhes</button>
                            <button class="btn btn-success">Comprar</button>
                        </div>
                    </div>
                </div>
            `;
            productList.innerHTML += card;
        });
        attachEventListeners();
    }

    function attachEventListeners() {
        document.querySelectorAll('#product-list .btn-success').forEach(btn => {
            btn.addEventListener('click', (event) => {
                event.stopPropagation();
                const card = btn.closest('.card');
                const priceString = card.querySelector('.card-text').textContent;
                const price = parseFloat(priceString.replace(/[^0-9,-]+/g, "").replace(',', '.'));

                if (!isNaN(price)) {
                    cartCount++;
                    cartTotal += price;
                    saveCart(); // Salva o carrinho no localStorage
                    updateCartDisplay(); // Atualiza a exibição
                }
            });
        });

        const productCards = document.querySelectorAll('#product-list .card');
        productCards.forEach(card => {
            card.addEventListener('click', () => {
                productCards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
            });
        });
    }

    fetch('produtos.json')
        .then(response => response.json())
        .then(data => {
            renderCards(data);
        });

    filterBtn.addEventListener('click', () => {
        const maxPrice = parseFloat(priceFilter.value);
        fetch('produtos.json')
            .then(response => response.json())
            .then(data => {
                const filteredProducts = data.filter(product => {
                    const price = parseFloat(product.preco.replace(/[^0-9,-]+/g, "").replace(',', '.'));
                    return isNaN(maxPrice) || price <= maxPrice;
                });
                renderCards(filteredProducts);
            });
    });

    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const nameInput = document.getElementById('nome');
            const emailInput = document.getElementById('email');
            const messageDiv = document.getElementById('form-message');

            messageDiv.innerHTML = '';
            messageDiv.classList.remove('alert', 'alert-danger', 'alert-success');

            if (nameInput.value.trim() === '' || emailInput.value.trim() === '') {
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

    // Atualiza a exibição do carrinho ao carregar a página
    updateCartDisplay();
});
