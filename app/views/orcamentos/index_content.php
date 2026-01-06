<?php
// ===== Helpers / dados =====
$meses = [
  1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
  5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
  9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

$mesInt = (int)$mes;
$anoInt = (int)$ano;

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
    if ($percentGeral <= 70) { $classeGeral = 'bg-success'; $textoGeral = 'Saudável'; }
    elseif ($percentGeral <= 100) { $classeGeral = 'bg-warning'; $textoGeral = 'Atenção'; }
    else { $classeGeral = 'bg-danger'; $textoGeral = 'Estourado'; }
}

// ícones simples por nome (opcional, só pra ficar mais bonito)
function iconeCategoria($nome) {
    $n = mb_strtolower($nome ?? '');
    if (str_contains($n, 'aliment') || str_contains($n, 'mercad') || str_contains($n, 'rest')) return 'bi-cup-hot';
    if (str_contains($n, 'casa') || str_contains($n, 'alug') || str_contains($n, 'luz') || str_contains($n, 'água') || str_contains($n, 'agua')) return 'bi-house';
    if (str_contains($n, 'trans') || str_contains($n, 'uber') || str_contains($n, 'comb') || str_contains($n, 'gas')) return 'bi-car-front';
    if (str_contains($n, 'saúde') || str_contains($n, 'saude') || str_contains($n, 'farm')) return 'bi-heart-pulse';
    if (str_contains($n, 'educ') || str_contains($n, 'curso')) return 'bi-mortarboard';
    if (str_contains($n, 'lazer') || str_contains($n, 'netflix') || str_contains($n, 'assin')) return 'bi-controller';
    return 'bi-tag';
}
?>

