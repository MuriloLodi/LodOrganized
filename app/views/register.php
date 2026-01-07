<?php
// app/views/register.php
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
  <title>Criar conta • Finanças</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />

  <!-- Reutiliza o MESMO CSS do login -->
  <link rel="stylesheet" href="/financas/public/css/login.css" />
</head>

<body>
  <div class="card">
    <div class="hero">
      <div class="hero-inner">
        <h2>Crie sua conta em 1 minuto</h2>
        <h3>Comece a organizar receitas, despesas e relatórios</h3>
      </div>
    </div>

    <form method="POST" action="/financas/public/?url=register-store" autocomplete="on">
      <div class="brand">
        <div class="brand-badge">✨</div>
        <div class="brand-text">
          <div class="brand-title">Finanças</div>
          <div class="brand-subtitle">Criar conta</div>
        </div>
      </div>

      <?php if (!empty($erro)): ?>
        <div class="alert">
          <?= htmlspecialchars($erro) ?>
        </div>
      <?php endif; ?>

      <label class="field">
        <span class="label">Nome completo</span>
        <input
          type="text"
          name="nome"
          placeholder="Ex: Murilo Lodi"
          required
        />
      </label>

      <label class="field">
        <span class="label">E-mail</span>
        <input
          type="email"
          name="email"
          placeholder="seuemail@exemplo.com"
          required
        />
      </label>

      <label class="field">
        <span class="label">Senha</span>
        <div class="pass-wrap">
          <input
            type="password"
            name="senha"
            id="senha"
            placeholder="mínimo 6 caracteres"
            required
          />
          <button type="button" class="pass-toggle" onclick="toggleSenha('senha')" aria-label="Mostrar/ocultar senha">
            👁️
          </button>
        </div>
      </label>

      <label class="field">
        <span class="label">Confirmar senha</span>
        <div class="pass-wrap">
          <input
            type="password"
            name="senha_confirm"
            id="senha_confirm"
            placeholder="repita a senha"
            required
          />
          <button type="button" class="pass-toggle" onclick="toggleSenha('senha_confirm')" aria-label="Mostrar/ocultar senha">
            👁️
          </button>
        </div>
      </label>

      <button type="submit">Criar conta</button>

      <div class="bottom">
        <a href="/financas/public/?url=login">Já tenho conta</a>
      </div>
    </form>
  </div>

  <script>
    function toggleSenha(id) {
      const el = document.getElementById(id);
      el.type = el.type === 'password' ? 'text' : 'password';
    }
  </script>
</body>
</html>
