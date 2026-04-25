<?php
include_once "logica_desconto.php";

// Dados com valores padrão
$precoOriginal = 100;
$percDesconto = 20;

// Obtendo os valores da barra de endereços
if (isset($_GET['preco'])) {
    $precoOriginal = $_GET['preco'];
}
if (isset($_GET['desc'])) {
    $percDesconto = $_GET['desc'];
}

// Calcula o preço final
$precoFinal = calculaDesconto($precoOriginal, $percDesconto);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <div class="card p-3 w-50 mx-auto shadow-lg">
            <h3 class="card-title text-center text-primary-emphasis">Simulador de Desconto</h3>
            <p class="card-title text-center">Preço original: R$ <?= $precoOriginal ?></p>
            <p class="card-title text-center">Percentual de desconto: <?= $percDesconto ?>%</p>
            <p class="card-title text-center">Preço final: <span class="text-primary fw-bold">R$ <?= number_format($precoFinal, 2) ?></span></p>
        </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
