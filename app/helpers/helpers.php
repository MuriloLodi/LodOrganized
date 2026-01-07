<?php

function usuarioId()
{
    return $_SESSION['usuario']['id'];
}

function normalizaValor($valor) {
    $v = trim((string)$valor);

    // remove R$, espaços etc (mantém dígitos, ponto, vírgula e sinal)
    $v = preg_replace('/[^\d,.\-]/', '', $v);

    if ($v === '' || $v === '-' || $v === '.' || $v === ',') return 0;

    // Se tem ponto e vírgula, assume BR: 1.234,56
    if (strpos($v, ',') !== false && strpos($v, '.') !== false) {
        $v = str_replace('.', '', $v);   // tira milhar
        $v = str_replace(',', '.', $v);  // vírgula vira decimal
        return (float)$v;
    }

    // Se só tem vírgula, é decimal BR: 12,34
    if (strpos($v, ',') !== false) {
        $v = str_replace(',', '.', $v);
        return (float)$v;
    }

    // Se só tem ponto, assume decimal EN: 12.34
    return (float)$v;
}
