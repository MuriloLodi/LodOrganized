<?php

class Categoria
{
    public static function allByUsuario($pdo, $idUsuario)
    {
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                COALESCE(SUM(l.valor), 0) AS total
            FROM categorias c
            LEFT JOIN lancamentos l 
                ON l.id_categoria = c.id 
               AND l.id_usuario = c.id_usuario
            WHERE c.id_usuario = ?
            GROUP BY c.id
            ORDER BY c.nome
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public static function create($pdo, $idUsuario, $nome, $tipo, $icone)
    {
        $stmt = $pdo->prepare("
            INSERT INTO categorias (id_usuario, nome, tipo, icone)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$idUsuario, $nome, $tipo, $icone]);
    }

    public static function update($pdo, $idUsuario, $id, $nome, $icone)
    {
        $stmt = $pdo->prepare("
            UPDATE categorias 
            SET nome = ?, icone = ?
            WHERE id = ? AND id_usuario = ?
        ");
        $stmt->execute([$nome, $icone, $id, $idUsuario]);
    }

    public static function canDelete($pdo, $idUsuario, $id)
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM lancamentos 
            WHERE id_categoria = ? AND id_usuario = ?
        ");
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetchColumn() == 0;
    }

    public static function delete($pdo, $idUsuario, $id)
    {
        if (!self::canDelete($pdo, $idUsuario, $id)) {
            throw new Exception("Categoria possui lançamentos.");
        }

        $stmt = $pdo->prepare("
            DELETE FROM categorias 
            WHERE id = ? AND id_usuario = ?
        ");
        $stmt->execute([$id, $idUsuario]);
    }
}
