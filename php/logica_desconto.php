<?php
function calculaDesconto($precoBase, $porcentagem) {
    $desconto = $precoBase * ($porcentagem / 100);
    return $precoBase - $desconto;
}