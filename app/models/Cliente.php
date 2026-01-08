<?php

class Cliente
{
    public static function allByUsuario(PDO $pdo, int $idUsuario, string $q = ''): array
    {
        $q = trim($q);

        if ($q !== '') {
            $stmt = $pdo->prepare("
                SELECT *
                  FROM clientes
                 WHERE id_usuario = ?
                   AND (
                        nome LIKE ?
                        OR email LIKE ?
                        OR telefone LIKE ?
                        OR documento LIKE ?
                   )
                 ORDER BY nome ASC
            ");
            $like = '%' . $q . '%';
            $stmt->execute([$idUsuario, $like, $like, $like, $like]);
        } else {
            $stmt = $pdo->prepare("
                SELECT *
                  FROM clientes
                 WHERE id_usuario = ?
                 ORDER BY nome ASC
            ");
            $stmt->execute([$idUsuario]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById(PDO $pdo, int $id, int $idUsuario): ?array
    {
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? AND id_usuario = ? LIMIT 1");
        $stmt->execute([$id, $idUsuario]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function create(PDO $pdo, int $idUsuario, array $data): int
    {
        $stmt = $pdo->prepare("
            INSERT INTO clientes (id_usuario, nome, email, telefone, documento, endereco, observacoes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $idUsuario,
            $data['nome'],
            $data['email'] ?: null,
            $data['telefone'] ?: null,
            $data['documento'] ?: null,
            $data['endereco'] ?: null,
            $data['observacoes'] ?: null
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, int $idUsuario, array $data): void
    {
        $stmt = $pdo->prepare("
            UPDATE clientes
               SET nome = ?,
                   email = ?,
                   telefone = ?,
                   documento = ?,
                   endereco = ?,
                   observacoes = ?
             WHERE id = ?
               AND id_usuario = ?
        ");
        $stmt->execute([
            $data['nome'],
            $data['email'] ?: null,
            $data['telefone'] ?: null,
            $data['documento'] ?: null,
            $data['endereco'] ?: null,
            $data['observacoes'] ?: null,
            $id,
            $idUsuario
        ]);
    }

    public static function delete(PDO $pdo, int $id, int $idUsuario): void
    {
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id, $idUsuario]);
    }
}
