<h1 class="mb-4">Orçamento mensal</h1>

<form class="row g-2 mb-4">
    <input type="hidden" name="url" value="orcamentos">

    <div class="col-md-2">
        <select name="mes" class="form-select">
            <?php for ($m=1; $m<=12; $m++): ?>
                <option value="<?= $m ?>" <?= $m==$mes?'selected':'' ?>>
                    <?= strftime('%B', mktime(0,0,0,$m)) ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="col-md-2">
        <input type="number" name="ano" value="<?= $ano ?>" class="form-control">
    </div>

    <div class="col-md-2">
        <button class="btn btn-primary">Filtrar</button>
    </div>
</form>

<table class="table table-striped">
    <thead>
<tr>
    <th>Categoria</th>
    <th>Orçado</th>
    <th>Real</th>
    <th>Consumo</th>
    <th>Ação</th>
</tr>
</thead>

    <tbody>
<?php foreach ($categorias as $cat): 
    if ($cat['tipo'] !== 'D') continue;

    $orcado = 0;
    foreach ($orcamentos as $o) {
        if ($o['id_categoria'] == $cat['id']) {
            $orcado = (float)$o['valor'];
            break;
        }
    }

    $real = $gastosReais[$cat['id']] ?? 0;
    $percentual = $orcado > 0 ? ($real / $orcado) * 100 : 0;

    if ($percentual <= 70) {
        $classe = 'bg-success';
    } elseif ($percentual <= 100) {
        $classe = 'bg-warning';
    } else {
        $classe = 'bg-danger';
    }
?>
<tr>
    <td><?= htmlspecialchars($cat['nome']) ?></td>

    <td>R$ <?= number_format($orcado, 2, ',', '.') ?></td>
    <td>R$ <?= number_format($real, 2, ',', '.') ?></td>

    <td style="min-width:220px">
        <div class="progress">
            <div class="progress-bar <?= $classe ?>"
                 style="width: <?= min($percentual, 100) ?>%">
                <?= number_format($percentual, 1) ?>%
            </div>
        </div>

        <?php if ($percentual > 100): ?>
            <small class="text-danger">
                Ultrapassou o orçamento!
            </small>
        <?php endif; ?>
    </td>

    <td>
        <form method="POST" action="/financas/public/?url=orcamentos-store" class="d-flex gap-1">
            <input type="number" step="0.01" name="valor"
                   value="<?= $orcado ?>" class="form-control form-control-sm">

            <input type="hidden" name="id_categoria" value="<?= $cat['id'] ?>">
            <input type="hidden" name="ano" value="<?= $ano ?>">
            <input type="hidden" name="mes" value="<?= $mes ?>">

            <button class="btn btn-sm btn-primary">Salvar</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody>

</table>
