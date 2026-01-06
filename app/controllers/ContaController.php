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
    public static function delete(PDO $pdo)
{
    $idConta = (int)($_POST['id'] ?? 0);
    $idUsuario = $_SESSION['usuario']['id'];

    if (!$idConta) {
        $_SESSION['erro'] = 'Conta inválida.';
        header("Location: /financas/public/?url=contas");
        exit;
    }

    if (!Conta::canDelete($pdo, $idUsuario, $idConta)) {
        $_SESSION['erro'] = 'Esta conta possui lançamentos e não pode ser excluída.';
        header("Location: /financas/public/?url=contas");
        exit;
    }

    $stmt = $pdo->prepare("
        DELETE FROM contas 
        WHERE id = ? AND id_usuario = ?
    ");
    $stmt->execute([$idConta, $idUsuario]);

    header("Location: /financas/public/?url=contas");
    exit;
}

}
