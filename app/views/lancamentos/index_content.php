<?php
$totalReceitas = 0;
$totalDespesas = 0;

foreach ($lancamentos as $l) {
    // só conta pagos no resumo (pra não bagunçar saldo/real)
    if (($l['status'] ?? 'pago') !== 'pago') continue;
    if ($l['tipo'] === 'R') $totalReceitas += (float)$l['valor'];
    else $totalDespesas += (float)$l['valor'];
}

/* =========================
   DADOS PARA GRÁFICOS
   (SEM BACKEND NOVO)
   ========================= */
$catsDesp = [];     // categoria => total despesas
$contasR = [];      // conta => total receitas
$contasD = [];      // conta => total despesas
$diasR = [];        // Y-m-d => total receitas
$diasD = [];        // Y-m-d => total despesas

foreach ($lancamentos as $l) {
    if (($l['status'] ?? 'pago') !== 'pago') continue;

    $valor = (float)$l['valor'];
    $data = !empty($l['data']) ? date('Y-m-d', strtotime($l['data'])) : null;
    $contaNome = (string)($l['conta'] ?? '—');
    $catNome = (string)($l['categoria'] ?? '—');

    // sobrescreve categorias "especiais" igual sua tabela já faz
    if (!empty($l['transferencia_id'])) $catNome = 'Transferência';
    if (!empty($l['parcelamento_id'])) $catNome = 'Parcelado';
    if (!empty($l['recorrencia_id'])) $catNome = 'Recorrente';

    if ($l['tipo'] === 'D') {
        $catsDesp[$catNome] = ($catsDesp[$catNome] ?? 0) + $valor;
        $contasD[$contaNome] = ($contasD[$contaNome] ?? 0) + $valor;
        if ($data) $diasD[$data] = ($diasD[$data] ?? 0) + $valor;
    } else {
        $contasR[$contaNome] = ($contasR[$contaNome] ?? 0) + $valor;
        if ($data) $diasR[$data] = ($diasR[$data] ?? 0) + $valor;
    }
}

// ordena pizza por maior despesa
arsort($catsDesp);

// monta evolução por dia (intervalo baseado nos próprios lançamentos)
$datasAll = array_unique(array_merge(array_keys($diasR), array_keys($diasD)));
sort($datasAll);

$linhaLabels = [];
$linhaReceitas = [];
$linhaDespesas = [];
foreach ($datasAll as $d) {
    $linhaLabels[] = date('d/m', strtotime($d));
    $linhaReceitas[] = round((float)($diasR[$d] ?? 0), 2);
    $linhaDespesas[] = round((float)($diasD[$d] ?? 0), 2);
}

// gráfico por conta (barra)
$contasAll = array_unique(array_merge(array_keys($contasR), array_keys($contasD)));
sort($contasAll);

$barLabels = [];
$barReceitas = [];
$barDespesas = [];
foreach ($contasAll as $cn) {
    $barLabels[] = $cn;
    $barReceitas[] = round((float)($contasR[$cn] ?? 0), 2);
    $barDespesas[] = round((float)($contasD[$cn] ?? 0), 2);
}

// pizza
$pizzaLabels = array_keys($catsDesp);
$pizzaData = array_values($catsDesp);
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===== PADRÃO DASHBOARD ===== */
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

.btn{ border-radius:12px; font-weight:600; }

.nav-pills .nav-link{
  border:1px solid rgba(0,0,0,.08);
  font-weight:700;
  border-radius:999px;
}
.nav-pills .nav-link.active{
  background:#0d6efd;
  border-color:#0d6efd;
}

