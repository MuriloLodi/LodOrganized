<?php

class Dashboard
{
    private static function prevMonth($ano, $mes)
    {
        $ano = (int)$ano; $mes = (int)$mes;
        $mes--;
        if ($mes <= 0) { $mes = 12; $ano--; }
        return [$ano, $mes];
    }

    public static function resumoMes($pdo, $idUsuario, $ano, $mes, $idConta = null)
    {
        $whereConta = "";
        $params = [(int)$idUsuario, (int)$ano, (int)$mes];

        if (!empty($idConta)) {
            $whereConta = " AND id_conta = ? ";
            $params[] = (int)$idConta;
        }

        $stmt = $pdo->prepare("
            SELECT
                SUM(CASE WHEN tipo='R' AND status='pago' THEN valor ELSE 0 END) AS receitas,
                SUM(CASE WHEN tipo='D' AND status='pago' THEN valor ELSE 0 END) AS despesas
            FROM lancamentos
            WHERE id_usuario = ?
              AND YEAR(data)=?
              AND MONTH(data)=?
              $whereConta
        ");
        $stmt->execute($params);
        $r = $stmt->fetch() ?: ['receitas'=>0,'despesas'=>0];

        $receitas = (float)($r['receitas'] ?? 0);
        $despesas = (float)($r['despesas'] ?? 0);

        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'saldo'    => $receitas - $despesas
        ];
    }

