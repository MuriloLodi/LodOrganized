<?php

class Conta
{
    public static function allByUsuario($pdo, $idUsuario)
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM contas WHERE id_usuario = ? ORDER BY nome"
        );
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public static function create($pdo, $idUsuario, $nome, $saldoInicial)
    {
        $stmt = $pdo->prepare(
            "INSERT INTO contas (id_usuario, nome, saldo_inicial, saldo_atual)
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([
            $idUsuario,
            $nome,
            $saldoInicial,
            $saldoInicial
        ]);
    }

    public static function atualizaSaldo($pdo, $idConta, $valor, $tipo)
    {
        if ($tipo === 'R') {
            $sql = "UPDATE contas SET saldo_atual = saldo_atual + ? WHERE id = ?";
        } else {
            $sql = "UPDATE contas SET saldo_atual = saldo_atual - ? WHERE id = ?";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$valor, $idConta]);
    }
    public static function reverterSaldo($pdo, $idConta, $valor, $tipo)
{
    if ($tipo === 'R') {
        $sql = "UPDATE contas SET saldo_atual = saldo_atual - ? WHERE id = ?";
    } else {
        $sql = "UPDATE contas SET saldo_atual = saldo_atual + ? WHERE id = ?";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$valor, $idConta]);
}

}