.table-responsive-mobile{ width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
.table-responsive-mobile table{ min-width: 980px; }

.kpi-mini{
  border-radius:16px;
  border:1px solid rgba(0,0,0,.05);
  box-shadow:0 8px 18px rgba(0,0,0,.05);
}

.kpi-mini .lbl{
  font-size:.72rem;
  text-transform:uppercase;
  letter-spacing:.08em;
  color:#6c757d;
}

.kpi-mini .val{
  font-size:1.25rem;
  font-weight:900;
  letter-spacing:-.3px;
}

.chart-wrap{
  border-radius:18px;
  border:1px solid rgba(0,0,0,.05);
  box-shadow:0 10px 28px rgba(0,0,0,.06);
  background:#fff;
  padding:16px;
  height: 320px;
}
.chart-wrap canvas{
  width:100% !important;
  height: 240px !important;
}

@media (max-width: 991px){
  .chart-wrap{ height:auto; }
  .chart-wrap canvas{ height: 220px !important; }
}
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="page-title mb-1">Lançamentos</h1>
    <div class="text-muted">Recorrentes, parcelas, transferências e anexos</div>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<!-- ABAS -->
<ul class="nav nav-pills mb-3 gap-2" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-lanc">+ Lançamento</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-transf">↔ Transferência</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-parc">💳 Parcelas</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rec">🔁 Recorrente</button>
  </li>
</ul>

<div class="row g-3">

  <!-- FORMULÁRIOS -->
  <div class="col-lg-4">
    <div class="card-soft p-4">
      <div class="tab-content">

        <!-- ===================== NOVO LANÇAMENTO ===================== -->
        <div class="tab-pane fade show active" id="tab-lanc">
          <div class="section-title mb-3">Novo lançamento</div>

          <form method="POST" action="/financas/public/?url=lancamentos-store">
            <div class="row g-2">

              <div class="col-6">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select" required>
                  <option value="">Selecione</option>
                  <option value="R">Receita</option>
                  <option value="D">Despesa</option>
                </select>
              </div>

              <div class="col-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="pago">Pago</option>
                  <option value="pendente">Pendente</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Data</label>
                <input type="date" name="data" class="form-control" required>
              </div>

              <div class="col-12">
                <label class="form-label">Conta</label>
                <select name="id_conta" class="form-select" required>
                  <option value="">Selecione</option>
                  <?php foreach ($contas as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Categoria</label>
                <select name="id_categoria" class="form-select" required>
                  <option value="">Selecione</option>
                  <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Valor</label>
                <input name="valor" class="form-control input-money" placeholder="0,00" required>
              </div>

              <div class="col-12">
                <label class="form-label">Descrição</label>
                <input type="text" name="descricao" class="form-control">
              </div>
            </div>

            <button class="btn btn-primary w-100 mt-3">Salvar</button>
          </form>
        </div>

        <!-- ===================== TRANSFERÊNCIA ===================== -->
        <div class="tab-pane fade" id="tab-transf">
          <div class="section-title mb-3">Transferência entre contas</div>

          <form method="POST" action="/financas/public/?url=lancamentos-transferencia-store">
            <div class="row g-2">

              <div class="col-6">
                <label class="form-label">Conta origem</label>
                <select name="id_conta_origem" class="form-select" required>
                  <option value="">Selecione</option>
                  <?php foreach ($contas as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-6">
                <label class="form-label">Conta destino</label>
                <select name="id_conta_destino" class="form-select" required>
                  <option value="">Selecione</option>
                  <?php foreach ($contas as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Data</label>
                <input type="date" name="data" class="form-control" required>
              </div>

              <div class="col-12">
                <label class="form-label">Valor</label>
                <input name="valor" class="form-control input-money" placeholder="0,00" required>
              </div>

              <div class="col-12">
                <label class="form-label">Descrição (opcional)</label>
                <input type="text" name="descricao" class="form-control">
              </div>

            </div>

            <button class="btn btn-primary w-100 mt-3">Transferir</button>
          </form>
        </div>

        <!-- ===================== PARCELAS ===================== -->
        <div class="tab-pane fade" id="tab-parc">
          <div class="section-title mb-3">Criar parcelamento</div>

          <form method="POST" action="/financas/public/?url=lancamentos-parcelas-store">
            <div class="row g-2">

              <div class="col-6">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select" required>
                  <option value="D" selected>Despesa</option>
                  <option value="R">Receita</option>
                </select>
              </div>

              <div class="col-6">
                <label class="form-label">Parcelas</label>
                <input type="number" name="total_parcelas" class="form-control" min="2" value="2" required>
              </div>

              <div class="col-12">
                <label class="form-label">Conta</label>
                <select name="id_conta" class="form-select" required>
                  <option value="">Selecione</option>
                  <?php foreach ($contas as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Categoria</label>
                <select name="id_categoria" class="form-select">
                  <option value="">(Opcional)</option>
                  <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Valor total</label>
                <input name="valor_total" class="form-control input-money" placeholder="0,00" required>
              </div>

              <div class="col-12">
                <label class="form-label">Data da 1ª parcela</label>
                <input type="date" name="data_inicio" class="form-control" required>
              </div>

              <div class="col-12">
                <label class="form-label">Descrição</label>
                <input type="text" name="descricao" class="form-control" placeholder="Ex: Notebook, Curso...">
              </div>

              <div class="col-12">
                <div class="form-check mt-1">
                  <input class="form-check-input" type="checkbox" name="pagar_primeira" id="pagar_primeira" checked>
                  <label class="form-check-label" for="pagar_primeira">
                    Marcar 1ª parcela como <b>paga</b> agora (atualiza saldo)
                  </label>
                </div>
              </div>

            </div>

            <button class="btn btn-primary w-100 mt-3">Gerar parcelas</button>
          </form>
        </div>

        <!-- ===================== RECORRENTE ===================== -->
        <div class="tab-pane fade" id="tab-rec">
          <div class="section-title mb-3">Criar recorrência</div>

          <form method="POST" action="/financas/public/?url=recorrencias-store">
            <div class="row g-2">

              <div class="col-6">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select" required>
                  <option value="">Selecione</option>
                  <option value="R">Receita</option>
                  <option value="D">Despesa</option>
                </select>
              </div>

              <div class="col-6">
                <label class="form-label">Frequência</label>
                <select name="frequencia" class="form-select" id="freq" required>
                  <option value="mensal" selected>Mensal</option>
                  <option value="semanal">Semanal</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Conta</label>
                <select name="id_conta" class="form-select" required>
                  <option value="">Selecione</option>
                  <?php foreach ($contas as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Categoria</label>
                <select name="id_categoria" class="form-select">
                  <option value="">(Opcional)</option>
                  <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Valor</label>
                <input name="valor" class="form-control input-money" placeholder="0,00" required>
              </div>

              <div class="col-12" id="boxDiaMes">
                <label class="form-label">Dia do mês</label>
                <input type="number" name="dia_mes" class="form-control" min="1" max="28" value="5">
              </div>

              <div class="col-12 d-none" id="boxDiaSemana">
                <label class="form-label">Dia da semana</label>
                <select name="dia_semana" class="form-select">
                  <option value="1">Segunda</option>
                  <option value="2">Terça</option>
                  <option value="3">Quarta</option>
                  <option value="4">Quinta</option>
                  <option value="5">Sexta</option>
                  <option value="6">Sábado</option>
                  <option value="7">Domingo</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Descrição</label>
                <input type="text" name="descricao" class="form-control" placeholder="Ex: Aluguel, Academia...">
              </div>

            </div>

            <button class="btn btn-primary w-100 mt-3">Salvar recorrência</button>
          </form>

          <hr class="my-4">

          <div class="d-flex justify-content-between align-items-center">
            <strong>Gerar mês</strong>
            <span class="text-muted small">cria lançamentos sem duplicar</span>
          </div>

          <form method="POST" action="/financas/public/?url=recorrencias-gerar-mes" class="row g-2 mt-2">
            <div class="col-6">
              <input type="number" name="ano" class="form-control" value="<?= (int)date('Y') ?>" required>
            </div>
            <div class="col-6">
              <input type="number" name="mes" class="form-control" value="<?= (int)date('m') ?>" min="1" max="12" required>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="marcar_pago" id="marcar_pago" checked>
                <label class="form-check-label" for="marcar_pago">Gerar como <b>pago</b> (atualiza saldo)</label>
              </div>
            </div>
            <div class="col-12">
              <button class="btn btn-outline-primary w-100">Gerar lançamentos do mês</button>
            </div>
          </form>

          <?php if (!empty($recorrencias)): ?>
            <div class="mt-3 small text-muted">
              Recorrências ativas: <b><?= count($recorrencias) ?></b>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>


  </div>

  <!-- HISTÓRICO -->
  <div class="col-lg-8">
    <div class="card-soft p-4">

      <button class="btn btn-outline-secondary btn-sm mb-3"
              data-bs-toggle="collapse"
              data-bs-target="#filtrosLanc">
        Filtros
      </button>

      <div id="filtrosLanc" class="collapse mb-4">
        <form method="GET" class="row g-2">
          <input type="hidden" name="url" value="lancamentos">

          <div class="col-md-3">
            <label class="form-label">Data inicial</label>
            <input type="date" name="data_inicio" class="form-control" value="<?= $_GET['data_inicio'] ?? '' ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Data final</label>
            <input type="date" name="data_fim" class="form-control" value="<?= $_GET['data_fim'] ?? '' ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Conta</label>
            <select name="id_conta" class="form-select">
              <option value="">Todas</option>
              <?php foreach ($contas as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($_GET['id_conta'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Categoria</label>
            <select name="id_categoria" class="form-select">
              <option value="">Todas</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($_GET['id_categoria'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 d-flex gap-2 mt-2">
            <button class="btn btn-primary btn-sm">Filtrar</button>
            <a href="/financas/public/?url=lancamentos" class="btn btn-outline-secondary btn-sm">Limpar</a>
          </div>
        </form>
      </div>

      <!-- RESUMO -->
      <div class="row g-2 mb-3">
        <div class="col-md-4">
          <div class="kpi-mini p-3">
            <div class="lbl">Receitas</div>
            <div class="val text-success">R$ <?= number_format($totalReceitas,2,',','.') ?></div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="kpi-mini p-3">
            <div class="lbl">Despesas</div>
            <div class="val text-danger">R$ <?= number_format($totalDespesas,2,',','.') ?></div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="kpi-mini p-3">
            <div class="lbl">Saldo</div>
            <div class="val text-primary">R$ <?= number_format($totalReceitas - $totalDespesas,2,',','.') ?></div>
          </div>
        </div>
      </div>

      <!-- TABELA -->
      <div class="table-responsive-mobile">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Data</th>
              <th>Descrição</th>
              <th>Categoria</th>
              <th>Conta</th>
              <th>Status</th>
              <th class="text-end">Valor</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($lancamentos)): ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">Nenhum lançamento encontrado.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($lancamentos as $l): ?>
                <?php
                  $status = $l['status'] ?? 'pago';
                  $catNome = $l['categoria'] ?? '—';
                  if (!empty($l['transferencia_id'])) $catNome = 'Transferência';
                  if (!empty($l['parcelamento_id'])) $catNome = 'Parcelado';
                  if (!empty($l['recorrencia_id'])) $catNome = 'Recorrente';
                ?>
                <tr>
                  <td><?= date('d/m/Y', strtotime($l['data'])) ?></td>
                  <td><?= htmlspecialchars($l['descricao'] ?? '') ?></td>
                  <td><?= htmlspecialchars($catNome) ?></td>
                  <td><?= htmlspecialchars($l['conta'] ?? '') ?></td>

                  <td>
                    <?php if ($status === 'pago'): ?>
                      <span class="badge bg-success">pago</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">pendente</span>
                    <?php endif; ?>
                  </td>

                  <td class="text-end <?= $l['tipo']=='R'?'text-success':'text-danger' ?>">
                    <?= $l['tipo']=='R'?'+':'-' ?>
                    R$ <?= number_format((float)$l['valor'],2,',','.') ?>
                  </td>

                  <td class="text-end">
                    <a href="/financas/public/?url=lancamentos-toggle-status&id=<?= $l['id'] ?>"
                       class="btn btn-outline-primary btn-sm">
                      <?= $status === 'pago' ? 'Marcar pendente' : 'Marcar pago' ?>
                    </a>

                    <a href="/financas/public/?url=lancamentos-edit&id=<?= $l['id'] ?>"
                       class="btn btn-outline-secondary btn-sm">
                      Editar
                    </a>

                    <a href="/financas/public/?url=lancamentos-delete&id=<?= $l['id'] ?>"
                       class="btn btn-outline-danger btn-sm"
                       onclick="return confirm('Excluir este lançamento?')">
                      Excluir
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<style>
/* ====== TABLE MOBILE FIX (mantido) ====== */
.table-responsive-mobile { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.table-responsive-mobile table { min-width: 900px; }
</style>

<script>
/* toggle recorrência semanal/mensal (mantido) */
(function(){
  const freq = document.getElementById('freq');
  const boxDiaMes = document.getElementById('boxDiaMes');
  const boxDiaSemana = document.getElementById('boxDiaSemana');

  function sync(){
    if (!freq) return;
    if (freq.value === 'semanal') {
      boxDiaMes.classList.add('d-none');
      boxDiaSemana.classList.remove('d-none');
    } else {
      boxDiaSemana.classList.add('d-none');
      boxDiaMes.classList.remove('d-none');
    }
  }
  if (freq) {
    freq.addEventListener('change', sync);
    sync();
  }
})();

/* CHARTS */
document.addEventListener('DOMContentLoaded', function(){
  const hasPizza = document.getElementById('chartPizza');
  const hasBar = document.getElementById('chartBar');
  const hasLine = document.getElementById('chartLine');

  if (hasPizza){
    new Chart(hasPizza, {
      type:'doughnut',
      data:{
        labels: <?= json_encode($pizzaLabels) ?>,
        datasets:[{
          data: <?= json_encode($pizzaData) ?>,
          backgroundColor:['#4f46e5','#22c55e','#f59e0b','#ef4444','#06b6d4','#a855f7','#94a3b8']
        }]
      },
      options:{ cutout:'65%', plugins:{legend:{position:'bottom'}} }
    });
  }

  if (hasBar){
    new Chart(hasBar, {
      type:'bar',
      data:{
        labels: <?= json_encode($barLabels) ?>,
        datasets:[
          { label:'Receitas', data: <?= json_encode($barReceitas) ?>, backgroundColor:'#22c55e' },
          { label:'Despesas', data: <?= json_encode($barDespesas) ?>, backgroundColor:'#ef4444' }
        ]
      },
      options:{
        responsive:true,
        plugins:{ legend:{position:'bottom'} },
        scales:{ x:{ stacked:false }, y:{ beginAtZero:true } }
      }
    });
  }

  if (hasLine){
    new Chart(hasLine, {
      type:'line',
      data:{
        labels: <?= json_encode($linhaLabels) ?>,
        datasets:[
          { label:'Receitas', data: <?= json_encode($linhaReceitas) ?>, borderColor:'#22c55e', tension:.35 },
          { label:'Despesas', data: <?= json_encode($linhaDespesas) ?>, borderColor:'#ef4444', tension:.35 }
        ]
      },
      options:{
        responsive:true,
        plugins:{ legend:{position:'bottom'} }
      }
    });
  }
});
</script>
