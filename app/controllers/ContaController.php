<?php
require_once __DIR__ . '/../models/Conta.php';

class ContaController
{
    public static function index($pdo)
    {
        $contas = Conta::allByUsuario($pdo, usuarioId());
        require '../app/views/contas/index.php';
    }

    public static function store($pdo)
    {
        $nome  = trim($_POST['nome'] ?? '');
        $saldo = normalizaValor($_POST['saldo'] ?? '0');

        if (!$nome) {
            $_SESSION['erro'] = "Informe o nome da conta.";
            header("Location: /financas/public/?url=contas");
            exit;
        }

        Conta::create($pdo, usuarioId(), $nome, $saldo);

        header("Location: /financas/public/?url=contas");
        exit;
    }
}
