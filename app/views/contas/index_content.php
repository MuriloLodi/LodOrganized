<?php
// garante sessão
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<style>
/* ===== PADRÃO DASHBOARD ===== */
body{
  background:#f8fafc;
}

.page-title{
  font-weight:900;
  letter-spacing:-.5px;
}

.card-soft{
  background:#fff;
  border-radius:18px;
  border:1px solid rgba(0,0,0,.05);
  box-shadow:0 10px 28px rgba(0,0,0,.06);
}

.section-title{
  font-weight:900;
  letter-spacing:-.4px;
}

.form-label{
  font-size:.72rem;
  text-transform:uppercase;
  letter-spacing:.08em;
  color:#6c757d;
}

.btn{
  border-radius:12px;
  font-weight:600;
}

.conta-item{
  padding:14px 0;
  border-bottom:1px solid rgba(0,0,0,.06);
}
.conta-item:last-child{
  border-bottom:0;
}

.conta-saldo{
  font-size:1.1rem;
  font-weight:800;
  letter-spacing:-.3px;
}

@media(max-width:768px){
  .conta-saldo{
    font-size:1rem;
  }
}
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="page-title mb-1">Contas financeiras</h1>
    <div class="text-muted">Gerencie saldos e movimentações</div>
  </div>
</div>

<div class="row g-3">

  <!-- NOVA CONTA -->
  <div class="col-lg-4">
    <div class="card-soft p-4 h-100">

      <div class="section-title mb-3">Nova conta</div>

      <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/financas/public/?url=contas-store">

        <div class="mb-3">
          <label class="form-label">Nome da conta</label>
          <input name="nome" class="form-control" placeholder="Ex: Banco, Carteira, Pix" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Saldo inicial</label>
          <input
            type="text"
            name="saldo"
            class="form-control money-br"
            inputmode="numeric"
            placeholder="0,00"
            value="<?= isset($conta['saldo_atual']) ? number_format((float)$conta['saldo_atual'],2,',','.') : '0,00' ?>">
        </div>

        <button class="btn btn-primary w-100">
          Salvar conta
        </button>

      </form>
    </div>
  </div>

  <!-- LISTA DE CONTAS -->
  <div class="col-lg-8">
    <div class="card-soft p-4">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="section-title">Minhas contas</div>
        <input
          type="text"
          id="buscaConta"
          class="form-control form-control-sm"
          style="max-width:220px"
          placeholder="Buscar conta...">
      </div>

      <?php if (empty($contas)): ?>
        <div class="text-muted">Nenhuma conta cadastrada.</div>
      <?php else: ?>

        <?php foreach ($contas as $c): ?>
          <div class="conta-item conta-row" data-nome="<?= strtolower($c['nome']) ?>">

            <div class="d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">

              <!-- EDITAR -->
              <form
                method="POST"
                action="/financas/public/?url=contas-update"
                class="d-flex flex-column flex-md-row gap-2 w-100">

                <input type="hidden" name="id" value="<?= $c['id'] ?>">

                <input
                  name="nome"
                  class="form-control form-control-sm"
                  value="<?= htmlspecialchars($c['nome']) ?>">

                <button class="btn btn-outline-primary btn-sm w-100 w-md-auto">
                  Salvar
                </button>
              </form>

              <!-- SALDO + AÇÕES -->
              <div class="text-start text-lg-end">

                <div class="conta-saldo <?= $c['saldo_atual'] >= 0 ? 'text-success' : 'text-danger' ?>">
                  R$ <?= number_format($c['saldo_atual'],2,',','.') ?>
                </div>

                <?php if (Conta::canDelete($pdo, $_SESSION['usuario']['id'], $c['id'])): ?>
                  <form
                    method="POST"
                    action="/financas/public/?url=contas-delete"
                    onsubmit="return confirm('Excluir conta?')">

                    <input type="hidden" name="id" value="<?= $c['id'] ?>">

                    <button class="btn btn-outline-danger btn-sm mt-1 w-100 w-lg-auto">
                      Excluir
                    </button>
                  </form>
                <?php else: ?>
                  <span class="badge bg-secondary mt-1">Em uso</span>
                <?php endif; ?>

              </div>

            </div>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>

    </div>
  </div>
</div>

<!-- BUSCA -->
<script>
document.getElementById('buscaConta')?.addEventListener('keyup', e => {
  const termo = e.target.value.toLowerCase();
  document.querySelectorAll('.conta-row').forEach(row => {
    row.style.display = row.dataset.nome.includes(termo) ? '' : 'none';
  });
});
</script>
