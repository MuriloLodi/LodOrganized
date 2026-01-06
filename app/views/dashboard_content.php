<?php
$receitas = (float)($resumo['receitas'] ?? 0);
$despesas = (float)($resumo['despesas'] ?? 0);
$saldoMes = (float)($resumo['saldo'] ?? ($receitas - $despesas));

$receitasAnt = (float)($resumoAnt['receitas'] ?? 0);
$despesasAnt = (float)($resumoAnt['despesas'] ?? 0);
$saldoAnt = (float)($resumoAnt['saldo'] ?? ($receitasAnt - $despesasAnt));

function badgeDelta($atual, $anterior) {
    $diff = $atual - $anterior;
    if (abs($diff) < 0.01) return '<span class="badge bg-secondary">igual</span>';
    $cls = $diff > 0 ? 'bg-success' : 'bg-danger';
    $sinal = $diff > 0 ? '+' : '-';
    return '<span class="badge '.$cls.'">'.$sinal.' R$ '.number_format(abs($diff), 2, ',', '.').'</span>';
}

$orcadoTotal = (float)($orcamentoGeral['orcado'] ?? 0);
$realTotal   = (float)($orcamentoGeral['real'] ?? 0);
$percentOrc  = (float)($orcamentoGeral['percentual'] ?? 0);

