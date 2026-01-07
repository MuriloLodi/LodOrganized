<?php
require_once __DIR__ . '/../helpers/helpers.php';

class Lancamento
{
    public static function filtrar($pdo, $idUsuario, $filtros)
    {
        $sql = "
            SELECT l.*,
                   c.nome AS conta,
                   cat.nome AS categoria
            FROM lancamentos l
            JOIN contas c ON c.id = l.id_conta
            LEFT JOIN categorias cat ON cat.id = l.id_categoria
            WHERE l.id_usuario = ?
        ";
        $params = [$idUsuario];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND l.data >= ? ";
            $params[] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND l.data <= ? ";
            $params[] = $filtros['data_fim'];
        }
        if (!empty($filtros['id_conta'])) {
            $sql .= " AND l.id_conta = ? ";
            $params[] = $filtros['id_conta'];
        }
        if (!empty($filtros['id_categoria'])) {
            $sql .= " AND l.id_categoria = ? ";
            $params[] = $filtros['id_categoria'];
        }

        $sql .= " ORDER BY l.data DESC, l.id DESC ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find($pdo, $id, $idUsuario)
    {
        $stmt = $pdo->prepare("SELECT * FROM lancamentos WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    public static function create($pdo, $dados)
    {
        $stmt = $pdo->prepare("
            INSERT INTO lancamentos
            (id_usuario, id_conta, id_categoria, tipo, valor, data, descricao, status, grupo_uuid, recorrencia_id, parcelamento_id, parcela_num, parcela_total, transferencia_id)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $dados['id_usuario'],
            $dados['id_conta'],
            $dados['id_categoria'] ?? null,
            $dados['tipo'],
            $dados['valor'],
            $dados['data'],
            $dados['descricao'] ?? null,
            $dados['status'] ?? 'pago',
            $dados['grupo_uuid'] ?? null,
            $dados['recorrencia_id'] ?? null,
            $dados['parcelamento_id'] ?? null,
            $dados['parcela_num'] ?? null,
            $dados['parcela_total'] ?? null,
            $dados['transferencia_id'] ?? null,
        ]);
    }

    public static function update($pdo, $id, $idUsuario, $novo)
    {
        $stmt = $pdo->prepare("
            UPDATE lancamentos
            SET id_conta = ?, id_categoria = ?, tipo = ?, valor = ?, data = ?, descricao = ?
            WHERE id = ? AND id_usuario = ?
        ");

        return $stmt->execute([
            $novo['id_conta'],
            $novo['id_categoria'] ?? null,
            $novo['tipo'],
            $novo['valor'],
            $novo['data'],
            $novo['descricao'] ?? null,
            $id,
            $idUsuario
        ]);
    }

    public static function delete($pdo, $id, $idUsuario)
    {
        // remove anexos primeiro
        $stmt = $pdo->prepare("DELETE FROM lancamento_anexos WHERE id_usuario = ? AND id_lancamento = ?");
        $stmt->execute([$idUsuario, $id]);

        $stmt = $pdo->prepare("DELETE FROM lancamentos WHERE id = ? AND id_usuario = ?");
        return $stmt->execute([$id, $idUsuario]);
    }

    public static function togglePago($pdo, $id, $idUsuario): array
    {
        $l = self::find($pdo, $id, $idUsuario);
        if (!$l) return ['ok' => false];

        $novo = ($l['status'] === 'pago') ? 'pendente' : 'pago';

        $stmt = $pdo->prepare("UPDATE lancamentos SET status = ? WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$novo, $id, $idUsuario]);

        return ['ok' => true, 'novo' => $novo, 'lancamento' => $l];
    }

    /* =========================
       TRANSFERÊNCIA
       ========================= */
    public static function criarTransferencia($pdo, $idUsuario, $idOrigem, $idDestino, $valor, $data, $descricao)
    {
        $pdo->beginTransaction();
        try {
            // cria registro de transferência
            $stmt = $pdo->prepare("
                INSERT INTO transferencias (id_usuario, id_conta_origem, id_conta_destino, valor, data, descricao)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$idUsuario, $idOrigem, $idDestino, $valor, $data, $descricao]);
            $idTransf = (int)$pdo->lastInsertId();

            $grupo = uuidv4();

            // lançamento D na origem
            self::create($pdo, [
                'id_usuario' => $idUsuario,
                'id_conta' => $idOrigem,
                'id_categoria' => null,
                'tipo' => 'D',
                'valor' => $valor,
                'data' => $data,
                'descricao' => $descricao ? ("Transferência: ".$descricao) : "Transferência (origem)",
                'status' => 'pago',
                'grupo_uuid' => $grupo,
                'transferencia_id' => $idTransf,
            ]);

            // lançamento R no destino
            self::create($pdo, [
                'id_usuario' => $idUsuario,
                'id_conta' => $idDestino,
                'id_categoria' => null,
                'tipo' => 'R',
                'valor' => $valor,
                'data' => $data,
                'descricao' => $descricao ? ("Transferência: ".$descricao) : "Transferência (destino)",
                'status' => 'pago',
                'grupo_uuid' => $grupo,
                'transferencia_id' => $idTransf,
            ]);

            $pdo->commit();
            return ['ok' => true];

        } catch (Exception $e) {
            $pdo->rollBack();
            return ['ok' => false, 'erro' => $e->getMessage()];
        }
    }

    /* =========================
       PARCELAS (gera N lançamentos)
       - só altera saldo quando status = pago
       ========================= */
    public static function criarParcelamento($pdo, $dados)
    {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO parcelamentos
                (id_usuario, tipo, id_conta, id_categoria, descricao, valor_total, total_parcelas, data_inicio)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $dados['id_usuario'],
                $dados['tipo'],
                $dados['id_conta'],
                $dados['id_categoria'] ?? null,
                $dados['descricao'] ?? null,
                $dados['valor_total'],
                $dados['total_parcelas'],
                $dados['data_inicio'],
            ]);
            $parcelamentoId = (int)$pdo->lastInsertId();
            $grupo = uuidv4();

            $valorParcela = round($dados['valor_total'] / $dados['total_parcelas'], 2);

            $dataBase = new DateTime($dados['data_inicio']);

            for ($i = 1; $i <= (int)$dados['total_parcelas']; $i++) {
                $dt = clone $dataBase;
                $dt->modify('+' . ($i - 1) . ' month');

                $status = ($i === 1 && !empty($dados['pagar_primeira'])) ? 'pago' : 'pendente';

                self::create($pdo, [
                    'id_usuario' => $dados['id_usuario'],
                    'id_conta' => $dados['id_conta'],
                    'id_categoria' => $dados['id_categoria'] ?? null,
                    'tipo' => $dados['tipo'], // geralmente D
                    'valor' => $valorParcela,
                    'data' => $dt->format('Y-m-d'),
                    'descricao' => ($dados['descricao'] ?? 'Parcelamento') . " ({$i}/{$dados['total_parcelas']})",
                    'status' => $status,
                    'grupo_uuid' => $grupo,
                    'parcelamento_id' => $parcelamentoId,
                    'parcela_num' => $i,
                    'parcela_total' => (int)$dados['total_parcelas']
                ]);
            }

            $pdo->commit();
            return ['ok' => true];

        } catch (Exception $e) {
            $pdo->rollBack();
            return ['ok' => false, 'erro' => $e->getMessage()];
        }
    }

    /* =========================
       RECORRÊNCIA
       ========================= */
    public static function criarRecorrencia($pdo, $dados)
    {
        $stmt = $pdo->prepare("
            INSERT INTO recorrencias
            (id_usuario, tipo, id_conta, id_categoria, valor, descricao, frequencia, dia_mes, dia_semana, ativo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        return $stmt->execute([
            $dados['id_usuario'],
            $dados['tipo'],
            $dados['id_conta'],
            $dados['id_categoria'] ?? null,
            $dados['valor'],
            $dados['descricao'] ?? null,
            $dados['frequencia'],
            $dados['dia_mes'] ?? null,
            $dados['dia_semana'] ?? null,
        ]);
    }

    public static function listarRecorrencias($pdo, $idUsuario)
    {
        $stmt = $pdo->prepare("
            SELECT r.*, c.nome AS conta, cat.nome AS categoria
            FROM recorrencias r
            JOIN contas c ON c.id = r.id_conta
            LEFT JOIN categorias cat ON cat.id = r.id_categoria
            WHERE r.id_usuario = ? AND r.ativo = 1
            ORDER BY r.id DESC
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public static function gerarRecorrenciasDoMes($pdo, $idUsuario, $ano, $mes, $marcarPago = true)
    {
        $rec = self::listarRecorrencias($pdo, $idUsuario);
        $gerados = 0;

        $pdo->beginTransaction();
        try {
            foreach ($rec as $r) {
                // evita duplicar
                $chk = $pdo->prepare("SELECT id FROM recorrencia_execucoes WHERE recorrencia_id = ? AND ano = ? AND mes = ?");
                $chk->execute([(int)$r['id'], (int)$ano, (int)$mes]);
                if ($chk->fetch()) continue;

                // data do lançamento dentro do mês
                $dataLanc = null;

                if ($r['frequencia'] === 'mensal') {
                    $dia = (int)($r['dia_mes'] ?: 1);
                    $dia = max(1, min($dia, 28)); // evita meses curtos
                    $dataLanc = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                    self::create($pdo, [
                        'id_usuario' => $idUsuario,
                        'id_conta' => $r['id_conta'],
                        'id_categoria' => $r['id_categoria'],
                        'tipo' => $r['tipo'],
                        'valor' => (float)$r['valor'],
                        'data' => $dataLanc,
                        'descricao' => $r['descricao'] ?? 'Recorrente',
                        'status' => $marcarPago ? 'pago' : 'pendente',
                        'recorrencia_id' => (int)$r['id'],
                        'grupo_uuid' => uuidv4()
                    ]);
                    $gerados++;
                } else {
                    // semanal: cria 4/5 ocorrências dentro do mês baseado em dia_semana
                    $dow = (int)($r['dia_semana'] ?: 1); // 1..7 seg..dom
                    $dt = new DateTime(sprintf('%04d-%02d-01', $ano, $mes));

                    $diasNoMes = (int)$dt->format('t');
                    for ($d = 1; $d <= $diasNoMes; $d++) {
                        $dt2 = new DateTime(sprintf('%04d-%02d-%02d', $ano, $mes, $d));
                        // PHP: N => 1 (seg) .. 7 (dom)
                        if ((int)$dt2->format('N') === $dow) {
                            self::create($pdo, [
                                'id_usuario' => $idUsuario,
                                'id_conta' => $r['id_conta'],
                                'id_categoria' => $r['id_categoria'],
                                'tipo' => $r['tipo'],
                                'valor' => (float)$r['valor'],
                                'data' => $dt2->format('Y-m-d'),
                                'descricao' => ($r['descricao'] ?? 'Recorrente') . ' (semanal)',
                                'status' => $marcarPago ? 'pago' : 'pendente',
                                'recorrencia_id' => (int)$r['id'],
                                'grupo_uuid' => uuidv4()
                            ]);
                            $gerados++;
                        }
                    }
                }

                $ins = $pdo->prepare("INSERT INTO recorrencia_execucoes (recorrencia_id, ano, mes) VALUES (?, ?, ?)");
                $ins->execute([(int)$r['id'], (int)$ano, (int)$mes]);
            }

            $pdo->commit();
            return ['ok' => true, 'gerados' => $gerados];

        } catch (Exception $e) {
            $pdo->rollBack();
            return ['ok' => false, 'erro' => $e->getMessage()];
        }
    }

    /* =========================
       ANEXOS
       ========================= */
    public static function anexos($pdo, $idUsuario, $idLancamento)
    {
        $stmt = $pdo->prepare("
            SELECT * FROM lancamento_anexos
            WHERE id_usuario = ? AND id_lancamento = ?
            ORDER BY id DESC
        ");
        $stmt->execute([$idUsuario, $idLancamento]);
        return $stmt->fetchAll();
    }

    public static function salvarAnexo($pdo, $idUsuario, $idLancamento, $arquivo, $mime, $tamanho)
    {
        $stmt = $pdo->prepare("
            INSERT INTO lancamento_anexos (id_usuario, id_lancamento, arquivo, mime, tamanho)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$idUsuario, $idLancamento, $arquivo, $mime, $tamanho]);
    }

    public static function excluirAnexo($pdo, $idUsuario, $idAnexo)
    {
        $stmt = $pdo->prepare("SELECT * FROM lancamento_anexos WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$idAnexo, $idUsuario]);
        $a = $stmt->fetch();

        if (!$a) return null;

        $del = $pdo->prepare("DELETE FROM lancamento_anexos WHERE id = ? AND id_usuario = ?");
        $del->execute([$idAnexo, $idUsuario]);

        return $a;
    }
    public static function totalPorCategoriaMes($pdo, $idUsuario, $ano, $mes)
{
    $stmt = $pdo->prepare("
        SELECT id_categoria, SUM(valor) AS total
        FROM lancamentos
        WHERE id_usuario = ?
          AND tipo = 'D'
          AND status = 'pago'
          AND id_categoria IS NOT NULL
          AND YEAR(data) = ?
          AND MONTH(data) = ?
        GROUP BY id_categoria
    ");
    $stmt->execute([$idUsuario, (int)$ano, (int)$mes]);

    $map = [];
    foreach ($stmt->fetchAll() as $r) {
        $map[(int)$r['id_categoria']] = (float)$r['total'];
    }
    return $map;
}

public static function topDespesasMes($pdo, $idUsuario, $ano, $mes, $limit = 6)
{
    $stmt = $pdo->prepare("
        SELECT c.nome AS categoria, SUM(l.valor) AS total
        FROM lancamentos l
        JOIN categorias c ON c.id = l.id_categoria
        WHERE l.id_usuario = ?
          AND l.tipo = 'D'
          AND l.status = 'pago'
          AND YEAR(l.data) = ?
          AND MONTH(l.data) = ?
        GROUP BY c.nome
        ORDER BY total DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $idUsuario, PDO::PARAM_INT);
    $stmt->bindValue(2, (int)$ano, PDO::PARAM_INT);
    $stmt->bindValue(3, (int)$mes, PDO::PARAM_INT);
    $stmt->bindValue(4, (int)$limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

public static function findById($pdo, $idUsuario, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM lancamentos WHERE id = ? AND id_usuario = ? LIMIT 1");
    $stmt->execute([(int)$id, (int)$idUsuario]);
    return $stmt->fetch();
}

public static function updateById($pdo, $idUsuario, $id, $dados)
{
    $stmt = $pdo->prepare("
        UPDATE lancamentos
        SET tipo = ?,
            id_conta = ?,
            id_categoria = ?,
            valor = ?,
            data = ?,
            descricao = ?,
            status = ?
        WHERE id = ? AND id_usuario = ?
        LIMIT 1
    ");

    return $stmt->execute([
        $dados['tipo'],
        (int)$dados['id_conta'],
        $dados['id_categoria'],
        (float)$dados['valor'],
        $dados['data'],
        $dados['descricao'],
        $dados['status'],
        (int)$id,
        (int)$idUsuario
    ]);
}

public static function deleteById($pdo, $idUsuario, $id)
{
    $stmt = $pdo->prepare("DELETE FROM lancamentos WHERE id = ? AND id_usuario = ? LIMIT 1");
    return $stmt->execute([(int)$id, (int)$idUsuario]);
}
}
