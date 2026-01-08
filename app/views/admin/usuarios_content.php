<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="mb-0">Usuários</h1>
    <div class="text-muted">Gerencie usuários do sistema</div>
  </div>
  <a class="btn btn-outline-secondary" href="/financas/public/?url=admin">Voltar</a>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div>
<?php endif; ?>

<?php if (!empty($_SESSION['sucesso'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_SESSION['sucesso']); unset($_SESSION['sucesso']); ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="GET" action="/financas/public/">
  <input type="hidden" name="url" value="admin-usuarios">
  <div class="col-md-6">
    <input class="form-control" name="q" placeholder="Buscar por nome/email..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
  </div>
  <div class="col-md-2">
    <button class="btn btn-primary w-100">Buscar</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table align-middle">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>E-mail</th>
        <th class="text-center">Admin</th>
        <th class="text-center">Status</th>
        <th>Último login</th>
        <th class="text-end">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (($usuarios ?? []) as $u): ?>
        <tr>
          <td class="text-muted"><?= (int)$u['id'] ?></td>
          <td><?= htmlspecialchars($u['nome'] ?? '') ?></td>
          <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
          <td class="text-center"><?= !empty($u['is_admin']) ? '✅' : '—' ?></td>

          <td class="text-center">
            <?php if (!empty($u['is_blocked'])): ?>
              <span class="badge bg-danger">Bloqueado</span>
            <?php else: ?>
              <span class="badge bg-success">Ativo</span>
            <?php endif; ?>
          </td>

          <td class="small text-muted">
            <?= !empty($u['last_login_at']) ? htmlspecialchars($u['last_login_at']) : '—' ?>
            <?php if (!empty($u['last_login_ip'])): ?>
              <div><?= htmlspecialchars($u['last_login_ip']) ?></div>
            <?php endif; ?>
          </td>

          <td class="text-end">
            <?php if (empty($u['is_admin'])): ?>
              <a class="btn btn-sm btn-outline-danger"
                 href="/financas/public/?url=admin-usuario-toggle-block&id=<?= (int)$u['id'] ?>"
                 onclick="return confirm('Confirmar ação?')">
                <?= !empty($u['is_blocked']) ? 'Desbloquear' : 'Bloquear' ?>
              </a>

              <a class="btn btn-sm btn-outline-dark"
                 href="/financas/public/?url=admin-usuario-reset-senha&id=<?= (int)$u['id'] ?>"
                 onclick="return confirm('Resetar senha deste usuário?')">
                 Resetar senha
              </a>
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
