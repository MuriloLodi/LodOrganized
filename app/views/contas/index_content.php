<h1 class="mb-4">Contas financeiras</h1>

<div class="row">
    <!-- FORM -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5>Nova conta</h5>

                <?php if (!empty($_SESSION['erro'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/financas/public/?url=contas-store">
                    <div class="mb-3">
                        <label class="form-label">Nome da conta</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Saldo inicial</label>
                        <input type="number" step="0.01" name="saldo" class="form-control" value="0">
                    </div>

                    <button class="btn btn-primary w-100">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- LISTA -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Minhas contas</h5>

                <?php if (empty($contas)): ?>
                    <p class="text-muted">Nenhuma conta cadastrada.</p>
                <?php else: ?>
                    <table class="table table-striped">
    <thead>
    <tr>
        <th>Conta</th>
        <th>Saldo atual</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($contas as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['nome']) ?></td>
            <td class="<?= $c['saldo_atual'] >= 0 ? 'text-success' : 'text-danger' ?>">
                R$ <?= number_format($c['saldo_atual'], 2, ',', '.') ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
