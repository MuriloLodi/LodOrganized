<?php

class Servico
{
    public static function listar($pdo, int $idUsuario, string $q = ''): array
    {
        $q = trim($q);

        if ($q !== '') {
            $like = '%' . $q . '%';
            $stmt = $pdo->prepare("
                SELECT *
                  FROM servicos
                 WHERE id_usuario = ?
                   AND (nome LIKE ? OR descricao LIKE ?)
                 ORDER BY ativo DESC, nome ASC, id DESC
            ");
            $stmt->execute([$idUsuario, $like, $like]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $stmt = $pdo->prepare("
            SELECT *
              FROM servicos
             WHERE id_usuario = ?
             ORDER BY ativo DESC, nome ASC, id DESC
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById($pdo, int $idUsuario, int $id): ?array
    {
        $stmt = $pdo->prepare("SELECT * FROM servicos WHERE id_usuario = ? AND id = ? LIMIT 1");
        $stmt->execute([$idUsuario, $id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function create($pdo, int $idUsuario, array $data): bool
    {
        $stmt = $pdo->prepare("
            INSERT INTO servicos (id_usuario, nome, descricao, preco, duracao_min, ativo)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $idUsuario,
            $data['nome'],
            $data['descricao'] ?: null,
            $data['preco'],
            $data['duracao_min'] !== '' ? (int)$data['duracao_min'] : null,
            (int)$data['ativo'],
        ]);
    }

    public static function update($pdo, int $idUsuario, int $id, array $data): bool
    {
        $stmt = $pdo->prepare("
            UPDATE servicos
               SET nome = ?,
                   descricao = ?,
                   preco = ?,
                   duracao_min = ?,
                   ativo = ?
             WHERE id_usuario = ?
               AND id = ?
        ");

        return $stmt->execute([
            $data['nome'],
            $data['descricao'] ?: null,
            $data['preco'],
            $data['duracao_min'] !== '' ? (int)$data['duracao_min'] : null,
            (int)$data['ativo'],
            $idUsuario,
            $id
        ]);
    }

    public static function delete($pdo, int $idUsuario, int $id): bool
    {
        $stmt = $pdo->prepare("DELETE FROM servicos WHERE id_usuario = ? AND id = ?");
        return $stmt->execute([$idUsuario, $id]);
    }

    public static function toggleAtivo($pdo, int $idUsuario, int $id): bool
    {
        $stmt = $pdo->prepare("
            UPDATE servicos
               SET ativo = IF(ativo = 1, 0, 1)
             WHERE id_usuario = ?
               AND id = ?
        ");
        return $stmt->execute([$idUsuario, $id]);
    }
}