    public static function cardsPorConta($pdo, $idUsuario, $ano, $mes)
    {
        [$anoAnt, $mesAnt] = self::prevMonth($ano, $mes);

        // contas + saldo atual (se sua tabela já tem saldo_atual, ok)
        $stmt = $pdo->prepare("
            SELECT id, nome, saldo_atual
            FROM contas
            WHERE id_usuario = ?
            ORDER BY nome
        ");
        $stmt->execute([(int)$idUsuario]);
        $contas = $stmt->fetchAll();

        if (empty($contas)) return [];

        // movimentos do mês atual por conta
        $stmt = $pdo->prepare("
            SELECT id_conta,
                   SUM(CASE WHEN tipo='R' AND status='pago' THEN valor ELSE 0 END) AS receitas,
                   SUM(CASE WHEN tipo='D' AND status='pago' THEN valor ELSE 0 END) AS despesas
            FROM lancamentos
            WHERE id_usuario = ?
              AND YEAR(data)=?
              AND MONTH(data)=?
            GROUP BY id_conta
        ");
        $stmt->execute([(int)$idUsuario, (int)$ano, (int)$mes]);
        $mapMes = [];
        foreach ($stmt->fetchAll() as $r) {
            $mapMes[(int)$r['id_conta']] = [
                'receitas' => (float)$r['receitas'],
                'despesas' => (float)$r['despesas'],
            ];
        }

        // movimentos do mês anterior por conta
        $stmt = $pdo->prepare("
            SELECT id_conta,
                   SUM(CASE WHEN tipo='R' AND status='pago' THEN valor ELSE 0 END) AS receitas,
                   SUM(CASE WHEN tipo='D' AND status='pago' THEN valor ELSE 0 END) AS despesas
            FROM lancamentos
            WHERE id_usuario = ?
              AND YEAR(data)=?
              AND MONTH(data)=?
            GROUP BY id_conta
        ");
        $stmt->execute([(int)$idUsuario, (int)$anoAnt, (int)$mesAnt]);
        $mapAnt = [];
        foreach ($stmt->fetchAll() as $r) {
            $mapAnt[(int)$r['id_conta']] = [
                'receitas' => (float)$r['receitas'],
                'despesas' => (float)$r['despesas'],
            ];
        }

        $out = [];
        foreach ($contas as $c) {
            $idConta = (int)$c['id'];

            $m  = $mapMes[$idConta] ?? ['receitas'=>0,'despesas'=>0];
            $ma = $mapAnt[$idConta] ?? ['receitas'=>0,'despesas'=>0];

            $saldoMes  = (float)$m['receitas'] - (float)$m['despesas'];
            $saldoAnt  = (float)$ma['receitas'] - (float)$ma['despesas'];
            $delta     = $saldoMes - $saldoAnt;

            $out[] = [
                'id'         => $idConta,
                'nome'       => $c['nome'],
                'saldo_atual'=> (float)($c['saldo_atual'] ?? 0),
                'mes_receitas'=> (float)$m['receitas'],
                'mes_despesas'=> (float)$m['despesas'],
                'mes_saldo'   => $saldoMes,
                'delta_mes'   => $delta,
            ];
        }

        return $out;
    }

    public static function topCategoriasMes($pdo, $idUsuario, $ano, $mes, $idConta = null, $limit = 6)
    {
        [$anoAnt, $mesAnt] = self::prevMonth($ano, $mes);

        $whereConta = "";
        $params = [(int)$idUsuario, (int)$ano, (int)$mes];

        if (!empty($idConta)) {
            $whereConta = " AND l.id_conta = ? ";
            $params[] = (int)$idConta;
        }

        // Top do mês
        $sql = "
            SELECT l.id_categoria, c.nome,
                   SUM(CASE WHEN l.tipo='D' AND l.status='pago' THEN l.valor ELSE 0 END) AS total
            FROM lancamentos l
            JOIN categorias c ON c.id = l.id_categoria
            WHERE l.id_usuario = ?
              AND YEAR(l.data)=?
              AND MONTH(l.data)=?
              AND l.id_categoria IS NOT NULL
              $whereConta
            GROUP BY l.id_categoria, c.nome
            ORDER BY total DESC
            LIMIT ".(int)$limit."
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $top = $stmt->fetchAll();

        if (empty($top)) return [];

        // Totais do mês anterior (para tendência)
        $whereConta2 = "";
        $params2 = [(int)$idUsuario, (int)$anoAnt, (int)$mesAnt];
        if (!empty($idConta)) {
            $whereConta2 = " AND l.id_conta = ? ";
            $params2[] = (int)$idConta;
        }

        $stmt = $pdo->prepare("
            SELECT l.id_categoria, SUM(CASE WHEN l.tipo='D' AND l.status='pago' THEN l.valor ELSE 0 END) AS total
            FROM lancamentos l
            WHERE l.id_usuario = ?
              AND YEAR(l.data)=?
              AND MONTH(l.data)=?
              AND l.id_categoria IS NOT NULL
              $whereConta2
            GROUP BY l.id_categoria
        ");
        $stmt->execute($params2);

        $mapAnt = [];
        foreach ($stmt->fetchAll() as $r) {
            $mapAnt[(int)$r['id_categoria']] = (float)$r['total'];
        }

        $max = 0.0;
        foreach ($top as $t) $max = max($max, (float)$t['total']);
        if ($max <= 0) $max = 1;

        $out = [];
        foreach ($top as $t) {
            $idCat = (int)$t['id_categoria'];
            $total = (float)$t['total'];
            $ant   = (float)($mapAnt[$idCat] ?? 0);

            $diff = $total - $ant;
            $trend = 'igual';
            if (abs($diff) >= 0.01) $trend = $diff > 0 ? 'up' : 'down';

            $out[] = [
                'id_categoria' => $idCat,
                'nome'         => $t['nome'],
                'total'        => $total,
                'anterior'     => $ant,
                'diff'         => $diff,
                'trend'        => $trend,
                'w'            => min(($total / $max) * 100, 100)
            ];
        }

        return $out;
    }

    public static function metasMes($pdo, $idUsuario, $ano, $mes, $idConta = null)
    {
        $params = [(int)$idUsuario, (int)$ano, (int)$mes];
        $whereConta = "";

        if (!empty($idConta)) {
            $whereConta = " AND l.id_conta = ? ";
            $params[] = (int)$idConta;
        }

        // metas do mês
        $stmt = $pdo->prepare("
            SELECT m.id, m.id_categoria, c.nome, m.valor_limite
            FROM metas m
            JOIN categorias c ON c.id = m.id_categoria
            WHERE m.id_usuario = ?
              AND m.ano = ?
              AND m.mes = ?
            ORDER BY c.nome
        ");
        $stmt->execute([(int)$idUsuario, (int)$ano, (int)$mes]);
        $metas = $stmt->fetchAll();

        if (empty($metas)) return [];

        // gasto real por categoria no mês (despesas pagas)
        $stmt = $pdo->prepare("
            SELECT l.id_categoria, SUM(l.valor) AS total
            FROM lancamentos l
            WHERE l.id_usuario = ?
              AND YEAR(l.data)=?
              AND MONTH(l.data)=?
              AND l.tipo='D'
              AND l.status='pago'
              AND l.id_categoria IS NOT NULL
              $whereConta
            GROUP BY l.id_categoria
        ");
        $stmt->execute($params);

        $mapReal = [];
        foreach ($stmt->fetchAll() as $r) {
            $mapReal[(int)$r['id_categoria']] = (float)$r['total'];
        }

        $out = [];
        foreach ($metas as $m) {
            $limite = (float)$m['valor_limite'];
            $real   = (float)($mapReal[(int)$m['id_categoria']] ?? 0);
            $pct    = $limite > 0 ? ($real / $limite) * 100 : 0;
            $barra  = min($pct, 100);

            $status = 'ok';
            if ($limite > 0) {
                if ($pct >= 100) $status = 'danger';
                else if ($pct >= 80) $status = 'warning';
            }

            $out[] = [
                'id'          => (int)$m['id'],
                'id_categoria'=> (int)$m['id_categoria'],
                'nome'        => $m['nome'],
                'limite'      => $limite,
                'real'        => $real,
                'pct'         => $pct,
                'barra'       => $barra,
                'status'      => $status
            ];
        }

        return $out;
    }

    public static function salvarMeta($pdo, $idUsuario, $ano, $mes, $idCategoria, $valorLimite)
    {
        // UPSERT (MySQL/MariaDB)
        $stmt = $pdo->prepare("
            INSERT INTO metas (id_usuario, id_categoria, ano, mes, valor_limite)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE valor_limite = VALUES(valor_limite)
        ");
        return $stmt->execute([
            (int)$idUsuario,
            (int)$idCategoria,
            (int)$ano,
            (int)$mes,
            (float)$valorLimite
        ]);
    }
}
