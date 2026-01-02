<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar conta • Finanças</title>
    <link rel="stylesheet" href="/financas/public/css/app.css">
</head>
<body>

<div class="login-container">
    <form method="POST" action="/financas/public/?url=register-store" class="login-card">

        <h2>Criar conta</h2>

        <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alert">
                <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
            </div>
        <?php endif; ?>

        <input type="text" name="nome" placeholder="Nome completo" required>
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <input type="password" name="senha_confirm" placeholder="Confirmar senha" required>

        <button type="submit">Cadastrar</button>

        <p class="small">
            Já tem conta?
            <a href="/financas/public/?url=login">Entrar</a>
        </p>

    </form>
</div>

</body>
</html>
