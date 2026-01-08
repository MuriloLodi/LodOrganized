<?php
require_once __DIR__ . '/../helpers/helpers.php';

class AdminController
{
    private static function adminId(): int
    {
        return (int)($_SESSION['usuario']['id'] ?? 0);
    }

    public static function index(PDO $pdo)
    {
        $titulo = "Admin";
        $view = __DIR__ . '/../views/admin/index_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function usuarios(PDO $pdo)
    {
        $q = trim($_GET['q'] ?? '');

        if ($q !== '') {
            $stmt = $pdo->prepare("
                SELECT id, nome, email, is_admin, is_blocked, created_at, last_login_at, last_login_ip
                  FROM usuarios
                 WHERE nome LIKE ? OR email LIKE ?
                 ORDER BY id DESC
                 LIMIT 200
            ");
            $like = "%{$q}%";
            $stmt->execute([$like, $like]);
        } else {
            $stmt = $pdo->query("
                SELECT id, nome, email, is_admin, is_blocked, created_at, last_login_at, last_login_ip
                  FROM usuarios
                 ORDER BY id DESC
                 LIMIT 200
            ");
        }

        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $titulo = "Admin • Usuários";
        $view = __DIR__ . '/../views/admin/usuarios_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function toggleBlock(PDO $pdo)
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header("Location: /financas/public/?url=admin-usuarios");
            exit;
        }

        // impede bloquear o próprio admin logado
        if ($id === self::adminId()) {
            $_SESSION['erro'] = "Você não pode bloquear sua própria conta.";
            header("Location: /financas/public/?url=admin-usuarios");
            exit;
        }

        $stmt = $pdo->prepare("SELECT is_blocked, is_admin FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$u) {
            $_SESSION['erro'] = "Usuário não encontrado.";
            header("Location: /financas/public/?url=admin-usuarios");
            exit;
        }

        // opcional: impedir bloquear outro admin
        if (!empty($u['is_admin'])) {
            $_SESSION['erro'] = "Você não pode bloquear um administrador.";
            header("Location: /financas/public/?url=admin-usuarios");
            exit;
        }

        $novo = empty($u['is_blocked']) ? 1 : 0;
        $upd = $pdo->prepare("UPDATE usuarios SET is_blocked = ? WHERE id = ?");
        $upd->execute([$novo, $id]);

        $_SESSION['sucesso'] = $novo ? "Usuário bloqueado." : "Usuário desbloqueado.";
        header("Location: /financas/public/?url=admin-usuarios");
        exit;
    }

    /**
     * RESET de senha (não existe “ver senha”)
     * Gera uma senha temporária e mostra 1 vez.
     */
    public static function resetSenha(PDO $pdo)
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header("Location: /financas/public/?url=admin-usuarios");
            exit;
        }

        if ($id === self::adminId()) {
            $_SESSION['erro'] = "Você não pode resetar sua própria senha por aqui.";
            header("Location: /financas/public/?url=admin-usuarios");
            exit;
        }

        // senha temporária simples (você pode mudar)
        $temp = substr(bin2hex(random_bytes(8)), 0, 12); // 12 chars
        $hash = password_hash($temp, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);

        $_SESSION['sucesso'] = "Senha resetada. Senha temporária: {$temp}";
        header("Location: /financas/public/?url=admin-usuarios");
        exit;
    }

    public static function metricas(PDO $pdo)
    {
        $totalUsuarios = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

        $ativos7d = 0;
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE last_login_at >= (NOW() - INTERVAL 7 DAY)");
        $ativos7d = (int)$stmt->fetchColumn();

        // Se suas tabelas existirem (propostas, clientes, servicos), pega também:
        $totalPropostas = self::safeCount($pdo, "propostas");
        $totalClientes  = self::safeCount($pdo, "clientes");
        $totalServicos  = self::safeCount($pdo, "servicos");

        $metricas = [
            'totalUsuarios' => $totalUsuarios,
            'ativos7d' => $ativos7d,
            'totalPropostas' => $totalPropostas,
            'totalClientes' => $totalClientes,
            'totalServicos' => $totalServicos,
        ];

        $titulo = "Admin • Métricas";
        $view = __DIR__ . '/../views/admin/metricas_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    private static function safeCount(PDO $pdo, string $table): int
    {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}
