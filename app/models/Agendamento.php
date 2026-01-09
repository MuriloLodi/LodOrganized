<?php

class Agendamento
{
    public static function allByUsuario(PDO $pdo, int $idUsuario, array $f = []): array
    {
        $where = " WHERE id_usuario = ? ";
        $params = [$idUsuario];

        if (!empty($f['status'])) {
            $where .= " AND status = ? ";
            $params[] = $f['status'];
        }

        if (!empty($f['de'])) {
            $where .= " AND data_inicio >= ? ";
            $params[] = $f['de'] . " 00:00:00";
        }

        if (!empty($f['ate'])) {
            $where .= " AND data_inicio <= ? ";
            $params[] = $f['ate'] . " 23:59:59";
        }

        if (!empty($f['q'])) {
            $where .= " AND (
                titulo LIKE ?
                OR cliente_nome LIKE ?
                OR cliente_email LIKE ?
                OR cliente_telefone LIKE ?
            ) ";
            $like = "%" . $f['q'] . "%";
            array_push($params, $like, $like, $like, $like);
        }

        $sql = "SELECT * FROM agendamentos {$where} ORDER BY data_inicio DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById(PDO $pdo, int $idUsuario, int $id): ?array
    {
        $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id = ? AND id_usuario = ? LIMIT 1");
        $stmt->execute([$id, $idUsuario]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function create(PDO $pdo, int $idUsuario, array $d): int
    {
        $stmt = $pdo->prepare("
            INSERT INTO agendamentos
            (id_usuario, titulo, descricao, cliente_nome, cliente_email, cliente_telefone, local, data_inicio, data_fim, status, notificar_minutos)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $idUsuario,
            $d['titulo'],
            $d['descricao'] ?? null,
            $d['cliente_nome'] ?? null,
            $d['cliente_email'] ?? null,
            $d['cliente_telefone'] ?? null,
            $d['local'] ?? null,
            $d['data_inicio'],
            $d['data_fim'],
            $d['status'] ?? 'marcado',
            (int)($d['notificar_minutos'] ?? 60),
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $idUsuario, int $id, array $d): void
    {
        $stmt = $pdo->prepare("
            UPDATE agendamentos
               SET titulo=?,
                   descricao=?,
                   cliente_nome=?,
                   cliente_email=?,
                   cliente_telefone=?,
                   local=?,
                   data_inicio=?,
                   data_fim=?,
                   status=?,
                   notificar_minutos=?,
                   updated_at=NOW()
             WHERE id=? AND id_usuario=?
        ");
        $stmt->execute([
            $d['titulo'],
            $d['descricao'] ?? null,
            $d['cliente_nome'] ?? null,
            $d['cliente_email'] ?? null,
            $d['cliente_telefone'] ?? null,
            $d['local'] ?? null,
            $d['data_inicio'],
            $d['data_fim'],
            $d['status'] ?? 'marcado',
            (int)($d['notificar_minutos'] ?? 60),
            $id,
            $idUsuario
        ]);
    }

    public static function delete(PDO $pdo, int $idUsuario, int $id): void
    {
        $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id=? AND id_usuario=?");
        $stmt->execute([$id, $idUsuario]);
    }

    public static function updateStatus(PDO $pdo, int $idUsuario, int $id, string $status): void
    {
        $stmt = $pdo->prepare("UPDATE agendamentos SET status=?, updated_at=NOW() WHERE id=? AND id_usuario=?");
        $stmt->execute([$status, $id, $idUsuario]);
    }

    public static function bloqueios(PDO $pdo, int $idUsuario): array
    {
        $stmt = $pdo->prepare("SELECT * FROM agenda_bloqueios WHERE id_usuario=? ORDER BY created_at DESC");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function createBloqueioPeriodo(PDO $pdo, int $idUsuario, string $titulo, string $inicio, string $fim): int
    {
        $stmt = $pdo->prepare("
            INSERT INTO agenda_bloqueios (id_usuario, tipo, titulo, data_inicio, data_fim, ativo)
            VALUES (?, 'periodo', ?, ?, ?, 1)
        ");
        $stmt->execute([$idUsuario, $titulo, $inicio, $fim]);
        return (int)$pdo->lastInsertId();
    }

    public static function createBloqueioSemanal(PDO $pdo, int $idUsuario, string $titulo, int $diaSemana, string $horaIni, string $horaFim): int
    {
        $stmt = $pdo->prepare("
            INSERT INTO agenda_bloqueios (id_usuario, tipo, titulo, dia_semana, hora_inicio, hora_fim, ativo)
            VALUES (?, 'semanal', ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$idUsuario, $titulo, $diaSemana, $horaIni, $horaFim]);
        return (int)$pdo->lastInsertId();
    }

    public static function deleteBloqueio(PDO $pdo, int $idUsuario, int $id): void
    {
        $stmt = $pdo->prepare("DELETE FROM agenda_bloqueios WHERE id=? AND id_usuario=?");
        $stmt->execute([$id, $idUsuario]);
    }

    /**
     * Verifica conflito (agendamentos + bloqueios) no período.
     * $inicio/$fim no formato "Y-m-d H:i:s"
     */
    public static function conflito(PDO $pdo, int $idUsuario, string $inicio, string $fim, ?int $ignorarId = null): bool
    {
        // 1) conflito com agendamentos (exceto cancelado)
        $sql = "
            SELECT COUNT(*) c
              FROM agendamentos
             WHERE id_usuario=?
               AND status <> 'cancelado'
               AND NOT (data_fim <= ? OR data_inicio >= ?)
        ";
        $params = [$idUsuario, $inicio, $fim];

        if ($ignorarId) {
            $sql .= " AND id <> ? ";
            $params[] = $ignorarId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $c = (int)($stmt->fetchColumn() ?: 0);
        if ($c > 0) return true;

        // 2) conflito com bloqueios por período
        $stmt = $pdo->prepare("
            SELECT COUNT(*) c
              FROM agenda_bloqueios
             WHERE id_usuario=?
               AND ativo=1
               AND tipo='periodo'
               AND data_inicio IS NOT NULL
               AND data_fim IS NOT NULL
               AND NOT (data_fim <= ? OR data_inicio >= ?)
        ");
        $stmt->execute([$idUsuario, $inicio, $fim]);
        $c2 = (int)($stmt->fetchColumn() ?: 0);
        if ($c2 > 0) return true;

        // 3) bloqueios semanais (checa em PHP)
        $stmt = $pdo->prepare("
            SELECT dia_semana, hora_inicio, hora_fim
              FROM agenda_bloqueios
             WHERE id_usuario=?
               AND ativo=1
               AND tipo='semanal'
               AND dia_semana IS NOT NULL
               AND hora_inicio IS NOT NULL
               AND hora_fim IS NOT NULL
        ");
        $stmt->execute([$idUsuario]);
        $bloqs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if ($bloqs) {
            $iniTs = strtotime($inicio);
            $fimTs = strtotime($fim);

            $dia = (int)date('w', $iniTs); // 0..6
            $iniTime = (int)date('Hi', $iniTs);
            $fimTime = (int)date('Hi', $fimTs);

            foreach ($bloqs as $b) {
                if ((int)$b['dia_semana'] !== $dia) continue;

                $bIni = (int)str_replace(':', '', substr($b['hora_inicio'], 0, 5)); // HHMM
                $bFim = (int)str_replace(':', '', substr($b['hora_fim'], 0, 5));

                // overlap: NOT (fim <= bIni OR ini >= bFim)
                if (!($fimTime <= $bIni || $iniTime >= $bFim)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Slots disponíveis (público)
     * Padrão: 30min slot, 60min duração, seg-sex 09-18, sáb 09-12, dom fechado
     */
    public static function slotsDisponiveis(PDO $pdo, int $idUsuario, string $dataYmd, int $slotMin = 30, int $duracaoMin = 60): array
    {
        $diaSemana = (int)date('w', strtotime($dataYmd)); // 0..6

        // domingo fechado
        if ($diaSemana === 0) return [];

        // janelas padrão
        $inicioDia = "09:00";
        $fimDia = "18:00";

        // sábado reduz
        if ($diaSemana === 6) {
            $inicioDia = "09:00";
            $fimDia = "12:00";
        }

        $start = strtotime($dataYmd . " " . $inicioDia . ":00");
        $end   = strtotime($dataYmd . " " . $fimDia . ":00");

        $slots = [];
        for ($t = $start; $t + ($duracaoMin * 60) <= $end; $t += ($slotMin * 60)) {
            $ini = date('Y-m-d H:i:s', $t);
            $fim = date('Y-m-d H:i:s', $t + ($duracaoMin * 60));

            if (!self::conflito($pdo, $idUsuario, $ini, $fim, null)) {
                $slots[] = date('H:i', $t);
            }
        }

        return $slots;
    }
}
