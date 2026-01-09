<style>
  /* mantém seu padrão card-soft/chip e só deixa a tabela mais bonita */
  .tbl-users{
    --b: rgba(0,0,0,.08);
    --b2: rgba(0,0,0,.06);
  }

  .tbl-users .table{
    margin: 0;
  }

  .tbl-users thead th{
    background: #f8f9fa;
    border-bottom: 1px solid var(--b);
    font-weight: 700;
    color: #495057;
    padding: 12px 14px;
    white-space: nowrap;
  }

  .tbl-users tbody td{
    padding: 14px 14px;
    border-top: 1px solid var(--b2);
    vertical-align: middle;
  }

  .tbl-users tbody tr:hover{
    background: rgba(13,110,253,.03);
  }

  .user-cell{
    display:flex;
    align-items:center;
    gap: 10px;
    min-width: 0;
  }

  .avatar-mini{
    width: 36px;
    height: 36px;
    border-radius: 999px;
    display:grid;
    place-items:center;
    font-weight: 800;
    font-size: .9rem;
    background: rgba(13,110,253,.10);
    color: #0d6efd;
    border: 1px solid rgba(13,110,253,.18);
    flex: 0 0 auto;
  }

  .user-text{ min-width:0; }
  .user-name{
    font-weight: 700;
    line-height: 1.1;
  }
  .user-sub{
    color:#6c757d;
    font-size: .85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 320px;
  }

  .badge-soft{
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    padding:.42rem .65rem;
    border-radius: 999px;
    border: 1px solid rgba(0,0,0,.08);
    font-weight: 700;
    font-size: .80rem;
  }
  .badge-soft.success{
    background: rgba(25,135,84,.10);
    color:#198754;
    border-color: rgba(25,135,84,.18);
  }
  .badge-soft.danger{
    background: rgba(220,53,69,.10);
    color:#dc3545;
    border-color: rgba(220,53,69,.18);
  }
  .badge-soft.dark{
    background: rgba(33,37,41,.08);
    color:#212529;
    border-color: rgba(33,37,41,.15);
  }

  .actions{
    display:flex;
    justify-content:flex-end;
    gap: 8px;
    flex-wrap: wrap;
  }

  .login-cell{
    color:#6c757d;
    font-size: .88rem;
    line-height: 1.15;
  }
  .login-cell .ip{
    font-size: .82rem;
    color:#8a94a6;
  }

  @media (max-width: 576px){
    .user-sub{ max-width: 220px; }
    .actions{ justify-content: flex-start; }
  }
</style>

<!-- LISTA (substitua só esse miolo da tabela) -->
<div class="card-soft tbl-users">
  <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="fw-semibold">Lista de usuários</div>
    <div class="text-muted small">Ações bloqueio/reset não aparecem para admins</div>
  </div>

  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th style="width:70px;">ID</th>
          <th>Usuário</th>
          <th class="d-none d-lg-table-cell">E-mail</th>
          <th class="text-center" style="width:90px;">Admin</th>
          <th class="text-center" style="width:120px;">Status</th>
          <th class="d-none d-md-table-cell" style="width:220px;">Último login</th>
          <th class="text-end" style="width:260px;">Ações</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($usuarios)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-5">
              <i class="bi bi-people fs-2 d-block mb-2"></i>
              Nenhum usuário encontrado.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach (($usuarios ?? []) as $u): ?>
            <?php
              $isAdmin   = !empty($u['is_admin']);
              $isBlocked = !empty($u['is_blocked']);

              $stLabel = $isBlocked ? 'Bloqueado' : 'Ativo';
              $stClass = $isBlocked ? 'danger' : 'success';

              $inic = strtoupper(substr(trim($u['nome'] ?? 'U'), 0, 1));
            ?>
            <tr>
              <td class="text-muted"><?= (int)$u['id'] ?></td>

              <td>
                <div class="user-cell">
                  <div class="avatar-mini" title="<?= htmlspecialchars($u['nome'] ?? '') ?>">
                    <?= htmlspecialchars($inic) ?>
                  </div>

                  <div class="user-text">
                    <div class="user-name td-trunc" title="<?= htmlspecialchars($u['nome'] ?? '') ?>">
                      <?= htmlspecialchars($u['nome'] ?? '') ?>
                    </div>

                    <!-- no mobile, mostra e-mail embaixo -->
                    <div class="user-sub d-lg-none" title="<?= htmlspecialchars($u['email'] ?? '') ?>">
                      <?= htmlspecialchars($u['email'] ?? '') ?>
                    </div>
                  </div>
                </div>
              </td>

              <td class="d-none d-lg-table-cell">
                <div class="td-trunc" title="<?= htmlspecialchars($u['email'] ?? '') ?>">
                  <?= htmlspecialchars($u['email'] ?? '') ?>
                </div>
              </td>

              <td class="text-center">
                <?php if ($isAdmin): ?>
                  <span class="badge-soft dark"><i class="bi bi-shield-lock"></i> Sim</span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>

              <td class="text-center">
                <span class="badge-soft <?= $stClass ?>">
                  <i class="bi <?= $isBlocked ? 'bi-slash-circle' : 'bi-check-circle' ?>"></i>
                  <?= $stLabel ?>
                </span>
              </td>

              <td class="d-none d-md-table-cell">
                <div class="login-cell">
                  <?php if (!empty($u['last_login_at'])): ?>
                    <div><?= htmlspecialchars($u['last_login_at']) ?></div>
                  <?php else: ?>
                    <div>—</div>
                  <?php endif; ?>

                  <?php if (!empty($u['last_login_ip'])): ?>
                    <div class="ip"><?= htmlspecialchars($u['last_login_ip']) ?></div>
                  <?php endif; ?>
                </div>
              </td>

              <td class="text-end">
                <?php if (!$isAdmin): ?>
                  <div class="actions">
                    <a class="btn btn-sm <?= $isBlocked ? 'btn-outline-success' : 'btn-outline-danger' ?>"
                       href="/financas/public/?url=admin-usuario-toggle-block&id=<?= (int)$u['id'] ?>"
                       onclick="return confirm('Confirmar ação?')">
                      <i class="bi <?= $isBlocked ? 'bi-unlock' : 'bi-slash-circle' ?> me-1"></i>
                      <?= $isBlocked ? 'Desbloquear' : 'Bloquear' ?>
                    </a>

                    <a class="btn btn-sm btn-outline-dark"
                       href="/financas/public/?url=admin-usuario-reset-senha&id=<?= (int)$u['id'] ?>"
                       onclick="return confirm('Resetar senha deste usuário?')">
                      <i class="bi bi-key me-1"></i> Resetar senha
                    </a>
                  </div>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
