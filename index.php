<?php
require_once __DIR__ . '/includes/layout.php';

render_head('Minha Loja Virtual');
render_header('home')
?>

    <div class="container mt-4">
        <section id="promo-box" class="mb-4 p-3 border rounded bg-light">
            <div>
                <h4 id="promo-title">Promoção do Dia</h4>
                <p id="promo-desc">Carregando...</p>
            </div>
        </section>

        <section id="lab-box" class="mb-4 p-3 border rounded bg-light">
            <label>Quantidade:</label>
            <input type="number" id="fake-count" value="12">
            <button id="generate-fake-btn" class="btn btn-primary">
                Gerar Cards
            </button>
            <button id="load-from-api-btn" class="btn btn-success">
                Recarregar
            </button>
        </section>

        <div class="row">
            <main class="col-md-12">
                <form id="filter-form" class="mb-4 p-3 border rounded bg-light" onsubmit="return false;">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="price-filter" class="form-label">Preço Máximo</label>
                            <input type="number" class="form-control" id="price-filter" placeholder="Digite o preço">
                        </div>
                        <div class="col-md-3">
                            <label for="sort-order" class="form-label">Ordenar por</label>
                            <select id="sort-order" class="form-select">
                                <option value="default">Padrão</option>
                                <option value="asc">Preço: Menor para Maior</option>
                                <option value="desc">Preço: Maior para Menor</option>
                            </select>
                        </div>
                        <div class="col-md-auto d-flex align-items-end">
                            <button type="button" id="filter-btn" class="btn btn-primary">Aplicar Filtros</button>
                        </div>
                    </div>
                </form>

                <section id="product-list"
                    class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 g-4">
                    <!-- Os produtos serão inseridos aqui via JavaScript -->
                </section>
            </main>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirm-clear-cart-modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Limpar Carrinho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Tem certeza de que deseja limpar o carrinho?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirm-clear-btn">Limpar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Checkout -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cart-check"></i> Finalizar Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                <label class="form-label fw-bold">Selecione o Cliente:</label>
                <select id="select-cliente" class="form-select">
                    <option value="">Carregando clientes...</option>
                </select>
                </div>
                <div class="alert alert-info py-2">
                    <strong>Total da Venda:</strong> <span id="checkout-total">R$ 0,00</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btn-confirmar-pedido" class="btn btn-success">Confirmar e Gravar</button>
            </div>
            </div>
        </div>
    </div>

    <script src="app.min.js"></script> 
<?php
render_footer();
?>