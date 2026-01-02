<?php

class Lancamento
{
    public static function allByUsuario($pdo, $idUsuario)
    {
        $stmt = $pdo->prepare("
            SELECT l.*, 
                   c.nome AS conta,
                   cat.nome AS categoria
            FROM lancamentos l
            JOIN contas c ON c.id = l.id_conta
            JOIN categorias cat ON cat.id = l.id_categoria
            WHERE l.id_usuario = ?
            ORDER BY l.data DESC
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public static function create($pdo, $dados)
    {
        $stmt = $pdo->prepare("
            INSERT INTO lancamentos 
            (id_usuario, id_conta, id_categoria, tipo, valor, data, descricao)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $dados['id_usuario'],
            $dados['id_conta'],
            $dados['id_categoria'],
            $dados['tipo'],
            $dados['valor'],
            $dados['data'],
            $dados['descricao']
        ]);
    }
    public static function find($pdo, $id, $idUsuario)
{
    $stmt = $pdo->prepare("
        SELECT * FROM lancamentos
        WHERE id = ? AND id_usuario = ?
    ");
    $stmt->execute([$id, $idUsuario]);
    return $stmt->fetch();
}

public static function update($pdo, $id, $dados)
{
    $stmt = $pdo->prepare("
        UPDATE lancamentos SET
            id_conta = ?,
            id_categoria = ?,
            tipo = ?,
            valor = ?,
            data = ?,
            descricao = ?
        WHERE id = ?
    ");

    return $stmt->execute([
        $dados['id_conta'],
        $dados['id_categoria'],
        $dados['tipo'],
        $dados['valor'],
        $dados['data'],
        $dados['descricao'],
        $id
    ]);
}

public static function delete($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM lancamentos WHERE id = ?");
    return $stmt->execute([$id]);
}
public static function filtrar($pdo, $idUsuario, $filtros)
{
    $sql = "
        SELECT l.*, 
               c.nome AS conta,
               cat.nome AS categoria
        FROM lancamentos l
        JOIN contas c ON c.id = l.id_conta
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

    if (!empty($filtros['id_conta'])) {
        $sql .= " AND l.id_conta = ?";
        $params[] = $filtros['id_conta'];
    }

    if (!empty($filtros['id_categoria'])) {
        $sql .= " AND l.id_categoria = ?";
        $params[] = $filtros['id_categoria'];
    }

    $sql .= " ORDER BY l.data DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

}
