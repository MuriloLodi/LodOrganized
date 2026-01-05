<h1 class="mb-4">Dashboard</h1>

<p class="text-muted">
    Resumo de <?= date('m/Y') ?>
</p>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Saldo do mês</h6>
                <h3 class="<?= $resumo['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                    R$ <?= number_format($resumo['saldo'], 2, ',', '.') ?>
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Receitas</h6>
                <h3 class="text-success">
                    R$ <?= number_format($resumo['receitas'], 2, ',', '.') ?>
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Despesas</h6>
                <h3 class="text-danger">
                    R$ <?= number_format($resumo['despesas'], 2, ',', '.') ?>
                </h3>
            </div>
        </div>
    </div>
</div>
<hr class="my-4">

<h5 class="mb-3">Visão geral</h5>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Saldo total</small>
                <h4 class="<?= $saldoGeral >= 0 ? 'text-success' : 'text-danger' ?>">
                    R$ <?= number_format($saldoGeral, 2, ',', '.') ?>
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Resultado do mês</small>
                <h4 class="<?= $resumo['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                    R$ <?= number_format($resumo['saldo'], 2, ',', '.') ?>
                </h4>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h6 class="mb-3">Evolução financeira (<?= date('Y') ?>)</h6>
        <canvas id="graficoExecutivo"></canvas>
    </div>
</div>
<?php
$receitas = array_fill(0, 12, 0);
$despesas = array_fill(0, 12, 0);

foreach ($linhaMensal as $r) {
    $i = $r['mes'] - 1;
    if ($r['tipo'] === 'R') {
        $receitas[$i] = $r['total'];
    } else {
        $despesas[$i] = $r['total'];
    }
}

$saldoAcumulado = [];
$saldo = 0;
for ($i = 0; $i < 12; $i++) {
    $saldo += $receitas[$i] - $despesas[$i];
    $saldoAcumulado[] = $saldo;
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const elExec = document.getElementById('graficoExecutivo');
if (elExec) {
    new Chart(elExec, {
        type: 'line',
        data: {
            labels: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
            datasets: [{
                label: 'Saldo acumulado',
                data: <?= json_encode($saldoAcumulado) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.15)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}
</script>
