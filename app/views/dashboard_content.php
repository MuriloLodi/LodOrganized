<?php
$dt = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

if (class_exists('IntlDateFormatter')) {
    $fmt = new IntlDateFormatter(
        'pt_BR',
        IntlDateFormatter::NONE,
        IntlDateFormatter::NONE,
        $dt->getTimezone()->getName(),
        IntlDateFormatter::GREGORIAN,
        "MMMM 'de' yyyy"
    );

    $mesAno = $fmt->format($dt);

    // "janeiro de 2026" -> "Janeiro de 2026"
    if (function_exists('mb_convert_case')) {
        $mesAno = mb_convert_case($mesAno, MB_CASE_TITLE, 'UTF-8');
    } else {
        $mesAno = ucfirst($mesAno);
    }
} else {
    // fallback caso não exista ext-intl
    $meses = [1=>'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $mesAno = $meses[(int)$dt->format('n')] . ' de ' . $dt->format('Y');
}
?>

<?php
$receitas = (float)($resumo['receitas'] ?? 0);
$despesas = (float)($resumo['despesas'] ?? 0);
$saldoMes = (float)($resumo['saldo'] ?? ($receitas - $despesas));

$receitasAnt = (float)($resumoAnt['receitas'] ?? 0);
$despesasAnt = (float)($resumoAnt['despesas'] ?? 0);
$saldoAnt = (float)($resumoAnt['saldo'] ?? ($receitasAnt - $despesasAnt));

function deltaBadge($atual, $ant) {
    $d = $atual - $ant;
    if (abs($d) < 0.01) return '<span class="badge bg-light text-dark">=</span>';
    return '<span class="badge '.($d>0?'bg-success':'bg-danger').'">'.($d>0?'+':'-').' R$ '.number_format(abs($d),2,',','.').'</span>';
}

$orcado = (float)($orcamentoGeral['orcado'] ?? 0);
$real   = (float)($orcamentoGeral['real'] ?? 0);
$perc   = (float)($orcamentoGeral['percentual'] ?? 0);

$orcClass = 'bg-success';
if ($perc > 70) $orcClass = 'bg-warning';
if ($perc > 100) $orcClass = 'bg-danger';
?>

<style>
/* === BASE === */
.dashboard-wrap {
    max-width: 1400px;
    margin: auto;
}
.soft-card {
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 15px 35px rgba(0,0,0,.08);
    border: none;
}
.kpi {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.kpi-title {
    font-size: 12px;
    text-transform: uppercase;
    color: #6c757d;
    letter-spacing: .6px;
}
.kpi-value {
    font-size: 34px;
    font-weight: 800;
}
.kpi-sub {
    font-size: 13px;
    color: #6c757d;
}
.progress-xl {
    height: 32px;
    border-radius: 20px;
    overflow: hidden;
}
.progress-xl .progress-bar {
    font-size: 14px;
    font-weight: 600;
}
.section-title {
    font-weight: 700;
    font-size: 18px;
}
.list-row {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}
@media (max-width: 768px) {
    .kpi-value { font-size: 26px; }
    .btn1 { width: 100%; }
}
</style>

<div class="dashboard-wrap">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="fw-bold mb-1">Resumo financeiro</h1>
        <div class="text-muted">
            <?= htmlspecialchars($mesAno) ?>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn1 btn-primary btn-lg" href="/financas/public/?url=lancamentos">+ Lançamento</a>
        <a class="btn btn1 btn-outline-secondary" href="/financas/public/?url=orcamentos">Orçamentos</a>
        <a class="btn btn1 btn-dark" href="/financas/public/?url=relatorio-pdf-executivo">PDF</a>
    </div>
</div>

<!-- ALERTAS -->
<?php foreach (($alertas ?? []) as $a): ?>
    <div class="alert alert-<?= $a['tipo'] ?> fw-semibold">
        <?= htmlspecialchars($a['msg']) ?>
    </div>
<?php endforeach; ?>

<!-- KPIs -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="soft-card p-4">
            <div class="kpi">
                <div class="kpi-title">Receitas</div>
                <div class="kpi-value text-success">R$ <?= number_format($receitas,2,',','.') ?></div>
                <div class="kpi-sub"><?= deltaBadge($receitas,$receitasAnt) ?> vs mês anterior</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="soft-card p-4">
            <div class="kpi">
                <div class="kpi-title">Despesas</div>
                <div class="kpi-value text-danger">R$ <?= number_format($despesas,2,',','.') ?></div>
                <div class="kpi-sub"><?= deltaBadge($despesas,$despesasAnt) ?> vs mês anterior</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="soft-card p-4">
            <div class="kpi">
                <div class="kpi-title">Saldo</div>
                <div class="kpi-value <?= $saldoMes>=0?'text-primary':'text-danger' ?>">
                    R$ <?= number_format($saldoMes,2,',','.') ?>
                </div>
                <div class="kpi-sub"><?= deltaBadge($saldoMes,$saldoAnt) ?> vs mês anterior</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="soft-card p-4">
            <div class="kpi">
                <div class="kpi-title">Orçamento</div>
                <div class="kpi-value"><?= number_format($perc,1) ?>%</div>
                <div class="progress progress-xl mt-2">
                    <div class="progress-bar <?= $orcClass ?>" style="width:<?= min($perc,100) ?>%">
                        <?= number_format($perc,1) ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ORÇAMENTO GERAL -->
<div class="soft-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="section-title">Consumo do orçamento</div>
        <a href="/financas/public/?url=orcamentos">Ver detalhes</a>
    </div>

    <?php if ($orcado > 0): ?>
        <div class="progress progress-xl">
            <div class="progress-bar <?= $orcClass ?>" style="width:<?= min($perc,100) ?>%">
                <?= number_format($perc,1) ?>%
            </div>
        </div>
        <div class="d-flex justify-content-between text-muted mt-2">
            <span>Gasto: <strong>R$ <?= number_format($real,2,',','.') ?></strong></span>
            <span>Orçado: <strong>R$ <?= number_format($orcado,2,',','.') ?></strong></span>
        </div>
    <?php else: ?>
        <div class="alert alert-info mb-0">Nenhum orçamento definido.</div>
    <?php endif; ?>
</div>

<!-- LISTAS -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="soft-card p-4 h-100">
            <div class="section-title mb-3">Categorias em risco</div>

            <?php if (empty($categoriasRisco)): ?>
                <div class="text-muted">Tudo sob controle 🎯</div>
            <?php else: ?>
                <?php foreach ($categoriasRisco as $c): ?>
                    <div class="list-row d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($c['nome']) ?></strong><br>
                            <small class="text-muted">
                                R$ <?= number_format($c['total_real'],2,',','.') ?>
                                / R$ <?= number_format($c['orcado'],2,',','.') ?>
                            </small>
                        </div>
                        <span class="badge <?= $c['status']==='danger'?'bg-danger':'bg-warning text-dark' ?>">
                            <?= number_format($c['percentual'],1) ?>%
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="soft-card p-4 h-100">
            <div class="section-title mb-3">Maiores despesas</div>

            <?php if (empty($topDespesas)): ?>
                <div class="text-muted">Sem despesas neste mês.</div>
            <?php else: ?>
                <?php
                $max = max(array_column($topDespesas,'total')) ?: 1;
                foreach ($topDespesas as $t):
                    $w = min(($t['total']/$max)*100,100);
                ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><?= htmlspecialchars($t['categoria']) ?></span>
                            <strong>R$ <?= number_format($t['total'],2,',','.') ?></strong>
                        </div>
                        <div class="progress" style="height:10px">
                            <div class="progress-bar" style="width:<?= $w ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>
