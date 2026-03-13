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

    document.querySelectorAll('.btn.btn-success').forEach(btn => {
        btn.addEventListener('click', () => {
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

    filterBtn.addEventListener('click', () => {
        const maxPrice = parseFloat(priceFilter.value);

        for (const product of productList.children) {
            const priceString = product.querySelector('.card-text').textContent;
            const price = parseFloat(priceString.replace(/[^0-9,-]+/g, "").replace(',', '.'));

            if (!isNaN(maxPrice) && price > maxPrice) {
                product.style.display = 'none';
            } else {
                product.style.display = 'block';
            }
        }
    });
});