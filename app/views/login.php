<?php
// app/views/login.php
if (session_status() === PHP_SESSION_NONE) session_start();

$erro = $_SESSION['erro'] ?? '';
unset($_SESSION['erro']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Entrar • Finanças</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="/financas/public/css/login.css" />
</head>

<body>
  <div class="card">
    <div class="hero">
      <div class="hero-inner">
        <h2>Controle suas finanças com segurança</h2>
        <h3>Dashboard, relatórios e organização em minutos</h3>
      </div>
    </div>

    <form method="POST" action="/financas/public/?url=login-auth" autocomplete="on">
      <div class="brand">
        <div class="brand-badge">💰</div>
        <div class="brand-text">
          <div class="brand-title">LodFinance</div>
          <div class="brand-subtitle">Acesse sua conta</div>
        </div>
      </div>

      <?php if (!empty($erro)): ?>
        <div class="alert">
          <?= htmlspecialchars($erro) ?>
        </div>
      <?php endif; ?>

      <!-- Socials (opcional / desativado por enquanto) -->
      <div class="socials">
        <button class="social-btn" type="button" aria-disabled="true" title="Em breve">
          <span class="social-ico">G</span>
          <p><span class="extra-text">Entrar com</span> Google</p>
        </button>

        <button class="social-btn" type="button" aria-disabled="true" title="Em breve">
          <span class="social-ico">f</span>
          <p><span class="extra-text">Entrar com</span> Facebook</p>
        </button>
      </div>

      <span class="or"></span>

      <label class="field">
        <span class="label">E-mail</span>
        <input
          type="email"
          name="email"
          placeholder="seuemail@exemplo.com"
          required
          autofocus
        />
      </label>

      <label class="field">
        <span class="label">Senha</span>
        <div class="pass-wrap">
          <input
            type="password"
            name="senha"
            id="senha"
            placeholder="••••••••"
            required
          />
          <button type="button" class="pass-toggle" onclick="toggleSenha()" aria-label="Mostrar/ocultar senha">
            👁️
          </button>
        </div>
      </label>

      <button type="submit">Entrar</button>

      <div class="bottom">
        <a href="/financas/public/?url=register">Criar conta</a>
      </div>
    </form>
  </div>

  <script>
    function toggleSenha() {
      const el = document.getElementById('senha');
      el.type = el.type === 'password' ? 'text' : 'password';
    }
  </script>
</body>
</html>
