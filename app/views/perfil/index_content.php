<?php
$u = $_SESSION['usuario'] ?? [];
$foto = avatarUrl($u);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<style>
  .card-soft{
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 16px;
    box-shadow: 0 10px 26px rgba(0,0,0,.06);
    background:#fff;
  }
  .avatar-wrap{
    width: 88px; height: 88px;
    border-radius: 18px;
    overflow:hidden;
    border: 1px solid rgba(0,0,0,.10);
    background: rgba(13,110,253,.08);
    display:flex; align-items:center; justify-content:center;
    box-shadow: 0 10px 26px rgba(0,0,0,.06);
    flex: 0 0 auto;
  }
  .avatar-wrap img{
    width:100%; height:100%;
    object-fit: cover;
    display:block;
  }
  .avatar-fallback{
    font-weight: 800;
    font-size: 1.4rem;
    color:#0d6efd;
  }
  .hint{
    color:#6c757d;
    font-size:.85rem;
  }
  .sec-box{
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 14px;
    background: rgba(255,255,255,.8);
    padding: 14px;
  }
  .sec-title{
    font-weight: 700;
    margin-bottom: 10px;
    display:flex; align-items:center; gap:.5rem;
  }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
  <div>
    <h1 class="mb-1">Perfil</h1>
    <div class="text-muted">Atualize seus dados e sua foto</div>
  </div>

  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-secondary" href="/financas/public/?url=dashboard">
      <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= h($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['sucesso'])): ?>
  <div class="alert alert-success">
    <?= h($_SESSION['sucesso']); unset($_SESSION['sucesso']); ?>
  </div>
<?php endif; ?>

<div class="row g-3">
  <!-- FOTO -->
  <div class="col-lg-4">
    <div class="card-soft p-3 h-100">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="fw-semibold">Foto do perfil</div>
        <span class="hint">JPG/PNG/WEBP • até 3MB</span>
      </div>

      <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="avatar-wrap">
          <?php if (!empty($foto)): ?>
            <img src="<?= h($foto) ?>" alt="Avatar">
          <?php else: ?>
            <div class="avatar-fallback">
              <?= h(iniciais($u['nome'] ?? 'Usuário')) ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="flex-grow-1" style="min-width: 240px;">
          <form method="POST"
                action="/financas/public/?url=perfil-avatar"
                enctype="multipart/form-data"
                class="d-flex flex-column gap-2">
            <input type="file"
                   name="avatar"
                   accept="image/png,image/jpeg,image/webp"
                   class="form-control form-control-sm"
                   required>
            <button class="btn btn-primary btn-sm w-100">
              <i class="bi bi-upload me-1"></i> Atualizar foto
            </button>
          </form>

          <?php if (!empty($foto)): ?>
            <form method="POST"
                  action="/financas/public/?url=perfil-avatar-delete"
                  class="mt-2"
                  onsubmit="return confirm('Remover foto do perfil?')">
              <button class="btn btn-outline-danger btn-sm w-100">
                <i class="bi bi-trash me-1"></i> Remover foto
              </button>
            </form>
          <?php endif; ?>

          <div class="hint mt-2">
            Dica: use uma imagem quadrada para ficar mais bonito.
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- DADOS -->
  <div class="col-lg-8">
    <div class="card-soft p-3">
      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div class="fw-semibold">Seus dados</div>
        <div class="hint">Alterações sensíveis exigem senha atual</div>
      </div>

      <form method="POST" action="/financas/public/?url=perfil-update">
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Nome/Empresa</label>
            <input class="form-control" name="nome" required value="<?= h($u['nome'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">E-mail</label>
            <input class="form-control" type="email" name="email" required value="<?= h($u['email'] ?? '') ?>">
          </div>

          <div class="col-12 mt-2">
            <div class="sec-box">
              <div class="sec-title">
                <i class="bi bi-shield-lock"></i> Segurança
              </div>

              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Senha atual</label>
                  <input class="form-control" type="password" name="senha_atual" placeholder="••••••••">
                  <div class="hint mt-1">Obrigatória para trocar e-mail e/ou senha.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Nova senha (opcional)</label>
                  <input class="form-control" type="password" name="nova_senha" placeholder="mín. 6 caracteres">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Confirmar nova senha</label>
                  <input class="form-control" type="password" name="nova_senha_confirm" placeholder="repita a nova senha">
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3 flex-wrap">
          <button class="btn btn-primary">
            <i class="bi bi-check2-circle me-1"></i> Salvar alterações
          </button>

          <a class="btn btn-outline-secondary" href="/financas/public/?url=dashboard">
            Cancelar
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
