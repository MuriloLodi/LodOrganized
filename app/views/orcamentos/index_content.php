<?php
// ===== Helpers / dados (SEM inventar backend) =====
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$meses = [
  1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',
  7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
];

$mesInt = (int)($mes ?? date('m'));
$anoInt = (int)($ano ?? date('Y'));

// total do mês (geral)
$orcadoGeral = 0.0;
$realGeral   = 0.0;

if (!empty($categoriasDespesa)) {
  foreach ($categoriasDespesa as $c) {
    $id = (int)$c['id'];
    $orcadoGeral += (float)($orcamentosMap[$id] ?? 0);
    $realGeral   += (float)($gastosReais[$id] ?? 0);
  }
}

$percentGeral = ($orcadoGeral > 0) ? ($realGeral / $orcadoGeral) * 100 : 0;

$classeGeral = 'bg-secondary';
$textoGeral  = 'Sem orçamentos definidos';

if ($orcadoGeral > 0) {
  if ($percentGeral <= 70)      { $classeGeral = 'bg-success'; $textoGeral = 'Saudável'; }
  elseif ($percentGeral <= 100) { $classeGeral = 'bg-warning'; $textoGeral = 'Atenção'; }
  else                          { $classeGeral = 'bg-danger';  $textoGeral = 'Estourado'; }
}

$restanteGeral = $orcadoGeral - $realGeral; // pode ficar negativo se estourar

// ===== dados gráficos (sem depender do controller) =====
$chartLabels = [];
$chartReal   = [];
$chartOrcado = [];

if (!empty($categoriasDespesa)) {
  foreach ($categoriasDespesa as $cat) {
    $id = (int)$cat['id'];
    $chartLabels[] = (string)($cat['nome'] ?? '');
    $chartOrcado[] = (float)($orcamentosMap[$id] ?? 0);
    $chartReal[]   = (float)($gastosReais[$id] ?? 0);
  }
}

// Top gastos por categoria (ordena por real desc)
$topLabels = $chartLabels;
$topReal   = $chartReal;

if (!empty($topReal)) {
  array_multisort($topReal, SORT_DESC, SORT_NUMERIC, $topLabels, SORT_ASC, SORT_STRING);
  $topLabels = array_slice($topLabels, 0, 8);
  $topReal   = array_slice($topReal, 0, 8);
}
?>

<!-- Chart.js (se você já carrega globalmente, pode remover daqui) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===== PADRÃO APP (mesmo “padrão” do dashboard) ===== */
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

