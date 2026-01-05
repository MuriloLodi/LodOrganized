<?php

class Orcamento
{
    /**
     * Retorna um MAPA: [id_categoria => valor]
     */
    public static function mapByMes(PDO $pdo, int $idUsuario, int $ano, int $mes): array
    {
        $stmt = $pdo->prepare("
            SELECT id_categoria, valor
            FROM orcamentos
            WHERE id_usuario = ?
              AND ano = ?
              AND mes = ?
        ");
        $stmt->execute([$idUsuario, $ano, $mes]);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int)$row['id_categoria']] = (float)$row['valor'];
        }
        return $map;
    }

    public static function save(PDO $pdo, int $idUsuario, int $idCategoria, int $ano, int $mes, float $valor): bool
    {
        $stmt = $pdo->prepare("
            INSERT INTO orcamentos (id_usuario, id_categoria, ano, mes, valor)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)
        ");
        return $stmt->execute([$idUsuario, $idCategoria, $ano, $mes, $valor]);
    }
public static function estouradosNoMes(PDO $pdo, int $idUsuario, int $ano, int $mes): array
{
    $sql = "
        SELECT
            c.id AS id_categoria,
            c.nome,
            o.valor AS orcado,
            SUM(l.valor) AS total_real,
            (SUM(l.valor) / o.valor) * 100 AS percentual
        FROM orcamentos o
        JOIN categorias c 
            ON c.id = o.id_categoria
            AND c.tipo = 'D'
        JOIN lancamentos l 
            ON l.id_categoria = c.id
            AND l.id_usuario = o.id_usuario
            AND l.tipo = 'D'
            AND YEAR(l.data) = o.ano
            AND MONTH(l.data) = o.mes
        WHERE o.id_usuario = ?
          AND o.ano = ?
          AND o.mes = ?
        GROUP BY c.id, c.nome, o.valor
        HAVING SUM(l.valor) > o.valor
        ORDER BY percentual DESC
    ";

    $st = $pdo->prepare($sql);
    $st->execute([$idUsuario, $ano, $mes]);
    return $st->fetchAll();
}
public static function resumoGeralMes(PDO $pdo, int $idUsuario, int $ano, int $mes): array
{
    // Total orçado do mês
    $st1 = $pdo->prepare("
        SELECT IFNULL(SUM(valor), 0)
        FROM orcamentos
        WHERE id_usuario = ?
          AND ano = ?
          AND mes = ?
    ");
    $st1->execute([$idUsuario, $ano, $mes]);
    $orcado = (float)$st1->fetchColumn();

    // Total real gasto no mês (somente despesas)
    $st2 = $pdo->prepare("
        SELECT IFNULL(SUM(valor), 0)
        FROM lancamentos
        WHERE id_usuario = ?
          AND tipo = 'D'
          AND YEAR(data) = ?
          AND MONTH(data) = ?
    ");
    $st2->execute([$idUsuario, $ano, $mes]);
    $real = (float)$st2->fetchColumn();

    $percentual = ($orcado > 0) ? ($real / $orcado) * 100 : 0;

    return [
        'orcado'     => $orcado,
        'real'       => $real,
        'percentual' => $percentual
    ];
}
public static function preventivosNoMes(PDO $pdo, int $idUsuario, int $ano, int $mes): array
{
    $sql = "
        SELECT
            c.id AS id_categoria,
            c.nome,
            o.valor AS orcado,
            SUM(l.valor) AS total_real,
            (SUM(l.valor) / o.valor) * 100 AS percentual
        FROM orcamentos o
        JOIN categorias c 
            ON c.id = o.id_categoria
            AND c.tipo = 'D'
        JOIN lancamentos l 
            ON l.id_categoria = c.id
            AND l.id_usuario = o.id_usuario
            AND l.tipo = 'D'
            AND YEAR(l.data) = o.ano
            AND MONTH(l.data) = o.mes
        WHERE o.id_usuario = ?
          AND o.ano = ?
          AND o.mes = ?
        GROUP BY c.id, c.nome, o.valor
        HAVING (SUM(l.valor) / o.valor) * 100 >= 80
           AND (SUM(l.valor) / o.valor) * 100 < 100
        ORDER BY percentual DESC
    ";

    $st = $pdo->prepare($sql);
    $st->execute([$idUsuario, $ano, $mes]);
    return $st->fetchAll();
}

}
