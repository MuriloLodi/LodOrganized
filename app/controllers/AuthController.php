<?php

class AuthController
{
    public static function login($pdo)
    {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (!$email || !$senha) {
            $_SESSION['erro'] = "Informe e-mail e senha.";
            header("Location: /financas/public/?url=login");
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            $_SESSION['erro'] = "E-mail ou senha inválidos.";
            header("Location: /financas/public/?url=login");
            exit;
        }

        // Login OK
        $_SESSION['usuario'] = [
            'id'    => $usuario['id'],
            'nome'  => $usuario['nome'],
            'email' => $usuario['email'],
            'tipo'  => $usuario['tipo']
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
    $nome   = trim($_POST['nome'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $senha  = $_POST['senha'] ?? '';
    $confirm = $_POST['senha_confirm'] ?? '';

    if (!$nome || !$email || !$senha) {
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
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $_SESSION['erro'] = "Este e-mail já está cadastrado.";
        header("Location: /financas/public/?url=register");
        exit;
    }

    // Cria usuário
    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nome, email, senha, tipo)
        VALUES (?, ?, ?, 'pessoal')
    ");

    $stmt->execute([$nome, $email, $hash]);

    // Login automático após cadastro
    $_SESSION['usuario'] = [
        'id'    => $pdo->lastInsertId(),
        'nome'  => $nome,
        'email' => $email,
        'tipo'  => 'pessoal'
    ];

    header("Location: /financas/public/?url=dashboard");
    exit;
}

}
