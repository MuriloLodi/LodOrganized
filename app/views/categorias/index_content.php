<?php
// garante sessão
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
?>

<style>
/* ===== PADRÃO APP (igual dashboard / editar lançamento) ===== */
body{ background:#f8fafc; }

.page-title{ font-weight:900; letter-spacing:-.5px; }
.section-title{ font-weight:900; letter-spacing:-.4px; }

.card-soft{
  background:#fff;
  border-radius:18px;
  border:1px solid rgba(0,0,0,.05);
  box-shadow:0 10px 28px rgba(0,0,0,.06);
}

.form-label{
  font-size:.72rem;
  text-transform:uppercase;
  letter-spacing:.08em;
  color:#6c757d;
}

.btn{ border-radius:12px; font-weight:650; }
.btn:active{ transform: translateY(1px); }

.micro{
  transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
}
.micro:hover{
  transform: translateY(-1px);
  box-shadow:0 14px 36px rgba(0,0,0,.10);
  border-color: rgba(13,110,253,.25);
}

/* list item refinado */
.cat-item{
  border-color: rgba(0,0,0,.06) !important;
  padding: 14px 0;
}

.badge-soft{
  border:1px solid rgba(0,0,0,.08);
  border-radius:999px;
  padding:.35rem .6rem;
  background:#fff;
}

.value-total{
  font-weight:900;
  letter-spacing:-.2px;
}

@media (max-width: 576px){
  .actions-stack .btn{ width:100%; }
  .searchbox{ width:100% !important; max-width:100% !important; }
}
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="page-title mb-1">Categorias</h1>
    <div class="text-muted">Organize suas receitas e despesas</div>
  </div>
</div>

<div class="row g-3">

  <!-- FORM -->
  <div class="col-lg-4">
    <div class="card-soft p-4 h-100 micro">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <div class="section-title">Nova categoria</div>
          <div class="text-muted small">Crie categorias simples e objetivas</div>
        </div>
      </div>

      <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/financas/public/?url=categorias-store">
        <div class="mb-3">
          <label class="form-label">Nome</label>
          <input name="nome" class="form-control" placeholder="Ex: Alimentação, Combustível..." required>
        </div>

        <div class="mb-3">
          <label class="form-label">Tipo</label>
          <select name="tipo" class="form-select" required>
            <option value="">Selecione</option>
            <option value="R">Receita</option>
            <option value="D">Despesa</option>
          </select>
          <div class="form-text">Depois de criada, o tipo fica travado (padrão do app).</div>
        </div>

        <!-- compatibilidade caso backend espere "icone" -->
        <input type="hidden" name="icone" value="">

        <button class="btn btn-primary w-100">Salvar categoria</button>
      </form>
    </div>
  </div>

  <!-- LISTA -->
  <div class="col-lg-8">
    <div class="card-soft p-4 micro">
      <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
        <div>
          <div class="section-title">Minhas categorias</div>
          <div class="text-muted small">Edite apenas o nome — o tipo é fixo</div>
        </div>

        <input type="text"
               id="buscaCategoria"
               class="form-control form-control-sm searchbox"
               style="max-width: 320px;"
               placeholder="Buscar categoria...">
      </div>

      <?php if (empty($categorias)): ?>
        <div class="text-muted">Nenhuma categoria cadastrada.</div>
      <?php else: ?>

        <div class="list-group list-group-flush">
          <?php foreach ($categorias as $c): ?>
            <?php
              $tipo = $c['tipo'] ?? '';
              $badgeTipo = ($tipo === 'R')
                ? '<span class="badge bg-success-subtle text-success border">Receita</span>'
                : '<span class="badge bg-danger-subtle text-danger border">Despesa</span>';
            ?>

            <div class="list-group-item cat-item categoria-item"
                 data-nome="<?= strtolower($c['nome']) ?>">

              <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">

                <!-- ESQUERDA: editar só nome -->
                <form method="POST"
                      action="/financas/public/?url=categorias-update"
                      class="d-flex flex-column flex-md-row gap-2 w-100">

                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">

                  <!-- mantém tipo travado (manda hidden pro backend não zerar) -->
                  <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">

                  <!-- compatibilidade caso backend espere "icone" -->
                  <input type="hidden" name="icone" value="<?= htmlspecialchars($c['icone'] ?? '') ?>">

                  <div class="flex-grow-1">
                    <label class="form-label mb-1">Nome</label>
                    <input name="nome"
                           class="form-control form-control-sm"
                           value="<?= htmlspecialchars($c['nome']) ?>"
                           required>
                  </div>

                  <div class="d-flex align-items-end">
                    <button class="btn btn-outline-primary btn-sm w-100">
                      Salvar
                    </button>
                  </div>
                </form>

                <!-- DIREITA: tipo + total + ações -->
                <div class="text-lg-end text-start actions-stack" style="min-width: 210px;">
                  <div class="d-flex justify-content-between justify-content-lg-end align-items-center gap-2">
                    <?= $badgeTipo ?>
                    <span class="badge-soft text-muted small">total</span>
                  </div>

                  <div class="value-total mt-1">
                    R$ <?= number_format((float)($c['total'] ?? 0), 2, ',', '.') ?>
                  </div>

                  <?php if (Categoria::canDelete($pdo, $_SESSION['usuario']['id'], $c['id'])): ?>
                    <form method="POST"
                          action="/financas/public/?url=categorias-delete"
                          class="mt-2"
                          onsubmit="return confirm('Excluir categoria?')">
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <button class="btn btn-outline-danger btn-sm w-100">
                        Excluir
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border mt-2 w-100 d-inline-flex justify-content-center">
                      Em uso
                    </span>
                  <?php endif; ?>
                </div>

              </div>
            </div>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

    </div>
  </div>

</div>

<!-- BUSCA -->
<script>
(function(){
  const input = document.getElementById('buscaCategoria');
  if (!input) return;

  input.addEventListener('keyup', e => {
    const termo = (e.target.value || '').toLowerCase();
    document.querySelectorAll('.categoria-item').forEach(item => {
      item.style.display = item.dataset.nome.includes(termo) ? '' : 'none';
    });
  });
})();
</script>
