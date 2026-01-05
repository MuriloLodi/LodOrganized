<h1 class="mb-4">Orçamento mensal</h1>

<?php if (!empty($_SESSION['erro'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
    </div>
<?php endif; ?>

<?php
$meses = [
  1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
  5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
  9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
?>

<form class="row g-2 mb-4" method="GET" action="/financas/public/">
    <input type="hidden" name="url" value="orcamentos">

    <div class="col-md-3">
        <select name="mes" class="form-select">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= ($m == (int)$mes) ? 'selected' : '' ?>>
                    <?= $meses[$m] ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="col-md-2">
        <input type="number" name="ano" value="<?= (int)$ano ?>" class="form-control">
    </div>

    <div class="col-md-2">
        <button class="btn btn-primary">Filtrar</button>
    </div>
</form>

<table class="table table-striped align-middle">
    <thead>
        <tr>
            <th>Categoria</th>
            <th>Orçado</th>
            <th>Real</th>
            <th>Consumo</th>
            <th style="width:260px">Ação</th>
        </tr>
    </thead>

    <tbody>
    <?php if (empty($categoriasDespesa)): ?>
        <tr>
            <td colspan="5" class="text-center text-muted py-4">
                Você ainda não cadastrou categorias de <b>Despesa</b>.  
                Vá em <b>Categorias</b> e crie pelo menos uma com tipo <b>Despesa (D)</b>.
            </td>
        </tr>
    <?php else: ?>

        <?php foreach ($categoriasDespesa as $cat): ?>
            <?php
                $idCat = (int)$cat['id'];

                $orcado = (float)($orcamentosMap[$idCat] ?? 0);
                $real   = (float)($gastosReais[$idCat] ?? 0);

                $percentual = ($orcado > 0) ? ($real / $orcado) * 100 : 0;

                if ($orcado <= 0) {
                    $classe = 'bg-secondary';
                } elseif ($percentual <= 70) {
                    $classe = 'bg-success';
                } elseif ($percentual <= 100) {
                    $classe = 'bg-warning';
                } else {
                    $classe = 'bg-danger';
                }

                $barra = min($percentual, 100);
            ?>

            <tr>
                <td><?= htmlspecialchars($cat['nome']) ?></td>

                <td>R$ <?= number_format($orcado, 2, ',', '.') ?></td>
                <td>R$ <?= number_format($real, 2, ',', '.') ?></td>

                <td style="min-width:240px">
                    <div class="progress" style="height:22px">
                        <div class="progress-bar <?= $classe ?>"
                             style="width: <?= $barra ?>%">
                            <?= ($orcado > 0) ? number_format($percentual, 1) . '%' : 'Sem orçamento' ?>
                        </div>
                    </div>

                    <?php if ($orcado > 0 && $percentual > 100): ?>
                        <small class="text-danger">Ultrapassou o orçamento!</small>
                    <?php endif; ?>
                </td>

                <td>
                    <form method="POST" action="/financas/public/?url=orcamentos-store" class="d-flex gap-2">
                        <input type="text" name="valor"
                               value="<?= number_format($orcado, 2, ',', '.') ?>"
                               class="form-control form-control-sm">

                        <input type="hidden" name="id_categoria" value="<?= $idCat ?>">
                        <input type="hidden" name="ano" value="<?= (int)$ano ?>">
                        <input type="hidden" name="mes" value="<?= (int)$mes ?>">

                        <button class="btn btn-sm btn-primary">Salvar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

    <?php endif; ?>
    </tbody>
</table>
