<?php
require_once __DIR__ . '/../helpers/helpers.php';

class Proposta
{
    public static function allByUsuario($pdo, int $idUsuario): array
    {
        $stmt = $pdo->prepare("SELECT * FROM propostas WHERE id_usuario = ? ORDER BY id DESC");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById($pdo, int $idUsuario, int $id): ?array
    {
        $stmt = $pdo->prepare("SELECT * FROM propostas WHERE id_usuario = ? AND id = ? LIMIT 1");
        $stmt->execute([$idUsuario, $id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        return $p ?: null;
    }

    public static function itens($pdo, int $idProposta): array
    {
        $stmt = $pdo->prepare("SELECT * FROM proposta_itens WHERE id_proposta = ? ORDER BY ordem ASC, id ASC");
        $stmt->execute([$idProposta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function gerarNumero($pdo, int $idUsuario): string
    {
        $ano = date('Y');

        $stmt = $pdo->prepare("
            SELECT numero FROM propostas
            WHERE id_usuario = ? AND numero LIKE ?
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$idUsuario, $ano . '-%']);
        $last = $stmt->fetchColumn();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last);
            $seq = isset($parts[1]) ? ((int)$parts[1] + 1) : 1;
        }

        return $ano . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    public static function create($pdo, array $data, array $itens): int
    {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO propostas
            (id_usuario, numero, status, data_emissao, validade_dias, cliente_nome, cliente_email, cliente_telefone, cliente_endereco, forma_pagamento, observacoes, consideracoes, total)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            (int)$data['id_usuario'],
            $data['numero'],
            $data['status'] ?? 'rascunho',
            $data['data_emissao'],
            (int)($data['validade_dias'] ?? 15),
            $data['cliente_nome'],
            $data['cliente_email'] ?? null,
            $data['cliente_telefone'] ?? null,
            $data['cliente_endereco'] ?? null,
            $data['forma_pagamento'] ?? null,
            $data['observacoes'] ?? null,
            $data['consideracoes'] ?? null,
            0
        ]);

        $idProposta = (int)$pdo->lastInsertId();

        self::salvarItens($pdo, $idProposta, $itens);
        $total = self::recalcularTotal($pdo, $idProposta);

        $upd = $pdo->prepare("UPDATE propostas SET total = ? WHERE id = ?");
        $upd->execute([$total, $idProposta]);

        $pdo->commit();
        return $idProposta;
    }

    public static function update($pdo, int $idUsuario, int $id, array $data, array $itens): void
    {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE propostas SET
              status = ?,
              data_emissao = ?,
              validade_dias = ?,
              cliente_nome = ?,
              cliente_email = ?,
              cliente_telefone = ?,
              cliente_endereco = ?,
              forma_pagamento = ?,
              observacoes = ?,
              consideracoes = ?
            WHERE id_usuario = ? AND id = ?
        ");

        $stmt->execute([
            $data['status'] ?? 'rascunho',
            $data['data_emissao'],
            (int)($data['validade_dias'] ?? 15),
            $data['cliente_nome'],
            $data['cliente_email'] ?? null,
            $data['cliente_telefone'] ?? null,
            $data['cliente_endereco'] ?? null,
            $data['forma_pagamento'] ?? null,
            $data['observacoes'] ?? null,
            $data['consideracoes'] ?? null,
            $idUsuario,
            $id
        ]);

        $del = $pdo->prepare("DELETE FROM proposta_itens WHERE id_proposta = ?");
        $del->execute([$id]);

        self::salvarItens($pdo, $id, $itens);

        $total = self::recalcularTotal($pdo, $id);
        $upd = $pdo->prepare("UPDATE propostas SET total = ? WHERE id = ?");
        $upd->execute([$total, $id]);

        $pdo->commit();
    }

    public static function delete($pdo, int $idUsuario, int $id): void
    {
        $stmt = $pdo->prepare("DELETE FROM propostas WHERE id_usuario = ? AND id = ?");
        $stmt->execute([$idUsuario, $id]);
    }

    private static function salvarItens($pdo, int $idProposta, array $itens): void
    {
        $ins = $pdo->prepare("
            INSERT INTO proposta_itens
            (id_proposta, ordem, descricao, quantidade, valor_unit, valor_total)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $ordem = 1;
        foreach ($itens as $i) {
            $desc = trim((string)($i['descricao'] ?? ''));
            if ($desc === '') continue;

            $qtd  = (float)normalizaValor($i['quantidade'] ?? '1');
            $vu   = (float)normalizaValor($i['valor_unit'] ?? '0');
            $vt   = round($qtd * $vu, 2);

            $ins->execute([$idProposta, $ordem, $desc, $qtd, $vu, $vt]);
            $ordem++;
        }
    }

    public static function recalcularTotal($pdo, int $idProposta): float
    {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(valor_total),0) FROM proposta_itens WHERE id_proposta = ?");
        $stmt->execute([$idProposta]);
        return (float)$stmt->fetchColumn();
    }
}
