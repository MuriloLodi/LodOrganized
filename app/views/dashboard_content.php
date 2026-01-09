<?php
function moeda($v){ return 'R$ '.number_format((float)$v, 2, ',', '.'); }

function badgeTrend($diff){
    if (abs($diff) < 0.01)
        return '<span class="badge bg-secondary-subtle text-secondary border">igual</span>';
    if ($diff > 0)
        return '<span class="badge bg-danger-subtle text-danger border">▲ '.moeda(abs($diff)).'</span>';
    return '<span class="badge bg-success-subtle text-success border">▼ '.moeda(abs($diff)).'</span>';
}

$meses = [
  1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',
  7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
];

$anoSel = (int)($ano ?? date('Y'));
$mesSel = (int)($mes ?? date('m'));
$idContaSel = $idConta ?? null;
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{ background:#f8fafc; }

.dash-title{
  font-weight:900;
  letter-spacing:-.5px;
}

.card-soft,
.kpi-card{
  background:#fff;
  border-radius:18px;
  border:1px solid rgba(0,0,0,.05);
  box-shadow:0 10px 28px rgba(0,0,0,.06);
}

.kpi-label{
  font-size:.72rem;
  text-transform:uppercase;
  letter-spacing:.08em;
  color:#6c757d;
}

.kpi-value{
  font-size:1.7rem;
  font-weight:900;
  letter-spacing:-.4px;
}

.section-title{
  font-weight:900;
  letter-spacing:-.4px;
}

.btn{
  border-radius:12px;
  font-weight:600;
}

.progress,
.progress-bar{
  border-radius:999px;
}

@media(max-width:768px){
  .kpi-value{ font-size:1.5rem; }
}
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="dash-title mb-1">Dashboard</h1>
    <div class="text-muted">Visão geral • <?= $meses[$mesSel] ?> / <?= $anoSel ?></div>
  </div>

  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-primary" href="/financas/public/?url=lancamentos">+ Lançamento</a>
    <a class="btn btn-outline-secondary" href="/financas/public/?url=orcamentos">Orçamentos</a>
    <a class="btn btn-outline-success" href="/financas/public/?url=relatorio-csv&ano=<?= $anoSel ?>&mes=<?= str_pad($mesSel,2,'0',STR_PAD_LEFT) ?>">CSV</a>
    <a class="btn btn-dark" href="/financas/public/?url=relatorio-pdf-executivo&ano=<?= $anoSel ?>&mes=<?= str_pad($mesSel,2,'0',STR_PAD_LEFT) ?>">PDF</a>
  </div>
</div>

<!-- KPIS -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="kpi-card p-3">
      <div class="kpi-label">Receitas</div>
      <div class="kpi-value text-success"><?= moeda($resumo['receitas'] ?? 0) ?></div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="kpi-card p-3">
      <div class="kpi-label">Despesas</div>
      <div class="kpi-value text-danger"><?= moeda($resumo['despesas'] ?? 0) ?></div>
    </div>
  </div>

  <div class="col-md-4">
    <?php $saldo=(float)($resumo['saldo'] ?? 0); ?>
    <div class="kpi-card p-3">
      <div class="kpi-label">Saldo</div>
      <div class="kpi-value <?= $saldo>=0?'text-primary':'text-danger' ?>">
        <?= moeda($saldo) ?>
      </div>
    </div>
  </div>
</div>

<!-- GRAFICOS -->
<?php if (!empty($topCategorias)): ?>
<div class="row g-3 mb-4">
  <div class="col-lg-6">
    <div class="card-soft p-3">
      <div class="section-title mb-2">Despesas por categoria</div>
      <canvas id="graficoPizza"></canvas>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card-soft p-3">
      <div class="section-title mb-2">Comparativo de gastos</div>
      <canvas id="graficoBarra"></canvas>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- CONTAS -->
<div class="card-soft p-3 mb-4">
  <div class="section-title mb-2">Contas</div>
  <div class="row g-2">
    <?php foreach(($cardsContas ?? []) as $c): ?>
      <div class="col-md-6">
        <div class="kpi-card p-3">
          <div class="d-flex justify-content-between">
            <div>
              <div class="fw-semibold"><?= htmlspecialchars($c['nome']) ?></div>
              <div class="kpi-value <?= $c['saldo_atual']>=0?'text-success':'text-danger' ?>">
                <?= moeda($c['saldo_atual']) ?>
              </div>
            </div>
            <?= badgeTrend($c['delta_mes']) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- METAS -->
<div class="card-soft p-3">
  <div class="section-title mb-2">Metas do mês</div>

  <?php if (empty($metas)): ?>
    <div class="text-muted">Nenhuma meta definida.</div>
  <?php else: ?>
    <?php foreach($metas as $m): ?>
      <?php
        $cls='bg-success';
        if($m['status']==='warning') $cls='bg-warning';
        if($m['status']==='danger') $cls='bg-danger';
      ?>
      <div class="mb-3">
        <div class="d-flex justify-content-between">
          <strong><?= htmlspecialchars($m['nome']) ?></strong>
          <span><?= moeda($m['real']) ?> / <?= moeda($m['limite']) ?></span>
        </div>
        <div class="progress mt-2">
          <div class="progress-bar <?= $cls ?>" style="width:<?= (float)$m['barra'] ?>%"></div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if (!empty($topCategorias)): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){

  const labels = <?= json_encode(array_column($topCategorias,'nome')) ?>;
  const valores = <?= json_encode(array_column($topCategorias,'total')) ?>;

  new Chart(document.getElementById('graficoPizza'), {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: valores,
        backgroundColor: [
          '#4f46e5','#22c55e','#f59e0b','#ef4444','#06b6d4','#a855f7'
        ]
      }]
    },
    options: {
      cutout: '65%',
      plugins: { legend: { position: 'bottom' } }
    }
  });

  new Chart(document.getElementById('graficoBarra'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Total gasto',
        data: valores,
        backgroundColor: '#4f46e5'
      }]
    },
    options: {
      indexAxis: 'y',
      plugins: { legend: { display: false } }
    }
  });

});
</script>
<?php endif; ?>
