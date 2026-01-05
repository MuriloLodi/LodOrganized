<?php
require_once __DIR__ . '/../models/Orcamento.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Lancamento.php';

class OrcamentoController
{
    public static function index($pdo)
    {
        $idUsuario = $_SESSION['usuario']['id'];
        $ano = $_GET['ano'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('m');

        $orcamentos = Orcamento::allByMes($pdo, $idUsuario, $ano, $mes);
        $categorias = Categoria::allByUsuario($pdo, $idUsuario);
        // total real gasto por categoria no mês
$gastosReais = Lancamento::totalPorCategoriaMes(
    $pdo,
    $idUsuario,
    $ano,
    $mes
);

        require '../app/views/orcamentos/index.php';
    }

    public static function store($pdo)
    {
        $idUsuario = $_SESSION['usuario']['id'];

        Orcamento::save(
            $pdo,
            $idUsuario,
            $_POST['id_categoria'],
            $_POST['ano'],
            $_POST['mes'],
            str_replace(',', '.', $_POST['valor'])
        );

        header("Location: /financas/public/?url=orcamentos&ano={$_POST['ano']}&mes={$_POST['mes']}");
        exit;
    }
}
