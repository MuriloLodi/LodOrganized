<?php

class Relatorio
{
    public static function filtrarLancamentos(PDO $pdo, int $idUsuario, array $f): array
    {
        $sql = "
            SELECT l.id, l.data, l.descricao, l.tipo, l.valor, l.status,
                   c.nome AS categoria,
                   ct.nome AS conta
            FROM lancamentos l
            LEFT JOIN categorias c ON c.id = l.id_categoria
            LEFT JOIN contas ct ON ct.id = l.id_conta
            WHERE l.id_usuario = :id_usuario
        ";

        $params = [':id_usuario' => $idUsuario];

        if (!empty($f['data_inicio'])) {
            $sql .= " AND l.data >= :data_inicio ";
            $params[':data_inicio'] = $f['data_inicio'];
        }
        if (!empty($f['data_fim'])) {
            $sql .= " AND l.data <= :data_fim ";
            $params[':data_fim'] = $f['data_fim'];
        }
        if (!empty($f['id_conta'])) {
            $sql .= " AND l.id_conta = :id_conta ";
            $params[':id_conta'] = (int)$f['id_conta'];
        }
        if (!empty($f['id_categoria'])) {
            $sql .= " AND l.id_categoria = :id_categoria ";
            $params[':id_categoria'] = (int)$f['id_categoria'];
        }
        if (!empty($f['tipo']) && in_array($f['tipo'], ['R','D'], true)) {
            $sql .= " AND l.tipo = :tipo ";
            $params[':tipo'] = $f['tipo'];
        }
        if (!empty($f['status']) && in_array($f['status'], ['pago','pendente'], true)) {
            $sql .= " AND l.status = :status ";
            $params[':status'] = $f['status'];
        }

        $sql .= " ORDER BY l.data DESC, l.id DESC ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function resumo(PDO $pdo, int $idUsuario, array $f): array
    {
        // Reusa o mesmo filtro
        $base = "
            FROM lancamentos l
            WHERE l.id_usuario = :id_usuario
        ";
        $params = [':id_usuario' => $idUsuario];

        if (!empty($f['data_inicio'])) { $base .= " AND l.data >= :data_inicio "; $params[':data_inicio'] = $f['data_inicio']; }
        if (!empty($f['data_fim']))    { $base .= " AND l.data <= :data_fim ";    $params[':data_fim'] = $f['data_fim']; }
        if (!empty($f['id_conta']))    { $base .= " AND l.id_conta = :id_conta "; $params[':id_conta'] = (int)$f['id_conta']; }
        if (!empty($f['id_categoria'])){ $base .= " AND l.id_categoria = :id_categoria "; $params[':id_categoria'] = (int)$f['id_categoria']; }
        if (!empty($f['tipo']) && in_array($f['tipo'], ['R','D'], true)) { $base .= " AND l.tipo = :tipo "; $params[':tipo'] = $f['tipo']; }
        if (!empty($f['status']) && in_array($f['status'], ['pago','pendente'], true)) { $base .= " AND l.status = :status "; $params[':status'] = $f['status']; }

        // Totais por tipo
        $stmt = $pdo->prepare("
            SELECT
                SUM(CASE WHEN l.tipo='R' THEN l.valor ELSE 0 END) AS receitas,
                SUM(CASE WHEN l.tipo='D' THEN l.valor ELSE 0 END) AS despesas,
                SUM(CASE WHEN l.tipo='R' THEN l.valor ELSE -l.valor END) AS saldo,
                COUNT(*) AS qtd
            $base
        ");
        $stmt->execute($params);
        $r = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'receitas' => (float)($r['receitas'] ?? 0),
            'despesas' => (float)($r['despesas'] ?? 0),
            'saldo'    => (float)($r['saldo'] ?? 0),
            'qtd'      => (int)($r['qtd'] ?? 0),
        ];
    }

    public static function porCategoria(PDO $pdo, int $idUsuario, array $f): array
    {
        $sql = "
            SELECT
                c.nome,
                l.tipo,
                SUM(l.valor) AS total
            FROM lancamentos l
            LEFT JOIN categorias c ON c.id = l.id_categoria
            WHERE l.id_usuario = :id_usuario
        ";
        $params = [':id_usuario' => $idUsuario];

        if (!empty($f['data_inicio'])) { $sql .= " AND l.data >= :data_inicio "; $params[':data_inicio'] = $f['data_inicio']; }
        if (!empty($f['data_fim']))    { $sql .= " AND l.data <= :data_fim ";    $params[':data_fim'] = $f['data_fim']; }
        if (!empty($f['id_conta']))    { $sql .= " AND l.id_conta = :id_conta "; $params[':id_conta'] = (int)$f['id_conta']; }
        if (!empty($f['tipo']) && in_array($f['tipo'], ['R','D'], true)) { $sql .= " AND l.tipo = :tipo "; $params[':tipo'] = $f['tipo']; }
        if (!empty($f['status']) && in_array($f['status'], ['pago','pendente'], true)) { $sql .= " AND l.status = :status "; $params[':status'] = $f['status']; }

        $sql .= "
            GROUP BY c.nome, l.tipo
            ORDER BY total DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function porConta(PDO $pdo, int $idUsuario, array $f): array
    {
        $sql = "
            SELECT
                ct.nome,
                l.tipo,
                SUM(l.valor) AS total
            FROM lancamentos l
            LEFT JOIN contas ct ON ct.id = l.id_conta
            WHERE l.id_usuario = :id_usuario
        ";
        $params = [':id_usuario' => $idUsuario];

        if (!empty($f['data_inicio'])) { $sql .= " AND l.data >= :data_inicio "; $params[':data_inicio'] = $f['data_inicio']; }
        if (!empty($f['data_fim']))    { $sql .= " AND l.data <= :data_fim ";    $params[':data_fim'] = $f['data_fim']; }
        if (!empty($f['id_categoria'])){ $sql .= " AND l.id_categoria = :id_categoria "; $params[':id_categoria'] = (int)$f['id_categoria']; }
        if (!empty($f['tipo']) && in_array($f['tipo'], ['R','D'], true)) { $sql .= " AND l.tipo = :tipo "; $params[':tipo'] = $f['tipo']; }
        if (!empty($f['status']) && in_array($f['status'], ['pago','pendente'], true)) { $sql .= " AND l.status = :status "; $params[':status'] = $f['status']; }

        $sql .= "
            GROUP BY ct.nome, l.tipo
            ORDER BY total DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
