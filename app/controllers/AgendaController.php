<?php
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/Agendamento.php';

class AgendaController
{
    private static function dtLocalToDb($s): string
    {
        // "2026-01-09T10:30" -> "2026-01-09 10:30:00"
        $s = trim((string)$s);
        if ($s === '') return '';
        $s = str_replace('T', ' ', $s);
        if (strlen($s) === 16) $s .= ':00';
        return $s;
    }

    private static function dbToDtLocal($s): string
    {
        // "2026-01-09 10:30:00" -> "2026-01-09T10:30"
        $s = trim((string)$s);
        if ($s === '') return '';
        return substr(str_replace(' ', 'T', $s), 0, 16);
    }

    private static function ensureToken(PDO $pdo, int $idUsuario): string
    {
        $stmt = $pdo->prepare("SELECT agenda_token FROM usuarios WHERE id=? LIMIT 1");
        $stmt->execute([$idUsuario]);
        $token = (string)($stmt->fetchColumn() ?: '');

        if ($token !== '') return $token;

        $token = bin2hex(random_bytes(16));
        $up = $pdo->prepare("UPDATE usuarios SET agenda_token=? WHERE id=?");
        $up->execute([$token, $idUsuario]);

        return $token;
    }

    public static function index(PDO $pdo)
    {
        $idUsuario = usuarioId();

        // garante token público
        $token = self::ensureToken($pdo, $idUsuario);

        $filtros = [
            'status' => $_GET['status'] ?? '',
            'de'     => $_GET['de'] ?? '',
            'ate'    => $_GET['ate'] ?? '',
            'q'      => $_GET['q'] ?? '',
        ];

        $agendamentos = Agendamento::allByUsuario($pdo, $idUsuario, $filtros);
        $bloqueios    = Agendamento::bloqueios($pdo, $idUsuario);

        // link público
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base  = $proto . '://' . $host;
        $publicLink = $base . "/financas/public/?url=agendar&token=" . urlencode($token);

        $titulo = "Agendamentos";
        $view = __DIR__ . '/../views/agenda/index_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function createForm(PDO $pdo)
    {
        $idUsuario = usuarioId();
        self::ensureToken($pdo, $idUsuario);

        $ag = [
            'id' => null,
            'titulo' => '',
            'descricao' => '',
            'cliente_nome' => '',
            'cliente_email' => '',
            'cliente_telefone' => '',
            'local' => '',
            'data_inicio' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'data_fim'    => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'status' => 'marcado',
            'notificar_minutos' => 60
        ];

        $titulo = "Novo agendamento";
        $view = __DIR__ . '/../views/agenda/form_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function edit(PDO $pdo)
    {
        $idUsuario = usuarioId();
        $id = (int)($_GET['id'] ?? 0);

        $ag = Agendamento::findById($pdo, $idUsuario, $id);
        if (!$ag) {
            $_SESSION['erro'] = "Agendamento não encontrado.";
            header("Location: /financas/public/?url=agenda");
            exit;
        }

        $titulo = "Editar agendamento";
        $view = __DIR__ . '/../views/agenda/form_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function store(PDO $pdo)
    {
        $idUsuario = usuarioId();

        $inicio = self::dtLocalToDb($_POST['data_inicio'] ?? '');
        $fim    = self::dtLocalToDb($_POST['data_fim'] ?? '');

        $d = [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'cliente_nome' => trim($_POST['cliente_nome'] ?? ''),
            'cliente_email' => trim($_POST['cliente_email'] ?? ''),
            'cliente_telefone' => trim($_POST['cliente_telefone'] ?? ''),
            'local' => trim($_POST['local'] ?? ''),
            'data_inicio' => $inicio,
            'data_fim' => $fim,
            'status' => $_POST['status'] ?? 'marcado',
            'notificar_minutos' => (int)($_POST['notificar_minutos'] ?? 60),
        ];

        if ($d['titulo'] === '' || $inicio === '' || $fim === '') {
            $_SESSION['erro'] = "Preencha título, início e fim.";
            header("Location: /financas/public/?url=agenda-new");
            exit;
        }

        if (strtotime($fim) <= strtotime($inicio)) {
            $_SESSION['erro'] = "A data/hora final deve ser maior que a inicial.";
            header("Location: /financas/public/?url=agenda-new");
            exit;
        }

        if (Agendamento::conflito($pdo, $idUsuario, $inicio, $fim, null)) {
            $_SESSION['erro'] = "Conflito: existe agendamento ou bloqueio nesse horário.";
            header("Location: /financas/public/?url=agenda-new");
            exit;
        }

        Agendamento::create($pdo, $idUsuario, $d);
        $_SESSION['sucesso'] = "Agendamento criado!";
        header("Location: /financas/public/?url=agenda");
        exit;
    }

    public static function update(PDO $pdo)
    {
        $idUsuario = usuarioId();
        $id = (int)($_POST['id'] ?? 0);

        $inicio = self::dtLocalToDb($_POST['data_inicio'] ?? '');
        $fim    = self::dtLocalToDb($_POST['data_fim'] ?? '');

        $d = [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'cliente_nome' => trim($_POST['cliente_nome'] ?? ''),
            'cliente_email' => trim($_POST['cliente_email'] ?? ''),
            'cliente_telefone' => trim($_POST['cliente_telefone'] ?? ''),
            'local' => trim($_POST['local'] ?? ''),
            'data_inicio' => $inicio,
            'data_fim' => $fim,
            'status' => $_POST['status'] ?? 'marcado',
            'notificar_minutos' => (int)($_POST['notificar_minutos'] ?? 60),
        ];

        if (!$id || $d['titulo'] === '' || $inicio === '' || $fim === '') {
            $_SESSION['erro'] = "Preencha título, início e fim.";
            header("Location: /financas/public/?url=agenda");
            exit;
        }

        if (strtotime($fim) <= strtotime($inicio)) {
            $_SESSION['erro'] = "A data/hora final deve ser maior que a inicial.";
            header("Location: /financas/public/?url=agenda-edit&id=" . $id);
            exit;
        }

        if (Agendamento::conflito($pdo, $idUsuario, $inicio, $fim, $id)) {
            $_SESSION['erro'] = "Conflito: existe agendamento ou bloqueio nesse horário.";
            header("Location: /financas/public/?url=agenda-edit&id=" . $id);
            exit;
        }

        Agendamento::update($pdo, $idUsuario, $id, $d);
        $_SESSION['sucesso'] = "Agendamento atualizado!";
        header("Location: /financas/public/?url=agenda");
        exit;
    }

    public static function delete(PDO $pdo)
    {
        $idUsuario = usuarioId();
        $id = (int)($_GET['id'] ?? 0);

        if ($id) {
            Agendamento::delete($pdo, $idUsuario, $id);
            $_SESSION['sucesso'] = "Agendamento removido.";
        }

        header("Location: /financas/public/?url=agenda");
        exit;
    }

    public static function status(PDO $pdo)
    {
        $idUsuario = usuarioId();
        $id = (int)($_GET['id'] ?? 0);
        $st = $_GET['st'] ?? '';

        $permitidos = ['marcado','confirmado','concluido','faltou','cancelado'];
        if (!$id || !in_array($st, $permitidos, true)) {
            header("Location: /financas/public/?url=agenda");
            exit;
        }

        Agendamento::updateStatus($pdo, $idUsuario, $id, $st);
        $_SESSION['sucesso'] = "Status atualizado!";
        header("Location: /financas/public/?url=agenda");
        exit;
    }

    // ======= BLOQUEIOS =======

    public static function bloqueioStore(PDO $pdo)
    {
        $idUsuario = usuarioId();
        $tipo = $_POST['tipo'] ?? 'periodo';
        $titulo = trim($_POST['titulo'] ?? '');

        if ($titulo === '') {
            $_SESSION['erro'] = "Informe um título para o bloqueio.";
            header("Location: /financas/public/?url=agenda");
            exit;
        }

        if ($tipo === 'semanal') {
            $dia = (int)($_POST['dia_semana'] ?? -1);
            $hIni = trim($_POST['hora_inicio'] ?? '');
            $hFim = trim($_POST['hora_fim'] ?? '');

            if ($dia < 0 || $dia > 6 || $hIni === '' || $hFim === '') {
                $_SESSION['erro'] = "Preencha dia da semana e horários.";
                header("Location: /financas/public/?url=agenda");
                exit;
            }

            if (strtotime("1970-01-01 $hFim") <= strtotime("1970-01-01 $hIni")) {
                $_SESSION['erro'] = "Hora final deve ser maior que a inicial.";
                header("Location: /financas/public/?url=agenda");
                exit;
            }

            Agendamento::createBloqueioSemanal($pdo, $idUsuario, $titulo, $dia, $hIni, $hFim);
            $_SESSION['sucesso'] = "Bloqueio semanal criado!";
        } else {
            $inicio = self::dtLocalToDb($_POST['data_inicio'] ?? '');
            $fim    = self::dtLocalToDb($_POST['data_fim'] ?? '');

            if ($inicio === '' || $fim === '') {
                $_SESSION['erro'] = "Preencha início e fim do bloqueio.";
                header("Location: /financas/public/?url=agenda");
                exit;
            }

            if (strtotime($fim) <= strtotime($inicio)) {
                $_SESSION['erro'] = "A data/hora final deve ser maior que a inicial.";
                header("Location: /financas/public/?url=agenda");
                exit;
            }

            Agendamento::createBloqueioPeriodo($pdo, $idUsuario, $titulo, $inicio, $fim);
            $_SESSION['sucesso'] = "Bloqueio por período criado!";
        }

        header("Location: /financas/public/?url=agenda");
        exit;
    }

    public static function bloqueioDelete(PDO $pdo)
    {
        $idUsuario = usuarioId();
        $id = (int)($_GET['id'] ?? 0);

        if ($id) {
            Agendamento::deleteBloqueio($pdo, $idUsuario, $id);
            $_SESSION['sucesso'] = "Bloqueio removido.";
        }

        header("Location: /financas/public/?url=agenda");
        exit;
    }

    // ======= PÚBLICO (AGENDAR COMIGO) =======

    public static function publicoForm(PDO $pdo)
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        $token = trim($_GET['token'] ?? '');
        if ($token === '') {
            http_response_code(404);
            echo "Link inválido.";
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE agenda_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo "Link inválido.";
            exit;
        }

        $titulo = "Agendar";
        require __DIR__ . '/../views/agenda/public_agendar.php';
        exit;
    }

    public static function publicoSlots(PDO $pdo)
    {
        header("Content-Type: application/json; charset=utf-8");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        $token = trim($_GET['token'] ?? '');
        $date  = trim($_GET['date'] ?? '');
        $dur   = (int)($_GET['dur'] ?? 60);

        if ($token === '' || $date === '') {
            echo json_encode(['ok' => false, 'slots' => []]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE agenda_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $idUsuario = (int)($stmt->fetchColumn() ?: 0);

        if (!$idUsuario) {
            echo json_encode(['ok' => false, 'slots' => []]);
            exit;
        }

        // valida date YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode(['ok' => false, 'slots' => []]);
            exit;
        }

        $slots = Agendamento::slotsDisponiveis($pdo, $idUsuario, $date, 30, max(15, $dur));
        echo json_encode(['ok' => true, 'slots' => $slots]);
        exit;
    }

    public static function publicoStore(PDO $pdo)
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        $token = trim($_POST['token'] ?? '');
        $date  = trim($_POST['data'] ?? '');
        $time  = trim($_POST['hora'] ?? '');
        $dur   = (int)($_POST['duracao'] ?? 60);

        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fone  = trim($_POST['telefone'] ?? '');
        $obs   = trim($_POST['observacao'] ?? '');

        if ($token === '' || $date === '' || $time === '' || $nome === '') {
            $_SESSION['erro_publico'] = "Preencha nome, data e horário.";
            header("Location: /financas/public/?url=agendar&token=" . urlencode($token));
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE agenda_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            http_response_code(404);
            echo "Link inválido.";
            exit;
        }
        $idUsuario = (int)$user['id'];

        // monta datetime
        $ini = $date . " " . $time . ":00";
        $fim = date('Y-m-d H:i:s', strtotime($ini) + (max(15, $dur) * 60));

        if (Agendamento::conflito($pdo, $idUsuario, $ini, $fim, null)) {
            $_SESSION['erro_publico'] = "Esse horário acabou de ficar indisponível. Escolha outro.";
            header("Location: /financas/public/?url=agendar&token=" . urlencode($token));
            exit;
        }

        $titulo = "Agendamento";
        if ($obs !== '') $titulo = "Agendamento - " . mb_substr($obs, 0, 40);

        Agendamento::create($pdo, $idUsuario, [
            'titulo' => $titulo,
            'descricao' => $obs,
            'cliente_nome' => $nome,
            'cliente_email' => $email,
            'cliente_telefone' => $fone,
            'local' => null,
            'data_inicio' => $ini,
            'data_fim' => $fim,
            'status' => 'marcado',
            'notificar_minutos' => 60
        ]);

        $_SESSION['sucesso_publico'] = "Agendamento realizado! Você receberá confirmação se o e-mail estiver correto.";
        header("Location: /financas/public/?url=agendar&token=" . urlencode($token));
        exit;
    }

    // ======= NOTIFICAÇÕES (CRON via URL) =======
    // Chame: /financas/public/?url=agenda-notificacoes&key=SUA_CHAVE
    public static function notificacoes(PDO $pdo)
    {
        // proteção simples por chave (defina APP_CRON_KEY no seu config)
        $key = $_GET['key'] ?? '';
        if (defined('APP_CRON_KEY')) {
            if ($key !== APP_CRON_KEY) {
                http_response_code(403);
                echo "Forbidden";
                exit;
            }
        } else {
            // se não definiu chave, só permite se estiver logado como admin
            if (empty($_SESSION['usuario']['is_admin'])) {
                http_response_code(403);
                echo "Forbidden";
                exit;
            }
        }

        $stmt = $pdo->query("
            SELECT a.*, u.email as dono_email, u.nome as dono_nome
              FROM agendamentos a
              JOIN usuarios u ON u.id = a.id_usuario
             WHERE a.notificado_em IS NULL
               AND a.status IN ('marcado','confirmado')
               AND a.data_inicio >= NOW()
               AND a.data_inicio <= DATE_ADD(NOW(), INTERVAL 1 DAY)
             ORDER BY a.data_inicio ASC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $enviados = 0;

        foreach ($rows as $a) {
            $inicioTs = strtotime($a['data_inicio']);
            $diffMin = (int)round(($inicioTs - time()) / 60);

            $min = (int)($a['notificar_minutos'] ?? 60);
            if ($diffMin < 0) continue;
            if ($diffMin > $min) continue;

            $toCliente = trim((string)($a['cliente_email'] ?? ''));
            $toDono    = trim((string)($a['dono_email'] ?? ''));

            $assunto = "Lembrete de agendamento";
            $msg = "Olá!\n\n";
            $msg .= "Lembrete do seu agendamento:\n";
            $msg .= "Data/Hora: " . date('d/m/Y H:i', strtotime($a['data_inicio'])) . "\n";
            $msg .= "Até: " . date('d/m/Y H:i', strtotime($a['data_fim'])) . "\n";
            $msg .= "Status: " . $a['status'] . "\n";
            if (!empty($a['descricao'])) $msg .= "Obs: " . $a['descricao'] . "\n";
            $msg .= "\n— App Finanças\n";

            $headers = "Content-Type: text/plain; charset=UTF-8\r\n";

            $ok = false;

            // envia para cliente se tiver e-mail
            if ($toCliente !== '' && filter_var($toCliente, FILTER_VALIDATE_EMAIL)) {
                @mail($toCliente, $assunto, $msg, $headers);
                $ok = true; // não dá pra garantir retorno real em todo host, mas seguimos
            }

            // envia para dono também
            if ($toDono !== '' && filter_var($toDono, FILTER_VALIDATE_EMAIL)) {
                @mail($toDono, "[CÓPIA] " . $assunto, $msg, $headers);
                $ok = true;
            }

            if ($ok) {
                $up = $pdo->prepare("UPDATE agendamentos SET notificado_em = NOW() WHERE id = ?");
                $up->execute([(int)$a['id']]);
                $enviados++;
            }
        }

        echo "OK - notificações processadas: " . $enviados;
        exit;
    }
}
