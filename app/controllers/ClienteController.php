<?php
require_once __DIR__ . '/../models/Cliente.php';

class ClienteController
{
    public static function index($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) {
            header("Location: /financas/public/?url=login");
            exit;
        }

        $q = trim($_GET['q'] ?? '');
        $clientes = Cliente::allByUsuario($pdo, $idUsuario, $q);

        $titulo = "Clientes • Finanças";
        $view = __DIR__ . '/../views/clientes/index_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function store($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) {
            header("Location: /financas/public/?url=login");
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') {
            $_SESSION['erro'] = "Informe o nome do cliente.";
            header("Location: /financas/public/?url=clientes");
            exit;
        }

        $data = [
            'nome' => $nome,
            'email' => trim($_POST['email'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'documento' => trim($_POST['documento'] ?? ''),
            'endereco' => trim($_POST['endereco'] ?? ''),
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];

        try {
            Cliente::create($pdo, $idUsuario, $data);
            $_SESSION['sucesso'] = "Cliente criado com sucesso.";
        } catch (Throwable $e) {
    $_SESSION['erro'] = "Erro ao criar cliente: " . $e->getMessage();
}


        header("Location: /financas/public/?url=clientes");
        exit;
    }

    public static function update($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) {
            header("Location: /financas/public/?url=login");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $_SESSION['erro'] = "Cliente inválido.";
            header("Location: /financas/public/?url=clientes");
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') {
            $_SESSION['erro'] = "Informe o nome do cliente.";
            header("Location: /financas/public/?url=clientes");
            exit;
        }

        $data = [
            'nome' => $nome,
            'email' => trim($_POST['email'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'documento' => trim($_POST['documento'] ?? ''),
            'endereco' => trim($_POST['endereco'] ?? ''),
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];

        try {
            Cliente::update($pdo, $id, $idUsuario, $data);
            $_SESSION['sucesso'] = "Cliente atualizado com sucesso.";
        } catch (Exception $e) {
            $_SESSION['erro'] = "Erro ao atualizar cliente.";
        }

        header("Location: /financas/public/?url=clientes");
        exit;
    }

    public static function delete($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) {
            header("Location: /financas/public/?url=login");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $_SESSION['erro'] = "Cliente inválido.";
            header("Location: /financas/public/?url=clientes");
            exit;
        }

        try {
            Cliente::delete($pdo, $id, $idUsuario);
            $_SESSION['sucesso'] = "Cliente removido.";
        } catch (Exception $e) {
            $_SESSION['erro'] = "Erro ao remover cliente.";
        }

        header("Location: /financas/public/?url=clientes");
        exit;
    }
}