<style>
/* ====== TABLE MOBILE FIX ====== */
.table-responsive-mobile {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.table-responsive-mobile table {
    min-width: 900px; /* força largura mínima */
}

/* ====== Orcamento UI ====== */
.page-header-sub { color: #6c757d; }
.card-soft {
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 16px;
}
.kpi {
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 16px;
    background: #fff;
}
.kpi .label { color: #6c757d; font-size: .85rem; }
.kpi .value { font-size: 1.35rem; font-weight: 800; }
.kpi .hint { color: #6c757d; font-size: .85rem; }
.badge-soft {
    border-radius: 999px;
    font-weight: 700;
}
.input-money { min-width: 130px; }
.progress { background: rgba(0,0,0,.06); }
@media (max-width: 991px) {
    .input-money { min-width: 120px; }
}
</style>

<div class="d-flex justify-content-between align-items-start align-items-md-center gap-3 mb-4 flex-wrap">
    <div>
        <h1 class="mb-1">Orçamento mensal</h1>
        <div class="page-header-sub">
            Controle por categoria • <?= $meses[$mesInt] ?? '—' ?>/<?= $anoInt ?>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-secondary" href="/financas/public/?url=dashboard">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
        <a class="btn btn-outline-secondary" href="/financas/public/?url=lancamentos">
            <i class="bi bi-arrow-left-right me-1"></i> Lançamentos
        </a>
        <a class="btn btn-primary" href="/financas/public/?url=categorias">
            <i class="bi bi-tags me-1"></i> Categorias
        </a>
    </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
    </div>
<?php endif; ?>

<!-- FILTRO -->
<div class="card card-soft mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET" action="/financas/public/">
            <input type="hidden" name="url" value="orcamentos">

            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label mb-1">Mês</label>
                <select name="mes" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= ($m == $mesInt) ? 'selected' : '' ?>>
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
                <button class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                <a class="btn btn-outline-secondary w-100"
                   href="/financas/public/?url=orcamentos&ano=<?= date('Y') ?>&mes=<?= date('m') ?>">
                    Hoje
                </a>
            </div>
        </form>
    </div>
</div>

<!-- RESUMO DO MÊS -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="kpi p-3 h-100">
            <div class="label mb-1">Orçado (total)</div>
            <div class="value">R$ <?= number_format($orcadoGeral, 2, ',', '.') ?></div>
            <div class="hint">Soma das categorias com orçamento</div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="kpi p-3 h-100">
            <div class="label mb-1">Real (gasto)</div>
            <div class="value">R$ <?= number_format($realGeral, 2, ',', '.') ?></div>
            <div class="hint">Despesas registradas no mês</div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="kpi p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="label mb-1">Consumo do orçamento</div>
                    <div class="value"><?= number_format($percentGeral, 1) ?>%</div>
                </div>
                <span class="badge badge-soft <?= $classeGeral ?> <?= $classeGeral === 'bg-warning' ? 'text-dark' : '' ?>">
                    <?= $textoGeral ?>
                </span>
            </div>

            <?php if ($orcadoGeral > 0): ?>
                <div class="progress mt-2" style="height: 14px;">
                    <div class="progress-bar <?= $classeGeral ?>" style="width: <?= min($percentGeral, 100) ?>%"></div>
                </div>
                <?php if ($percentGeral > 100): ?>
                    <div class="hint text-danger mt-2 fw-semibold">
                        <i class="bi bi-exclamation-triangle me-1"></i> Você ultrapassou o orçamento total do mês.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="hint mt-2">
                    Defina um valor em pelo menos 1 categoria para aparecer o consumo geral.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- LISTA -->
<div class="card card-soft">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-semibold">Categorias de despesa</h5>
                <div class="text-muted small">Ajuste o orçamento por categoria e acompanhe o consumo</div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary btn-sm" href="/financas/public/?url=categorias">
                    <i class="bi bi-plus-circle me-1"></i> Criar categoria
                </a>
                <a class="btn btn-outline-secondary btn-sm" href="/financas/public/?url=lancamentos">
                    <i class="bi bi-plus-circle me-1"></i> Lançar despesa
                </a>
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
                            <th style="min-width:320px">Ajustar</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($categoriasDespesa as $cat): ?>
                        <?php
                            $idCat = (int)$cat['id'];
                            $orcado = (float)($orcamentosMap[$idCat] ?? 0);
                            $real   = (float)($gastosReais[$idCat] ?? 0);

                            $percentual = ($orcado > 0) ? ($real / $orcado) * 100 : 0;

                            if ($orcado <= 0) {
                                $classe = 'bg-secondary';
                                $label = 'Sem orçamento';
                            } elseif ($percentual <= 70) {
                                $classe = 'bg-success';
                                $label = 'Saudável';
                            } elseif ($percentual <= 100) {
                                $classe = 'bg-warning';
                                $label = 'Atenção';
                            } else {
                                $classe = 'bg-danger';
                                $label = 'Estourado';
                            }

                            $barra = min($percentual, 100);
                            $icone = iconeCategoria($cat['nome'] ?? '');
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-3 d-inline-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;background:rgba(13,110,253,.10);color:#0d6efd;">
                                        <i class="bi <?= $icone ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($cat['nome']) ?></div>
                                        <div class="text-muted small">
                                            <span class="badge <?= $classe ?> <?= $classe==='bg-warning' ? 'text-dark' : '' ?>">
                                                <?= $label ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="fw-semibold">
                                R$ <?= number_format($orcado, 2, ',', '.') ?>
                            </td>

                            <td class="<?= $real > 0 ? 'fw-semibold' : 'text-muted' ?>">
                                R$ <?= number_format($real, 2, ',', '.') ?>
                            </td>

                            <td>
                                <div class="progress" style="height: 18px;">
                                    <div class="progress-bar <?= $classe ?>"
                                         style="width: <?= $barra ?>%">
                                        <?= ($orcado > 0) ? number_format($percentual, 1) . '%' : '—' ?>
                                    </div>
                                </div>
                                <div class="text-muted small mt-1 d-flex justify-content-between">
                                    <span>Gasto: <b>R$ <?= number_format($real, 2, ',', '.') ?></b></span>
                                    <span>Meta: <b>R$ <?= number_format($orcado, 2, ',', '.') ?></b></span>
                                </div>
                                <?php if ($orcado > 0 && $percentual > 100): ?>
                                    <div class="text-danger small fw-semibold mt-1">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Ultrapassou o orçamento
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <form method="POST"
                                      action="/financas/public/?url=orcamentos-store"
                                      class="d-flex gap-2 align-items-center flex-wrap">
                                    <input type="text"
                                           name="valor"
                                           value="<?= number_format($orcado, 2, ',', '.') ?>"
                                           class="form-control form-control-sm input-money"
                                           placeholder="0,00">

                                    <input type="hidden" name="id_categoria" value="<?= $idCat ?>">
                                    <input type="hidden" name="ano" value="<?= $anoInt ?>">
                                    <input type="hidden" name="mes" value="<?= $mesInt ?>">

                                    <button class="btn btn-sm btn-primary">
                                        <i class="bi bi-check2 me-1"></i> Salvar
                                    </button>

                                    <?php if ($orcado > 0): ?>
                                        <button class="btn btn-sm btn-outline-secondary"
                                                type="button"
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
                Dica: se você não definir orçamento (0,00), a categoria não entra no cálculo do consumo total.
            </div>

        <?php endif; ?>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
