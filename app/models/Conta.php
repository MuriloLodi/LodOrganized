<?php

class Conta
{
    public static function allByUsuario($pdo, $idUsuario)
    {
        $stmt = $pdo->prepare("SELECT * FROM contas WHERE id_usuario = ? ORDER BY nome");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public static function create($pdo, $idUsuario, $nome, $saldoInicial)
    {
        $stmt = $pdo->prepare("
            INSERT INTO contas (id_usuario, nome, saldo_atual)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$idUsuario, $nome, $saldoInicial]);
    }

    public static function find($pdo, $id, $idUsuario)
    {
        $stmt = $pdo->prepare("SELECT * FROM contas WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    public static function update($pdo, $id, $idUsuario, $nome)
    {
        $stmt = $pdo->prepare("UPDATE contas SET nome = ? WHERE id = ? AND id_usuario = ?");
        return $stmt->execute([$nome, $id, $idUsuario]);
    }

    public static function movimentar($pdo, $idConta, $idUsuario, $valor, $tipo)
    {
        if ($tipo === 'R') {
            $sql = "UPDATE contas SET saldo_atual = saldo_atual + ? WHERE id = ? AND id_usuario = ?";
        } else {
            $sql = "UPDATE contas SET saldo_atual = saldo_atual - ? WHERE id = ? AND id_usuario = ?";
        }

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$valor, $idConta, $idUsuario]);
    }

    public static function reverterMovimento($pdo, $idConta, $idUsuario, $valor, $tipo)
    {
        // desfaz a movimentação anterior
        $tipoInvertido = ($tipo === 'R') ? 'D' : 'R';
        return self::movimentar($pdo, $idConta, $idUsuario, $valor, $tipoInvertido);
    }

    public static function canDelete($pdo, $idUsuario, $idConta): bool
    {
        // bloqueia se existir qualquer lançamento vinculado
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM lancamentos WHERE id_usuario = ? AND id_conta = ?");
        $stmt->execute([$idUsuario, $idConta]);
        $row = $stmt->fetch();
        return ((int)$row['total'] === 0);
    }

    public static function delete($pdo, $idUsuario, $idConta)
    {
        $stmt = $pdo->prepare("DELETE FROM contas WHERE id_usuario = ? AND id = ?");
        return $stmt->execute([$idUsuario, $idConta]);
    }
}
