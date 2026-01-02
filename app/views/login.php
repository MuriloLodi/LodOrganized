<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login • Finanças</title>
    <link rel="stylesheet" href="/financas/public/css/app.css">
</head>
<body>

<div class="login-container">
    <form method="POST" action="/financas/public/?url=login-auth" class="login-card">

        <h2>Entrar</h2>

        <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert">
                <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
            </div>
        <?php endif; ?>

        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha" required>

        <button type="submit">Entrar</button>

        <p class="small">
            Ainda não tem conta?
            <a href="/financas/public/?url=register">Criar conta</a>
        </p>

    </form>
</div>

</body>
</html>
