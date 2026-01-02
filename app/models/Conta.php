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

    public static function create($pdo, $idUsuario, $nome, $saldo)
    {
        $stmt = $pdo->prepare(
            "INSERT INTO contas (id_usuario, nome, saldo_inicial)
             VALUES (?, ?, ?)"
        );
        return $stmt->execute([$idUsuario, $nome, $saldo]);
    }
}