.kpi{
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 18px;
  background:#fff;
}
.kpi .label{ color:#6c757d; font-size:.82rem; }
.kpi .value{ font-size:1.35rem; font-weight:900; letter-spacing:-.2px; }
.kpi .hint{ color:#6c757d; font-size:.82rem; }

.badge-soft{
  border:1px solid rgba(0,0,0,.10);
  border-radius:999px;
  padding:.35rem .6rem;
  font-weight:800;
}

.progress{ background: rgba(0,0,0,.06); border-radius:999px; }
.progress-bar{ border-radius:999px; }

.table-responsive-mobile{
  width:100%;
  overflow-x:auto;
  -webkit-overflow-scrolling:touch;
}
.table-responsive-mobile table{ min-width: 980px; }

.input-money{ min-width: 130px; }

@media (max-width: 991px){
  .table-responsive-mobile table{ min-width: 940px; }
  .input-money{ min-width: 120px; }
}
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-start align-items-md-center gap-3 mb-4 flex-wrap">
  <div>
    <h1 class="page-title mb-1">Orçamento mensal</h1>
    <div class="text-muted">
      Controle por categoria • <?= $meses[$mesInt] ?? '—' ?>/<?= $anoInt ?>
    </div>
  </div>

  <div class="d-flex gap-2 flex-wrap btn-stack">
    <a class="btn btn-outline-secondary" href="/financas/public/?url=dashboard">
      Dashboard
    </a>
    <a class="btn btn-outline-secondary" href="/financas/public/?url=lancamentos">
      Lançamentos
    </a>
    <a class="btn btn-outline-secondary" href="/financas/public/?url=categorias">
      Categorias
    </a>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<!-- FILTRO -->
<div class="card-soft p-4 mb-3 micro">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
    <div>
      <div class="section-title">Filtro</div>
      <div class="text-muted small">Selecione mês e ano para comparar com seus lançamentos</div>
    </div>
  </div>

  <form class="row g-2 align-items-end" method="GET" action="/financas/public/">
    <input type="hidden" name="url" value="orcamentos">

    <div class="col-12 col-md-4 col-lg-3">
      <label class="form-label mb-1">Mês</label>
      <select name="mes" class="form-select">
        <?php for ($m=1; $m<=12; $m++): ?>
          <option value="<?= $m ?>" <?= ($m === $mesInt) ? 'selected' : '' ?>>
            <?= $meses[$m] ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="col-12 col-md-3 col-lg-2">
      <label class="form-label mb-1">Ano</label>
      <input type="number" name="ano" value="<?= $anoInt ?>" class="form-control">
    </div>

    <div class="col-12 col-md-5 col-lg-3 d-flex gap-2">
      <button class="btn btn-primary w-100">Filtrar</button>
      <a class="btn btn-outline-secondary w-100"
         href="/financas/public/?url=orcamentos&ano=<?= (int)date('Y') ?>&mes=<?= (int)date('m') ?>">
        Hoje
      </a>
    </div>
  </form>
</div>

<!-- RESUMO DO MÊS -->
<div class="row g-3 mb-3">
  <div class="col-12 col-md-3">
    <div class="kpi p-3 h-100 micro">
      <div class="label mb-1">Orçado (total)</div>
      <div class="value">R$ <?= number_format($orcadoGeral, 2, ',', '.') ?></div>
      <div class="hint">Soma das categorias (D)</div>
    </div>
  </div>

  <div class="col-12 col-md-3">
    <div class="kpi p-3 h-100 micro">
      <div class="label mb-1">Real (gasto)</div>
      <div class="value">R$ <?= number_format($realGeral, 2, ',', '.') ?></div>
      <div class="hint">Com base nos lançamentos</div>
    </div>
  </div>

  <div class="col-12 col-md-3">
    <div class="kpi p-3 h-100 micro">
      <div class="label mb-1">Restante</div>
      <div class="value <?= ($restanteGeral >= 0) ? 'text-success' : 'text-danger' ?>">
        R$ <?= number_format($restanteGeral, 2, ',', '.') ?>
      </div>
      <div class="hint"><?= ($restanteGeral >= 0) ? 'Ainda disponível' : 'Você passou do total' ?></div>
    </div>
  </div>

  <div class="col-12 col-md-3">
    <div class="kpi p-3 h-100 micro">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div class="label mb-1">Consumo</div>
          <div class="value"><?= number_format($percentGeral, 1, ',', '.') ?>%</div>
        </div>
        <span class="badge-soft <?= $classeGeral ?> <?= ($classeGeral === 'bg-warning') ? 'text-dark' : 'text-white' ?>">
          <?= $textoGeral ?>
        </span>
      </div>

      <?php if ($orcadoGeral > 0): ?>
        <div class="progress mt-2" style="height: 14px;">
          <div class="progress-bar <?= $classeGeral ?>" style="width: <?= min($percentGeral, 100) ?>%"></div>
        </div>
        <?php if ($percentGeral > 100): ?>
          <div class="hint text-danger mt-2 fw-semibold">Você ultrapassou o orçamento total do mês.</div>
        <?php endif; ?>
      <?php else: ?>
        <div class="hint mt-2">Defina orçamento em pelo menos 1 categoria para calcular consumo.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- GRÁFICOS (sem inventar dados) -->
<div class="row g-3 mb-4">
  <div class="col-lg-5">
    <div class="card-soft p-4 h-100 micro">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
          <div class="section-title">Orçado x Real</div>
          <div class="text-muted small">Visão geral do mês</div>
        </div>
      </div>

      <?php if ($orcadoGeral <= 0): ?>
        <div class="text-muted">Defina orçamentos para exibir este gráfico.</div>
      <?php else: ?>
        <div style="height: 280px;">
          <canvas id="chartOrcadoReal"></canvas>
        </div>
        <div class="text-muted small mt-2">
          Dica: se “Real” encostar em 100%, você está no limite do orçamento.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card-soft p-4 h-100 micro">
      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div>
          <div class="section-title">Top gastos por categoria</div>
          <div class="text-muted small">Somente despesas (Real)</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-outline-secondary btn-sm" href="/financas/public/?url=categorias">Criar categoria</a>
          <a class="btn btn-outline-secondary btn-sm" href="/financas/public/?url=lancamentos">Lançar despesa</a>
        </div>
      </div>

      <?php if (empty($topReal) || array_sum($topReal) <= 0): ?>
        <div class="text-muted">Sem gastos para exibir neste período.</div>
      <?php else: ?>
        <div style="height: 280px;">
          <canvas id="chartTopGastos"></canvas>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- LISTA -->
<div class="card-soft p-4 micro">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <div class="section-title">Categorias de despesa</div>
      <div class="text-muted small">Ajuste o orçamento por categoria e acompanhe o consumo</div>
    </div>
  </div>

  <?php if (empty($categoriasDespesa)): ?>
    <div class="alert alert-info mb-0">
      Você ainda não cadastrou categorias de <b>Despesa</b>.<br>
      Vá em <b>Categorias</b> e crie pelo menos uma com tipo <b>Despesa (D)</b>.
    </div>
  <?php else: ?>

    <div class="table-responsive-mobile">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="min-width:260px">Categoria</th>
            <th style="min-width:130px">Orçado</th>
            <th style="min-width:130px">Real</th>
            <th style="min-width:320px">Consumo</th>
            <th style="min-width:340px">Ajustar orçamento</th>
          </tr>
        </thead>

        <tbody>
        <?php foreach ($categoriasDespesa as $cat): ?>
          <?php
            $idCat  = (int)$cat['id'];
            $orcado = (float)($orcamentosMap[$idCat] ?? 0);
            $real   = (float)($gastosReais[$idCat] ?? 0);

            $pct = ($orcado > 0) ? ($real / $orcado) * 100 : 0;

            if ($orcado <= 0) { $classe='bg-secondary'; $label='Sem orçamento'; }
            elseif ($pct <= 70) { $classe='bg-success'; $label='Saudável'; }
            elseif ($pct <= 100){ $classe='bg-warning'; $label='Atenção'; }
            else { $classe='bg-danger'; $label='Estourado'; }

            $barra = min($pct, 100);
          ?>

          <tr>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($cat['nome'] ?? '') ?></div>
              <div class="text-muted small">
                <span class="badge <?= $classe ?> <?= ($classe==='bg-warning') ? 'text-dark' : '' ?>">
                  <?= $label ?>
                </span>
              </div>
            </td>

            <td class="fw-semibold">
              R$ <?= number_format($orcado, 2, ',', '.') ?>
            </td>

            <td class="<?= ($real > 0) ? 'fw-semibold' : 'text-muted' ?>">
              R$ <?= number_format($real, 2, ',', '.') ?>
            </td>

            <td>
              <div class="progress" style="height: 18px;">
                <div class="progress-bar <?= $classe ?>" style="width: <?= $barra ?>%">
                  <?= ($orcado > 0) ? number_format($pct, 1, ',', '.') . '%' : '—' ?>
                </div>
              </div>

              <div class="text-muted small mt-1 d-flex justify-content-between">
                <span>Gasto: <b>R$ <?= number_format($real, 2, ',', '.') ?></b></span>
                <span>Meta: <b>R$ <?= number_format($orcado, 2, ',', '.') ?></b></span>
              </div>

              <?php if ($orcado > 0 && $pct > 100): ?>
                <div class="text-danger small fw-semibold mt-1">Ultrapassou o orçamento</div>
              <?php endif; ?>
            </td>

            <td>
              <form method="POST"
                    action="/financas/public/?url=orcamentos-store"
                    class="d-flex gap-2 align-items-center flex-wrap">
                <input type="text"
                       name="valor"
                       class="form-control form-control-sm money-br input-money"
                       inputmode="numeric"
                       placeholder="0,00"
                       value="<?= number_format($orcado, 2, ',', '.') ?>">

                <input type="hidden" name="id_categoria" value="<?= $idCat ?>">
                <input type="hidden" name="ano" value="<?= $anoInt ?>">
                <input type="hidden" name="mes" value="<?= $mesInt ?>">

                <button class="btn btn-sm btn-primary">
                  Salvar
                </button>

                <?php if ($orcado > 0): ?>
                  <button class="btn btn-sm btn-outline-secondary" type="button"
                          onclick="this.closest('form').querySelector('input[name=valor]').value='0,00';">
                    Zerar
                  </button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="text-muted small mt-3">
      Dica: se você não definir orçamento (0,00), a categoria fica como “Sem orçamento” e não entra direito no consumo.
    </div>

  <?php endif; ?>
</div>

<script>
(function(){
  // ===== Orçado x Real (doughnut) =====
  const orcadoGeral = <?= json_encode((float)$orcadoGeral) ?>;
  const realGeral   = <?= json_encode((float)$realGeral) ?>;

  const elOrcadoReal = document.getElementById('chartOrcadoReal');
  if (elOrcadoReal && window.Chart && orcadoGeral > 0) {
    const restante = Math.max(orcadoGeral - realGeral, 0);

    new Chart(elOrcadoReal, {
      type: 'doughnut',
      data: {
        labels: ['Real', 'Restante'],
        datasets: [{
          data: [realGeral, restante]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const v = Number(ctx.raw || 0);
                return `${ctx.label}: R$ ${v.toFixed(2).replace('.', ',')}`;
              }
            }
          }
        }
      }
    });
  }

  // ===== Top gastos (bar) =====
  const topLabels = <?= json_encode($topLabels, JSON_UNESCAPED_UNICODE) ?>;
  const topReal   = <?= json_encode(array_map('floatval', $topReal)) ?>;

  const elTop = document.getElementById('chartTopGastos');
  if (elTop && window.Chart && Array.isArray(topReal) && topReal.length) {
    new Chart(elTop, {
      type: 'bar',
      data: {
        labels: topLabels,
        datasets: [{
          label: 'Gasto (R$)',
          data: topReal
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (v) => 'R$ ' + Number(v).toFixed(0)
            }
          }
        }
      }
    });
  }
})();
</script>
