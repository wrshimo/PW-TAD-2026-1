<?php
function subtotalItem($preco, $qnt) {
    return $preco * $qnt;
}

function calculaTotal($carrinho) {
    $total = 0;
    foreach ($carrinho as $item) {
        $total += subtotalItem($item['preco'], $item['qnt']);
    }
    return $total;
}

function temFreteGratis($totalPedido, $minimoFreteGratis = 5000) {
    return $totalPedido >= $minimoFreteGratis;
}
