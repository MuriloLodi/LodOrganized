<h1 class="mb-4">Lançamentos</h1>

<div class="row">
    <!-- FORM -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5>Novo lançamento</h5>

                <?php if (!empty($_SESSION['erro'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/financas/public/?url=lancamentos-store">
                    <div class="mb-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Selecione</option>
                            <option value="R">Receita</option>
                            <option value="D">Despesa</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Conta</label>
                        <select name="id_conta" class="form-select" required>
                            <option value="">Selecione</option>
                            <?php foreach ($contas as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    <?= htmlspecialchars($c['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Categoria</label>
                        <select name="id_categoria" class="form-select" required>
                            <option value="">Selecione</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Valor</label>
                        <input type="number" step="0.01" name="valor" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Data</label>
                        <input type="date" name="data" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" name="descricao" class="form-control">
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
                <h5>Histórico</h5>

                <?php if (empty($lancamentos)): ?>
                    <p class="text-muted">Nenhum lançamento registrado.</p>
                <?php else: ?>
                    <form method="GET" class="card card-body mb-4">
    <input type="hidden" name="url" value="lancamentos">

    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Data inicial</label>
            <input type="date" name="data_inicio" class="form-control"
                   value="<?= $_GET['data_inicio'] ?? '' ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Data final</label>
            <input type="date" name="data_fim" class="form-control"
                   value="<?= $_GET['data_fim'] ?? '' ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Conta</label>
            <select name="id_conta" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($contas as $c): ?>
                    <option value="<?= $c['id'] ?>"
                        <?= ($_GET['id_conta'] ?? '') == $c['id'] ? 'selected' : '' ?>>
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
                    <option value="<?= $cat['id'] ?>"
                        <?= ($_GET['id_categoria'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary">
            Filtrar
        </button>

        <a href="/financas/public/?url=lancamentos"
           class="btn btn-outline-secondary">
            Limpar
        </a>
    </div>
</form>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Receitas x Despesas</h6>
                <canvas id="graficoTipo"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Por categoria</h6>
                <canvas id="graficoCategoria"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
$totalReceitas = 0;
$totalDespesas = 0;

foreach ($lancamentos as $l) {
    if ($l['tipo'] === 'R') {
        $totalReceitas += $l['valor'];
    } else {
        $totalDespesas += $l['valor'];
    }
}
?>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="alert alert-success">
            Receitas: R$ <?= number_format($totalReceitas, 2, ',', '.') ?>
        </div>
    </div>

    <div class="col-md-4">
        <div class="alert alert-danger">
            Despesas: R$ <?= number_format($totalDespesas, 2, ',', '.') ?>
        </div>
    </div>

    <div class="col-md-4">
        <div class="alert alert-primary">
            Saldo: R$ <?= number_format($totalReceitas - $totalDespesas, 2, ',', '.') ?>
        </div>
    </div>
</div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                            <th>Conta</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lancamentos as $l): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($l['data'])) ?></td>
                                <td><?= htmlspecialchars($l['descricao']) ?></td>
                                <td><?= htmlspecialchars($l['categoria']) ?></td>
                                <td><?= htmlspecialchars($l['conta']) ?></td>
                                <td class="<?= $l['tipo'] === 'R' ? 'text-success' : 'text-danger' ?>">
                                    <?= $l['tipo'] === 'R' ? '+' : '-' ?>
                                    R$ <?= number_format($l['valor'], 2, ',', '.') ?>
                                </td>
                                <td>
    <a href="/financas/public/?url=lancamentos-edit&id=<?= $l['id'] ?>"
       class="btn btn-sm btn-warning">Editar</a>

    <a href="/financas/public/?url=lancamentos-delete&id=<?= $l['id'] ?>"
       class="btn btn-sm btn-danger"
       onclick="return confirm('Excluir este lançamento?')">
       Excluir
    </a>
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
<script>
const dadosTipo = {
    receitas: <?= json_encode(
        array_sum(array_column(
            array_filter($resumoTipo, fn($r) => $r['tipo'] === 'R'),
            'total'
        ))
    ) ?>,
    despesas: <?= json_encode(
        array_sum(array_column(
            array_filter($resumoTipo, fn($r) => $r['tipo'] === 'D'),
            'total'
        ))
    ) ?>
};

new Chart(document.getElementById('graficoTipo'), {
    type: 'bar',
    data: {
        labels: ['Receitas', 'Despesas'],
        datasets: [{
            data: [dadosTipo.receitas, dadosTipo.despesas],
            backgroundColor: ['#198754', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>
<script>
const categorias = <?= json_encode(array_column($resumoCategoria, 'nome')) ?>;
const valores = <?= json_encode(array_column($resumoCategoria, 'total')) ?>;

new Chart(document.getElementById('graficoCategoria'), {
    type: 'pie',
    data: {
        labels: categorias,
        datasets: [{
            data: valores
        }]
    },
    options: {
        responsive: true
    }
});
</script>
