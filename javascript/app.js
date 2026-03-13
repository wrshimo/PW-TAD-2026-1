document.addEventListener('DOMContentLoaded', () => {
    let cartCount = 0;
    let cartTotal = 0;

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

    function renderCards(products) {
        productList.innerHTML = ''; // Limpa a lista de produtos existente
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

        // Re-anexa os event listeners aos botões "Comprar" e aos cards
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
                    cartCountElement.textContent = cartCount;

                    const formattedTotal = cartTotal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                    popover.setContent({
                        '.popover-body': `Total: <strong>${formattedTotal}</strong>`
                    });
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
});