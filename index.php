<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>Loja do Shimo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css?v=1.0">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
            <div class="container">
                <a class="navbar-brand" href="#">Loja do Shimo</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"
                    aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="nav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" href="/">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="/produtos">Produtos</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Categorias
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Eletrônicos</a></li>
                                <li><a class="dropdown-item" href="#">Roupas</a></li>
                                <li><a class="dropdown-item" href="#">Livros</a></li>
                                <li><a class="dropdown-item" href="#">Casa e Jardim</a></li>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="/contato">Contato</a></li>
                    </ul>
                    <form class="d-flex me-3" role="search">
                        <input class="form-control me-2" type="search" placeholder="Buscar" />
                        <button class="btn btn-outline-light" type="submit">Buscar</button>
                    </form>
                    <a href="#" class="nav-link text-white">
                        <i class="bi bi-cart position-relative fs-4">
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                0
                                <span class="visually-hidden">itens no carrinho</span>
                            </span>
                        </i>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <div class="container mt-4">
        <main>
            <section class="row row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xxl-6 g-4">
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="https://placehold.co/150x150" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Produto 1</h5>
                            <p class="card-text">R$ 150,00</p>
                            <a href="#" class="btn btn-primary">Detalhes</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="https://placehold.co/150x150" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Produto 2</h5>
                            <p class="card-text">R$ 200,00</p>
                            <a href="#" class="btn btn-primary">Detalhes</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="https://placehold.co/150x150" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Produto 3</h5>
                            <p class="card-text">R$ 75,00</p>
                            <a href="#" class="btn btn-primary">Detalhes</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="https://placehold.co/150x150" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Produto 4</h5>
                            <p class="card-text">R$ 300,00</p>
                            <a href="#" class="btn btn-primary">Detalhes</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="https://placehold.co/150x150" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Produto 5</h5>
                            <p class="card-text">R$ 50,00</p>
                            <a href="#" class="btn btn-primary">Detalhes</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="https://placehold.co/150x150" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Produto 6</h5>
                            <p class="card-text">R$ 120,00</p>
                            <a href="#" class="btn btn-primary">Detalhes</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="https://placehold.co/150x150" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Produto 7</h5>
                            <p class="card-text">R$ 180,00</p>
                            <a href="#" class="btn btn-primary">Detalhes</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="https://placehold.co/150x150" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Produto 8</h5>
                            <p class="card-text">R$ 90,00</p>
                            <a href="#" class="btn btn-primary">Detalhes</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="https://placehold.co/150x150" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Produto 9</h5>
                            <p class="card-text">R$ 250,00</p>
                            <a href="#" class="btn btn-primary">Detalhes</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="https://placehold.co/150x150" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Produto 10</h5>
                            <p class="card-text">R$ 100,00</p>
                            <a href="#" class="btn btn-primary">Detalhes</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <footer class="bg-dark text-white text-center p-3 mt-4">
        <p>&copy; 2024 Minha Loja Virtual. Todos os direitos reservados.</p>
        <ul class="nav justify-content-center">
            <li class="nav-item"><a href="#" class="nav-link px-2 text-white">Sobre Nós</a></li>
            <li class="nav-item"><a href="#" class="nav-link px-2 text-white">Política de Privacidade</a></li>
            <li class="nav-item"><a href="#" class="nav-link px-2 text-white">Termos de Uso</a></li>
        </ul>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>
</body>

</html>