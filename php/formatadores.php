<?php
function formataNome($nome)
{
    $preposicoes = array('de', 'do', 'da', 'das', 'dos');
    $string = strtolower($nome); // Passa tudo paras minúsculas
    $palavras = explode(' ', $string); // Separa cada palavra do nome
    
    foreach ($palavras as $key => $word) {
         // Passa a primeira letra para maiúscula somente se não
         // estiver na lista de preposições
        if ($key == 0 || !in_array($word, $preposicoes)) {
            $palavras[$key] = ucwords($word);
        }
    }
    
    return implode(' ', $palavras);
}

function formataCPF($cpf)
{
    // Assume $cpf com 11 dígitos numéricos
    $p1 = substr($cpf, 0, 3);
    $p2 = substr($cpf, 3, 3);
    $p3 = substr($cpf, 6, 3);
    $dig = substr($cpf, 9, 2);
    return "$p1.$p2.$p3-$dig";
}
