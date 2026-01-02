<?php

class Categoria
{
    public static function allByUsuario($pdo, $idUsuario)
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM categorias WHERE id_usuario = ? ORDER BY nome"
        );
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public static function create($pdo, $idUsuario, $nome, $tipo)
    {
        $stmt = $pdo->prepare(
            "INSERT INTO categorias (id_usuario, nome, tipo)
             VALUES (?, ?, ?)"
        );
        return $stmt->execute([$idUsuario, $nome, $tipo]);
    }
}
