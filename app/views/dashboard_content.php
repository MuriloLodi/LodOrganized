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
