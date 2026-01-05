<?php if (!empty($orcamentosEstourados)): ?>
<div class="alert alert-danger">
    <strong>⚠️ Orçamento estourado!</strong>
    <ul class="mb-0">
        <?php foreach ($orcamentosEstourados as $o): ?>
            <li>
                <?= htmlspecialchars($o['nome']) ?> —
                <?= number_format($o['percentual'], 1) ?>%
                (R$ <?= number_format($o['total_real'], 2, ',', '.') ?> /
                 R$ <?= number_format($o['orcado'], 2, ',', '.') ?>)
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="/financas/public/?url=orcamentos&ano=<?= date('Y') ?>&mes=<?= date('m') ?>"
       class="btn btn-sm btn-light mt-2">
       Ver orçamento
    </a>
</div>
<?php endif; ?>
<?php if (!empty($orcamentosPreventivo)): ?>
<div class="alert alert-warning">
    <strong>⚠️ Atenção!</strong> Você está perto de estourar o orçamento em:
    <ul class="mb-0">
        <?php foreach ($orcamentosPreventivo as $o): ?>
            <li>
                <?= htmlspecialchars($o['nome']) ?> —
                <?= number_format($o['percentual'], 1) ?>%
                (R$ <?= number_format($o['total_real'], 2, ',', '.') ?> /
                R$ <?= number_format($o['orcado'], 2, ',', '.') ?>)
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>


<h1 class="mb-4">Dashboard</h1>
<?php if ($orcamentoGeral['orcado'] > 0): ?>
<?php
    $p = $orcamentoGeral['percentual'];

    if ($p <= 70) {
        $classe = 'bg-success';
    } elseif ($p <= 100) {
        $classe = 'bg-warning';
    } else {
        $classe = 'bg-danger';
    }

    $barra = min($p, 100);
?>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">📊 Orçamento geral do mês</h5>

        <div class="progress" style="height: 26px">
            <div class="progress-bar <?= $classe ?>"
                 style="width: <?= $barra ?>%">
                <?= number_format($p, 1) ?>%
            </div>
        </div>

        <div class="d-flex justify-content-between mt-2">
            <small>
                Gasto: <strong>R$ <?= number_format($orcamentoGeral['real'], 2, ',', '.') ?></strong>
            </small>
            <small>
                Orçado: <strong>R$ <?= number_format($orcamentoGeral['orcado'], 2, ',', '.') ?></strong>
            </small>
        </div>

        <?php if ($p > 100): ?>
            <div class="mt-2 text-danger fw-bold">
                ⚠️ Você ultrapassou o orçamento do mês!
            </div>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="alert alert-info mb-4">
    Defina orçamentos mensais para visualizar o consumo geral do mês.
    <a href="/financas/public/?url=orcamentos" class="alert-link">Configurar orçamento</a>
</div>
<?php endif; ?>

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
