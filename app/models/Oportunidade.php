<?php

class Oportunidade
{
    public static function etapas(): array
    {
        return [
            'lead'         => 'Lead',
            'negociacao'   => 'Em negociação',
            'proposta'     => 'Proposta enviada',
            'aprovado'     => 'Aprovado',
            'perdido'      => 'Perdido',
        ];
    }

    public static function allByUsuario(PDO $pdo, int $idUsuario): array
    {
        $stmt = $pdo->prepare("
            SELECT o.*,
                   c.nome AS cliente_nome
              FROM oportunidades o
         LEFT JOIN clientes c ON c.id = o.id_cliente
             WHERE o.id_usuario = ?
          ORDER BY FIELD(o.etapa,'lead','negociacao','proposta','aprovado','perdido'),
                   o.ordem ASC, o.id DESC
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function create(PDO $pdo, int $idUsuario, array $data): int
    {
        // ordem no final da coluna
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordem),0)+1 FROM oportunidades WHERE id_usuario=? AND etapa=?");
        $stmt->execute([$idUsuario, $data['etapa']]);
        $ordem = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("
            INSERT INTO oportunidades (id_usuario, id_cliente, titulo, descricao, valor, etapa, ordem, data_prevista)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $idUsuario,
            ($data['id_cliente'] ?: null),
            $data['titulo'],
            ($data['descricao'] ?: null),
            ($data['valor'] !== '' ? $data['valor'] : null),
            $data['etapa'],
            $ordem,
            ($data['data_prevista'] ?: null),
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $idUsuario, int $id, array $data): void
    {
        $stmt = $pdo->prepare("
            UPDATE oportunidades
               SET id_cliente = ?,
                   titulo = ?,
                   descricao = ?,
                   valor = ?,
                   data_prevista = ?
             WHERE id = ?
               AND id_usuario = ?
        ");
        $stmt->execute([
            ($data['id_cliente'] ?: null),
            $data['titulo'],
            ($data['descricao'] ?: null),
            ($data['valor'] !== '' ? $data['valor'] : null),
            ($data['data_prevista'] ?: null),
            $id,
            $idUsuario
        ]);
    }

    public static function delete(PDO $pdo, int $idUsuario, int $id): void
    {
        $stmt = $pdo->prepare("DELETE FROM oportunidades WHERE id=? AND id_usuario=?");
        $stmt->execute([$id, $idUsuario]);
    }

    public static function reorder(PDO $pdo, int $idUsuario, string $etapa, array $idsOrdenados): void
    {
        // atualiza ordem conforme a lista recebida
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE oportunidades SET etapa=?, ordem=? WHERE id=? AND id_usuario=?");
            $i = 1;
            foreach ($idsOrdenados as $id) {
                $id = (int)$id;
                if ($id <= 0) continue;
                $stmt->execute([$etapa, $i++, $id, $idUsuario]);
            }
            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
    public static function findById(PDO $pdo, int $idUsuario, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM oportunidades WHERE id = ? AND id_usuario = ? LIMIT 1");
    $stmt->execute([$id, $idUsuario]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    return $r ?: null;
}

public static function setEtapa(PDO $pdo, int $idUsuario, int $id, string $etapa): void
{
    $stmt = $pdo->prepare("UPDATE oportunidades SET etapa = ? WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$etapa, $id, $idUsuario]);
}

}
