<?php
require_once 'processa_pedido.php';

// Inicializa
$meuCarrinho = [
  ['nome' => 'Notebook', 'preco' => 3200, 'qnt' => 1],
  ['nome' => 'Monitor', 'preco' => 800, 'qnt' => 2],
];

// Verifica se tem item para adidionar no carrinho
if (isset($_GET['nome']) && isset($_GET['preco']) && isset($_GET['qnt'])) {
  $nome = $_GET['nome'];
  $preco = $_GET['preco'];
  $qnt = $_GET['qnt'];
  $meuCarrinho[] = ['nome' => $nome, 'preco' => $preco, 'qnt' => $qnt];
}

// Processa total e vê se tem direito a frete grátis
$minimoFreteGratis = 5000;
$totalFatura = calculaTotal($meuCarrinho);
$isGratis = temFreteGratis($totalFatura, $minimoFreteGratis);
$diferencaFreteGratis = $minimoFreteGratis - $totalFatura;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Usuários</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
  <div class="container mt-5">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Produto</th>
          <th class="text-end">Preço Unitário</th>
          <th class="text-end">Quantidade</th>
          <th class="text-end">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($meuCarrinho as $item): ?>
          <tr>
            <td><?= $item['nome'] ?></td>
            <td class="text-end"><?= $item['preco'] ?></td>
            <td class="text-end"><?= $item['qnt'] ?></td>
            <td class="text-end">R$ <?= subtotalItem($item['preco'], $item['qnt']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <h3 style="text-align: right;">Total: <?= $totalFatura ?></h3>

    <?php if ($isGratis): ?>
      <div class="alert alert-success">Frete Grátis Liberado!</div>
    <?php else: ?>
      <div class="alert alert-warning">Adicione mais itens para frete grátis!<br>
        Inclua mais R$ <?= number_format($diferencaFreteGratis, 2)?> em produtos.</div>
    <?php endif; ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
</body>

</html>