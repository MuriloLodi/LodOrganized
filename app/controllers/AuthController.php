<?php

class AuthController
{
    public static function login($pdo)
    {
        $email = trim($_POST['email'] ?? '');
        $senha = (string)($_POST['senha'] ?? '');

        if ($email === '' || $senha === '') {
            $_SESSION['erro'] = "Informe e-mail e senha.";
            header("Location: /financas/public/?url=login");
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || !password_verify($senha, $usuario['senha'] ?? '')) {
            $_SESSION['erro'] = "E-mail ou senha inválidos.";
            header("Location: /financas/public/?url=login");
            exit;
        }

        // ✅ bloqueio pelo admin
        if (!empty($usuario['is_blocked'])) {
            $_SESSION['erro'] = "Sua conta está bloqueada. Fale com o suporte.";
            header("Location: /financas/public/?url=login");
            exit;
        }

        // (opcional) atualiza last_login
        try {
            $up = $pdo->prepare("UPDATE usuarios SET last_login_at = NOW() WHERE id = ?");
            $up->execute([(int)$usuario['id']]);
        } catch (Exception $e) {
            // não trava login por isso
        }

        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id'         => (int)$usuario['id'],
            'nome'       => (string)($usuario['nome'] ?? ''),
            'email'      => (string)($usuario['email'] ?? ''),
            'tipo'       => (string)($usuario['tipo'] ?? 'pessoal'),
            'avatar'     => $usuario['avatar'] ?? null,

            // ✅ admin (pra sidebar + guard)
            'is_admin'   => (int)($usuario['is_admin'] ?? 0),

            // ✅ bloqueio (caso queira usar em telas)
            'is_blocked' => (int)($usuario['is_blocked'] ?? 0),
        ];

        header("Location: /financas/public/?url=dashboard");
        exit;
    }

    public static function logout()
    {
        session_destroy();
        header("Location: /financas/public/?url=login");
        exit;
    }

    public static function register($pdo)
    {
        $nome    = trim($_POST['nome'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $senha   = (string)($_POST['senha'] ?? '');
        $confirm = (string)($_POST['senha_confirm'] ?? '');

        if ($nome === '' || $email === '' || $senha === '') {
            $_SESSION['erro'] = "Preencha todos os campos.";
            header("Location: /financas/public/?url=register");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['erro'] = "E-mail inválido.";
            header("Location: /financas/public/?url=register");
            exit;
        }

        if ($senha !== $confirm) {
            $_SESSION['erro'] = "As senhas não conferem.";
            header("Location: /financas/public/?url=register");
            exit;
        }

        if (strlen($senha) < 6) {
            $_SESSION['erro'] = "A senha deve ter no mínimo 6 caracteres.";
            header("Location: /financas/public/?url=register");
            exit;
        }

        // Verifica se e-mail já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['erro'] = "Este e-mail já está cadastrado.";
            header("Location: /financas/public/?url=register");
            exit;
        }

        $hash = password_hash($senha, PASSWORD_DEFAULT);

        // ✅ já cria com is_admin/is_blocked controlados pelo banco (você altera manualmente)
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, tipo, is_admin, is_blocked, last_login_at)
            VALUES (?, ?, ?, 'pessoal', 0, 0, NOW())
        ");
        $stmt->execute([$nome, $email, $hash]);

        $id = (int)$pdo->lastInsertId();

        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id'         => $id,
            'nome'       => $nome,
            'email'      => $email,
            'tipo'       => 'pessoal',
            'avatar'     => null,
            'is_admin'   => 0,
            'is_blocked' => 0,
        ];

        header("Location: /financas/public/?url=dashboard");
        exit;
    }
}
