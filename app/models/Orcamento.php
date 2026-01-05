<?php

class Orcamento
{
    public static function allByMes($pdo, $idUsuario, $ano, $mes)
    {
        $stmt = $pdo->prepare("
            SELECT o.*, c.nome, c.tipo
            FROM orcamentos o
            JOIN categorias c ON c.id = o.id_categoria
            WHERE o.id_usuario = ?
              AND o.ano = ?
              AND o.mes = ?
            ORDER BY c.nome
        ");
        $stmt->execute([$idUsuario, $ano, $mes]);
        return $stmt->fetchAll();
    }

    public static function save($pdo, $idUsuario, $idCategoria, $ano, $mes, $valor)
    {
        $stmt = $pdo->prepare("
            INSERT INTO orcamentos (id_usuario, id_categoria, ano, mes, valor)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)
        ");
        return $stmt->execute([
            $idUsuario, $idCategoria, $ano, $mes, $valor
        ]);
    }
}
