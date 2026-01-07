<?php
function moeda($v){ return 'R$ '.number_format((float)$v, 2, ',', '.'); }

function badgeTrend($diff){
    if (abs($diff) < 0.01) return '<span class="badge bg-secondary-subtle text-secondary border">igual</span>';
    if ($diff > 0) return '<span class="badge bg-danger-subtle text-danger border">▲ '.moeda(abs($diff)).'</span>';
    return '<span class="badge bg-success-subtle text-success border">▼ '.moeda(abs($diff)).'</span>';
}

// meses pt-br
$meses = [
  1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',
  7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
];

$anoSel = (int)($ano ?? date('Y'));
$mesSel = (int)($mes ?? date('m'));
$idContaSel = $idConta ?? null;
?>

<style>
/* ===== DASHBOARD CLEAN ===== */
.dash-title { font-weight: 800; letter-spacing: -0.4px; }
.kpi-card { border: 1px solid rgba(0,0,0,.06); border-radius: 16px; }
.kpi-label { font-size: .78rem; color: #6c757d; }
.kpi-value { font-size: 1.55rem; font-weight: 800; letter-spacing: -0.3px; }
.kpi-sub { font-size: .82rem; color: #6c757d; }

.section-title { font-weight: 800; letter-spacing: -0.2px; }
.card-soft { border: 1px solid rgba(0,0,0,.06); border-radius: 16px; }

.bar-row { display:flex; align-items:center; gap:12px; }
.bar-name { min-width: 160px; max-width: 160px; white-space: nowrap; overflow:hidden; text-overflow: ellipsis; font-weight: 600; }
.bar-wrap { flex: 1; }
.bar-total { min-width: 120px; text-align: right; font-weight: 700; }
@media (max-width: 576px){
  .bar-name { min-width: 120px; max-width: 120px; }
  .bar-total { min-width: 95px; }
}

/* metas */
.meta-item { padding: 12px 0; border-bottom: 1px solid rgba(0,0,0,.06); }
.meta-item:last-child{ border-bottom:0; }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h1 class="mb-1 dash-title">Dashboard</h1>
        <div class="text-muted">Visão geral • <?= $meses[$mesSel] ?> / <?= $anoSel ?></div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-primary" href="/financas/public/?url=lancamentos">+ Lançamento</a>
        <a class="btn btn-outline-secondary" href="/financas/public/?url=orcamentos">Orçamentos</a>
        <a class="btn btn-outline-success" href="/financas/public/?url=relatorio-csv&ano=<?= $anoSel ?>&mes=<?= str_pad($mesSel,2,'0',STR_PAD_LEFT) ?>">CSV</a>
        <a class="btn btn-dark" href="/financas/public/?url=relatorio-pdf-executivo&ano=<?= $anoSel ?>&mes=<?= str_pad($mesSel,2,'0',STR_PAD_LEFT) ?>">PDF</a>
    </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<!-- FILTRO RÁPIDO -->
<div class="card-soft p-3 mb-4">
  <form class="row g-2 align-items-end" method="GET" action="/financas/public/">
    <input type="hidden" name="url" value="dashboard">

    <div class="col-6 col-md-2">
      <label class="form-label kpi-label">Mês</label>
      <select name="mes" class="form-select">
        <?php for($m=1;$m<=12;$m++): ?>
          <option value="<?= $m ?>" <?= $m===$mesSel?'selected':'' ?>><?= $meses[$m] ?></option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="col-6 col-md-2">
      <label class="form-label kpi-label">Ano</label>
      <input type="number" name="ano" class="form-control" value="<?= $anoSel ?>">
    </div>

    <div class="col-12 col-md-5">
      <label class="form-label kpi-label">Conta</label>
      <select name="id_conta" class="form-select">
        <option value="">Todas as contas</option>
        <?php foreach(($contas ?? []) as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (!empty($idContaSel) && (int)$idContaSel===(int)$c['id'])?'selected':'' ?>>
            <?= htmlspecialchars($c['nome']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-12 col-md-3 d-flex gap-2">
      <button class="btn btn-primary w-100">Aplicar</button>
      <a class="btn btn-outline-secondary w-100" href="/financas/public/?url=dashboard">Hoje</a>
    </div>
  </form>
</div>

<!-- KPI GERAL (limpo) -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="kpi-card p-3 h-100">
      <div class="kpi-label">Receitas (mês)</div>
      <div class="kpi-value text-success"><?= moeda($resumo['receitas'] ?? 0) ?></div>
      <div class="kpi-sub">Somente lançamentos pagos</div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="kpi-card p-3 h-100">
      <div class="kpi-label">Despesas (mês)</div>
      <div class="kpi-value text-danger"><?= moeda($resumo['despesas'] ?? 0) ?></div>
      <div class="kpi-sub">Somente lançamentos pagos</div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="kpi-card p-3 h-100">
      <div class="kpi-label">Saldo (mês)</div>
      <?php $saldo = (float)($resumo['saldo'] ?? 0); ?>
      <div class="kpi-value <?= $saldo>=0?'text-primary':'text-danger' ?>"><?= moeda($saldo) ?></div>
      <div class="kpi-sub">Receitas − Despesas</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- CARDS POR CONTA -->
  <div class="col-lg-7">
    <div class="card-soft p-3 h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <div class="section-title">Contas</div>
          <div class="text-muted small">Saldo atual + variação do mês</div>
        </div>
      </div>

      <?php if (empty($cardsContas)): ?>
        <div class="text-muted">Cadastre uma conta para começar.</div>
      <?php else: ?>
        <div class="row g-2">
          <?php foreach($cardsContas as $cc): ?>
            <?php
              $delta = (float)$cc['delta_mes'];
              $deltaBadge = ($delta >= 0)
                ? '<span class="badge bg-primary-subtle text-primary border">+'.moeda($delta).'</span>'
                : '<span class="badge bg-danger-subtle text-danger border">-'.moeda(abs($delta)).'</span>';
            ?>
            <div class="col-12 col-md-6">
              <div class="kpi-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-semibold"><?= htmlspecialchars($cc['nome']) ?></div>
                    <div class="kpi-label mt-1">Saldo atual</div>
                    <div class="kpi-value <?= ((float)$cc['saldo_atual']>=0)?'text-success':'text-danger' ?>">
                      <?= moeda($cc['saldo_atual']) ?>
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="kpi-label">Variação mês</div>
                    <?= $deltaBadge ?>
                  </div>
                </div>

                <div class="mt-2 d-flex justify-content-between small text-muted">
                  <span>Receitas: <b class="text-success"><?= moeda($cc['mes_receitas']) ?></b></span>
                  <span>Despesas: <b class="text-danger"><?= moeda($cc['mes_despesas']) ?></b></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- TOP CATEGORIAS -->
  <div class="col-lg-5">
    <div class="card-soft p-3 h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <div class="section-title">Top categorias do mês</div>
          <div class="text-muted small">Despesas • tendência vs mês anterior</div>
        </div>
        <a class="small text-decoration-none" href="/financas/public/?url=orcamentos">ver orçamento</a>
      </div>

      <?php if (empty($topCategorias)): ?>
        <div class="text-muted">Sem despesas nesse período.</div>
      <?php else: ?>
        <?php foreach($topCategorias as $t): ?>
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold" style="max-width:65%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                <?= htmlspecialchars($t['nome']) ?>
              </div>
              <div class="text-end d-flex align-items-center gap-2">
                <span class="fw-bold"><?= moeda($t['total']) ?></span>
                <?= badgeTrend($t['diff']) ?>
              </div>
            </div>

            <div class="progress mt-2" style="height: 10px;">
              <div class="progress-bar" style="width: <?= (float)$t['w'] ?>%"></div>
            </div>

            <div class="text-muted small mt-1">
              mês anterior: <?= moeda($t['anterior']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- METAS -->
<div class="card-soft p-3 mt-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div>
      <div class="section-title">Metas do mês</div>
      <div class="text-muted small">Ex: gastar até R$ X em Alimentação</div>
    </div>
  </div>

  <!-- criar meta -->
  <form class="row g-2 align-items-end mb-3" method="POST" action="/financas/public/?url=dashboard-meta-store">
    <input type="hidden" name="ano" value="<?= $anoSel ?>">
    <input type="hidden" name="mes" value="<?= $mesSel ?>">

    <div class="col-12 col-md-6">
      <label class="form-label kpi-label">Categoria (Despesa)</label>
      <select name="id_categoria" class="form-select" required>
        <option value="">Selecione</option>
        <?php foreach(($categoriasDespesa ?? []) as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-7 col-md-3">
      <label class="form-label kpi-label">Limite (R$)</label>
      <input name="valor_limite" class="form-control money-br" inputmode="numeric" placeholder="0,00" required>
    </div>

    <div class="col-5 col-md-3">
      <button class="btn btn-primary w-100">Salvar meta</button>
    </div>
  </form>

  <?php if (empty($metas)): ?>
    <div class="text-muted">Nenhuma meta definida para este mês.</div>
  <?php else: ?>
    <?php foreach($metas as $m): ?>
      <?php
        $cls = 'bg-success';
        $txt = 'text-success';
        if ($m['status'] === 'warning') { $cls = 'bg-warning'; $txt = 'text-warning'; }
        if ($m['status'] === 'danger')  { $cls = 'bg-danger';  $txt = 'text-danger'; }
      ?>
      <div class="meta-item">
        <div class="d-flex justify-content-between align-items-center">
          <div class="fw-semibold"><?= htmlspecialchars($m['nome']) ?></div>
          <div class="text-end">
            <div class="fw-bold"><?= moeda($m['real']) ?> <span class="text-muted">/ <?= moeda($m['limite']) ?></span></div>
            <div class="small <?= $txt ?>">
              <?= number_format((float)$m['pct'], 1, ',', '.') ?>%
              <?php if ($m['status']==='warning'): ?> • alerta (80%+)<?php endif; ?>
              <?php if ($m['status']==='danger'): ?> • estourou<?php endif; ?>
            </div>
          </div>
        </div>

        <div class="progress mt-2" style="height: 12px;">
          <div class="progress-bar <?= $cls ?>" style="width: <?= (float)$m['barra'] ?>%"></div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
