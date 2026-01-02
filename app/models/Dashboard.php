<?php

class Dashboard
{
    public static function resumoMensal($pdo, $idUsuario, $ano, $mes)
    {
        // Receitas
        $stmt = $pdo->prepare("
            SELECT SUM(valor) AS total
            FROM lancamentos
            WHERE id_usuario = ?
              AND tipo = 'R'
              AND YEAR(data) = ?
              AND MONTH(data) = ?
        ");
        $stmt->execute([$idUsuario, $ano, $mes]);
        $receitas = $stmt->fetch()['total'] ?? 0;

        // Despesas
        $stmt = $pdo->prepare("
            SELECT SUM(valor) AS total
            FROM lancamentos
            WHERE id_usuario = ?
              AND tipo = 'D'
              AND YEAR(data) = ?
              AND MONTH(data) = ?
        ");
        $stmt->execute([$idUsuario, $ano, $mes]);
        $despesas = $stmt->fetch()['total'] ?? 0;

        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'saldo'    => $receitas - $despesas
        ];
    }
    public static function saldoGeral($pdo, $idUsuario)
{
    $stmt = $pdo->prepare("
        SELECT SUM(saldo_atual) AS total
        FROM contas
        WHERE id_usuario = ?
    ");
    $stmt->execute([$idUsuario]);
    return $stmt->fetch()['total'] ?? 0;
}
public static function resumoPorTipo($pdo, $idUsuario, $filtros)
{
    $sql = "
        SELECT tipo, SUM(valor) AS total
        FROM lancamentos
        WHERE id_usuario = ?
    ";

    $params = [$idUsuario];

    if (!empty($filtros['data_inicio'])) {
        $sql .= " AND data >= ?";
        $params[] = $filtros['data_inicio'];
    }

    if (!empty($filtros['data_fim'])) {
        $sql .= " AND data <= ?";
        $params[] = $filtros['data_fim'];
    }

    $sql .= " GROUP BY tipo";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

public static function resumoPorCategoria($pdo, $idUsuario, $filtros)
{
    $sql = "
        SELECT cat.nome, SUM(l.valor) AS total
        FROM lancamentos l
        JOIN categorias cat ON cat.id = l.id_categoria
        WHERE l.id_usuario = ?
    ";

    $params = [$idUsuario];

    if (!empty($filtros['data_inicio'])) {
        $sql .= " AND l.data >= ?";
        $params[] = $filtros['data_inicio'];
    }

    if (!empty($filtros['data_fim'])) {
        $sql .= " AND l.data <= ?";
        $params[] = $filtros['data_fim'];
    }

    $sql .= " GROUP BY cat.nome";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

}