$classeOrc = 'bg-secondary';
if ($orcadoTotal > 0) {
    if ($percentOrc <= 70) $classeOrc = 'bg-success';
    elseif ($percentOrc <= 100) $classeOrc = 'bg-warning';
    else $classeOrc = 'bg-danger';
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="mb-0">Dashboard</h1>
        <div class="text-muted">Visão geral do mês atual</div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-primary" href="/financas/public/?url=lancamentos">+ Lançamento</a>
        <a class="btn btn-outline-secondary" href="/financas/public/?url=orcamentos">Ajustar Orçamento</a>
        <a class="btn btn-outline-success" href="/financas/public/?url=relatorio-csv&ano=<?= date('Y') ?>&mes=<?= date('m') ?>">CSV</a>
        <a class="btn btn-dark" href="/financas/public/?url=relatorio-pdf-executivo&ano=<?= date('Y') ?>&mes=<?= date('m') ?>">PDF Executivo</a>
    </div>
</div>

<!-- ALERTAS (TOP PRIORIDADE) -->
<?php if (!empty($alertas)): ?>
    <?php foreach ($alertas as $a): ?>
        <div class="alert alert-<?= $a['tipo'] ?> fw-bold">
            <?= htmlspecialchars($a['msg']) ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- CARDS EXECUTIVOS -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-success">
            <div class="card-body">
                <div class="text-muted small">Receitas (mês)</div>
                <div class="display-6 text-success">R$ <?= number_format($receitas, 2, ',', '.') ?></div>
                <div class="mt-2"><?= badgeDelta($receitas, $receitasAnt) ?> <span class="text-muted small">vs mês anterior</span></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 border-danger">
            <div class="card-body">
                <div class="text-muted small">Despesas (mês)</div>
                <div class="display-6 text-danger">R$ <?= number_format($despesas, 2, ',', '.') ?></div>
                <div class="mt-2"><?= badgeDelta($despesas, $despesasAnt) ?> <span class="text-muted small">vs mês anterior</span></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 border-primary">
            <div class="card-body">
                <div class="text-muted small">Saldo (mês)</div>
                <div class="display-6 <?= $saldoMes >= 0 ? 'text-primary' : 'text-danger' ?>">
                    R$ <?= number_format($saldoMes, 2, ',', '.') ?>
                </div>
                <div class="mt-2"><?= badgeDelta($saldoMes, $saldoAnt) ?> <span class="text-muted small">vs mês anterior</span></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 border-warning">
            <div class="card-body">
                <div class="text-muted small">Orçamento (mês)</div>
                <div class="display-6"><?= number_format($percentOrc, 1) ?>%</div>
                <div class="text-muted small">
                    <?= $orcadoTotal > 0
                        ? 'R$ '.number_format($realTotal,2,',','.').' / R$ '.number_format($orcadoTotal,2,',','.')
                        : 'Defina orçamentos para visualizar' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BARRA GERAL DO ORÇAMENTO (VISUAL PRIORITÁRIO) -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Consumo do orçamento (mês)</strong>
            <a class="small text-decoration-none" href="/financas/public/?url=orcamentos">Ver detalhes</a>
        </div>

        <?php if ($orcadoTotal > 0): ?>
            <div class="progress" style="height: 26px;">
                <div class="progress-bar <?= $classeOrc ?>" style="width: <?= min($percentOrc, 100) ?>%">
                    <?= number_format($percentOrc, 1) ?>%
                </div>
            </div>
            <div class="d-flex justify-content-between mt-2 text-muted small">
                <div>Gasto: <strong>R$ <?= number_format($realTotal, 2, ',', '.') ?></strong></div>
                <div>Orçado: <strong>R$ <?= number_format($orcadoTotal, 2, ',', '.') ?></strong></div>
            </div>
            <?php if ($percentOrc > 100): ?>
                <div class="mt-2 text-danger fw-bold">⚠️ Você ultrapassou o orçamento do mês.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info mb-0">
                Defina orçamentos mensais para ver a barra geral.
                <a href="/financas/public/?url=orcamentos" class="alert-link">Configurar orçamento</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <!-- CATEGORIAS EM RISCO -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Categorias em risco</strong>
                    <a class="small text-decoration-none" href="/financas/public/?url=orcamentos">Ajustar</a>
                </div>

                <?php if (empty($categoriasRisco)): ?>
                    <div class="text-muted">
                        Nenhuma categoria em risco agora. ✅
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach (array_slice($categoriasRisco, 0, 6) as $c): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($c['nome']) ?>
                                        <?php if ($c['status'] === 'danger'): ?>
                                            <span class="badge bg-danger ms-2">estourado</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark ms-2">80%+</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small">
                                        R$ <?= number_format($c['total_real'],2,',','.') ?> / R$ <?= number_format($c['orcado'],2,',','.') ?>
                                    </div>
                                </div>
                                <span class="badge <?= $c['status']==='danger'?'bg-danger':'bg-warning text-dark' ?>">
                                    <?= number_format($c['percentual'],1) ?>%
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TOP GASTOS -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Top gastos do mês</strong>
                    <a class="small text-decoration-none" href="/financas/public/?url=lancamentos">Ver lançamentos</a>
                </div>

                <?php if (empty($topDespesas)): ?>
                    <div class="text-muted">Sem despesas registradas neste mês.</div>
                <?php else: ?>
                    <?php
                        $maxTop = (float)($topDespesas[0]['total'] ?? 1);
                        if ($maxTop <= 0) $maxTop = 1;
                    ?>
                    <?php foreach ($topDespesas as $t): ?>
                        <?php
                            $nome = $t['categoria'] ?? '—';
                            $total = (float)($t['total'] ?? 0);
                            $w = min(($total / $maxTop) * 100, 100);
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <div class="fw-semibold"><?= htmlspecialchars($nome) ?></div>
                                <div>R$ <?= number_format($total,2,',','.') ?></div>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" style="width: <?= $w ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <a class="btn btn-outline-primary btn-sm" href="/financas/public/?url=lancamentos">Adicionar despesa</a>
                    <a class="btn btn-outline-secondary btn-sm" href="/financas/public/?url=categorias">Gerenciar categorias</a>
                    <a class="btn btn-outline-secondary btn-sm" href="/financas/public/?url=contas">Gerenciar contas</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EVOLUÇÃO (GRÁFICO) -->
<div class="card mt-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Evolução no ano (<?= (int)date('Y') ?>)</strong>
            <span class="text-muted small">Receitas x Despesas</span>
        </div>

        <canvas id="chartEvolucao" height="90"></canvas>
    </div>
</div>

<script>
(function(){
    const linha = <?= json_encode($linhaMensal ?? []) ?>;

    // Espera o formato típico: [{mes:1, receitas:..., despesas:...}, ...]
    const labels = linha.map(x => {
        const m = (x.mes ?? x['mes'] ?? '');
        return String(m).padStart(2,'0');
    });

    const receitas = linha.map(x => Number(x.receitas ?? x['receitas'] ?? 0));
    const despesas = linha.map(x => Number(x.despesas ?? x['despesas'] ?? 0));

    const ctx = document.getElementById('chartEvolucao');
    if (!ctx || typeof Chart === 'undefined') return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'Receitas', data: receitas, tension: 0.25 },
                { label: 'Despesas', data: despesas, tension: 0.25 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true } }
        }
    });
})();
</script>
