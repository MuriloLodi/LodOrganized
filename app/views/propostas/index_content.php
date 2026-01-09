<?php
// (mantém seu backend igual — só padrão de front)
// esperado: $propostas
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<style>
/* ===== PADRÃO VISUAL (igual dashboard) ===== */
.page-title{ font-weight: 900; letter-spacing: -.5px; margin:0; }
.page-sub{ color:#6c757d; }

.card-soft{
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 18px;
  background:#fff;
  box-shadow: 0 10px 26px rgba(0,0,0,.06);
}
.card-soft .card-body{ padding: 1.25rem; }

.micro{
  transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
}
.micro:hover{
  transform: translateY(-1px);
  box-shadow: 0 14px 34px rgba(0,0,0,.10);
  border-color: rgba(13,110,253,.25);
}

.btn{ border-radius: 12px; font-weight: 650; }
.btn:active{ transform: translateY(1px); }



.table-responsive-mobile{
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.table-responsive-mobile table{ min-width: 860px; }

.table thead th{ white-space: nowrap; }
.row-hover tr{ transition: background-color .12s ease; }
.row-hover tr:hover{ background: rgba(13,110,253,.04); }

.kpi-chip{
  display:inline-flex; align-items:center; gap:.5rem;
  padding:.45rem .7rem;
  border-radius: 14px;
  border: 1px solid rgba(0,0,0,.08);
  background: rgba(0,0,0,.02);
  font-weight: 750;
  font-size: .9rem;
}

@media (max-width: 576px){
  .btn-stack .btn{ width: 100%; }
}
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
  <div>
    <h1 class="page-title">Orçamentos / Propostas</h1>
    <div class="page-sub">Crie propostas profissionais e gere PDF</div>
  </div>

  <div class="d-flex gap-2 flex-wrap btn-stack">
    <a class="btn btn-primary" href="/financas/public/?url=propostas-new">
      Nova proposta
    </a>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= h($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<!-- LISTA -->
<div class="card-soft micro">
  <div class="card-body">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div class="kpi-chip">
        Total: <span class="text-muted"><?= (int)count($propostas ?? []) ?></span>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <input type="text" id="qProp" class="form-control" style="max-width:320px"
               placeholder="Buscar por nº / cliente / status...">
        <a class="btn btn-outline-secondary" href="/financas/public/?url=propostas">
          Atualizar
        </a>
      </div>
    </div>

    <?php if (empty($propostas)): ?>
      <div class="text-muted">Você ainda não criou nenhuma proposta.</div>
    <?php else: ?>
      <div class="table-responsive-mobile">
        <table class="table align-middle table-hover mb-0 row-hover" id="tblProp">
          <thead class="table-light">
            <tr>
              <th style="width:90px;">Nº</th>
              <th>Cliente</th>
              <th style="width:140px;">Emissão</th>
              <th style="width:140px;">Status</th>
              <th class="text-end" style="width:140px;">Total</th>
              <th class="text-end" style="width:180px;">Ações</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($propostas as $p): ?>
            <?php
              $status = (string)($p['status'] ?? '');
              $badge = 'bg-secondary';
              $txt = '';
              if ($status === 'enviado')  { $badge = 'bg-primary'; }
              if ($status === 'aprovado') { $badge = 'bg-success'; }
              if ($status === 'recusado') { $badge = 'bg-danger'; }

              $q = strtolower(
                (string)($p['numero'] ?? '').' '.
                (string)($p['cliente_nome'] ?? '').' '.
                (string)($status ?? '')
              );
            ?>
            <tr data-q="<?= h($q) ?>">
              <td class="fw-semibold"><?= h($p['numero']) ?></td>
              <td class="text-truncate" style="max-width:360px;"><?= h($p['cliente_nome']) ?></td>
              <td><?= !empty($p['data_emissao']) ? date('d/m/Y', strtotime($p['data_emissao'])) : '—' ?></td>
              <td>
                <span class="badge <?= $badge ?> <?= $badge==='bg-warning'?'text-dark':'' ?>">
                  <?= h($status) ?>
                </span>
              </td>
              <td class="text-end fw-semibold">
                R$ <?= number_format((float)($p['total'] ?? 0), 2, ',', '.') ?>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                  <a class="btn btn-outline-secondary btn-sm"
                     href="/financas/public/?url=propostas-edit&id=<?= (int)$p['id'] ?>"
                     title="Editar">
                    Editar
                  </a>

                  <a class="btn btn-outline-dark btn-sm"
                     href="/financas/public/?url=propostas-pdf&id=<?= (int)$p['id'] ?>"
                     title="PDF">
                    PDF
                  </a>

                  <a class="btn btn-outline-danger btn-sm"
                     href="/financas/public/?url=propostas-delete&id=<?= (int)$p['id'] ?>"
                     onclick="return confirm('Excluir esta proposta?')"
                     title="Excluir">
                    Excluir
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="text-muted small mt-3">
        Dica: use a busca para filtrar rapidamente sem recarregar a página.
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
(function(){
  const input = document.getElementById('qProp');
  const tbody = document.querySelector('#tblProp tbody');
  if(!input || !tbody) return;

  input.addEventListener('input', function(){
    const term = (this.value || '').toLowerCase().trim();
    const rows = tbody.querySelectorAll('tr');

    rows.forEach(r => {
      const q = (r.getAttribute('data-q') || '').toLowerCase();
      if(!term){ r.style.display = ''; return; }
      r.style.display = q.includes(term) ? '' : 'none';
    });
  });
})();
</script>
