<?php

class Proposta
{
    public static function listar($pdo, $idUsuario)
    {
        $stmt = $pdo->prepare("
            SELECT p.*, c.nome AS cliente_nome
            FROM propostas p
            LEFT JOIN clientes c ON c.id = p.id_cliente
            WHERE p.id_usuario = ?
            ORDER BY p.id DESC
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public static function proximoNumero($pdo, $idUsuario, $ano)
    {
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(numero),0)+1 AS prox FROM propostas WHERE id_usuario=? AND ano=?");
        $stmt->execute([$idUsuario, $ano]);
        $row = $stmt->fetch();
        return (int)($row['prox'] ?? 1);
    }

    public static function criar($pdo, $dados)
    {
        $stmt = $pdo->prepare("
            INSERT INTO propostas (id_usuario, id_cliente, numero, ano, status, data_emissao, validade_dias, desconto, observacoes)
            VALUES (?, ?, ?, ?, 'rascunho', ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dados['id_usuario'],
            $dados['id_cliente'] ?: null,
            $dados['numero'],
            $dados['ano'],
            $dados['data_emissao'],
            $dados['validade_dias'],
            $dados['desconto'],
            $dados['observacoes']
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function buscar($pdo, $idUsuario, $id)
    {
        $stmt = $pdo->prepare("SELECT * FROM propostas WHERE id_usuario=? AND id=?");
        $stmt->execute([$idUsuario, $id]);
        return $stmt->fetch();
    }

    public static function atualizar($pdo, $idUsuario, $id, $dados)
    {
        $stmt = $pdo->prepare("
            UPDATE propostas SET
              id_cliente=?,
              data_emissao=?,
              validade_dias=?,
              desconto=?,
              observacoes=?
            WHERE id_usuario=? AND id=?
        ");
        return $stmt->execute([
            $dados['id_cliente'] ?: null,
            $dados['data_emissao'],
            $dados['validade_dias'],
            $dados['desconto'],
            $dados['observacoes'],
            $idUsuario,
            $id
        ]);
    }

    public static function setStatus($pdo, $idUsuario, $id, $status)
    {
        $permitidos = ['rascunho','enviado','aprovado','recusado'];
        if (!in_array($status, $permitidos)) return false;

        $stmt = $pdo->prepare("UPDATE propostas SET status=? WHERE id_usuario=? AND id=?");
        return $stmt->execute([$status, $idUsuario, $id]);
    }

    public static function excluir($pdo, $idUsuario, $id)
    {
        // itens deletam junto (ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM propostas WHERE id_usuario=? AND id=?");
        return $stmt->execute([$idUsuario, $id]);
    }

    // ===== ITENS =====
    public static function itens($pdo, $idProposta)
    {
        $stmt = $pdo->prepare("SELECT * FROM proposta_itens WHERE id_proposta=? ORDER BY id ASC");
        $stmt->execute([$idProposta]);
        return $stmt->fetchAll();
    }

    public static function limparItens($pdo, $idProposta)
    {
        $stmt = $pdo->prepare("DELETE FROM proposta_itens WHERE id_proposta=?");
        return $stmt->execute([$idProposta]);
    }

    public static function adicionarItem($pdo, $idProposta, $item)
    {
        $total = (float)$item['qtd'] * (float)$item['valor_unit'];

        $stmt = $pdo->prepare("
            INSERT INTO proposta_itens (id_proposta, id_servico, descricao, qtd, valor_unit, total)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $idProposta,
            $item['id_servico'] ?: null,
            $item['descricao'],
            $item['qtd'],
            $item['valor_unit'],
            $total
        ]);
    }

    public static function totais($pdo, $idProposta)
    {
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total),0) AS subtotal
            FROM proposta_itens
            WHERE id_proposta=?
        ");
        $stmt->execute([$idProposta]);
        $r = $stmt->fetch();
        return (float)($r['subtotal'] ?? 0);
    }
}
