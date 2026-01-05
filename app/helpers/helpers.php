<?php

function usuarioId()
{
    return $_SESSION['usuario']['id'];
}

function normalizaValor($valor)
{
    $valor = trim($valor);
    $valor = str_replace(['R$', ' '], '', $valor);
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);
    return (float)$valor;
}
