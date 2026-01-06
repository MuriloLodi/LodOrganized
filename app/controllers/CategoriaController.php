<?php
require_once __DIR__ . '/../models/Categoria.php';

class CategoriaController
{
    public static function index($pdo)
    {
        $categorias = Categoria::allByUsuario(
            $pdo,
            $_SESSION['usuario']['id']
        );

        require '../app/views/categorias/index.php';
    }

    public static function store($pdo)
    {
        Categoria::create(
            $pdo,
            $_SESSION['usuario']['id'],
            $_POST['nome'],
            $_POST['tipo'],
            $_POST['icone'] ?? 'bi-tag'
        );

        header("Location: /financas/public/?url=categorias");
        exit;
    }

    public static function update($pdo)
    {
        Categoria::update(
            $pdo,
            $_SESSION['usuario']['id'],
            $_POST['id'],
            $_POST['nome'],
            $_POST['icone']
        );

        header("Location: /financas/public/?url=categorias");
        exit;
    }

    public static function delete($pdo)
    {
        try {
            Categoria::delete(
                $pdo,
                $_SESSION['usuario']['id'],
                $_POST['id']
            );
        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
        }

        header("Location: /financas/public/?url=categorias");
        exit;
    }
}


