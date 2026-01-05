<?php
require_once __DIR__ . '/../models/Categoria.php';

class CategoriaController
{
    public static function index($pdo)
    {
        $categorias = Categoria::allByUsuario($pdo, usuarioId());
        require '../app/views/categorias/index.php';
    }

    public static function store($pdo)
    {
        $nome = trim($_POST['nome'] ?? '');
        $tipo = $_POST['tipo'] ?? '';

        if (!$nome || !in_array($tipo, ['R', 'D'])) {
            $_SESSION['erro'] = "Informe nome e tipo da categoria.";
            header("Location: /financas/public/?url=categorias");
            exit;
        }

        Categoria::create($pdo, usuarioId(), $nome, $tipo);

        header("Location: /financas/public/?url=categorias");
        exit;
    }
}

