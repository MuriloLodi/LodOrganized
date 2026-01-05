<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
}
h1 {
    text-align: center;
    margin-bottom: 5px;
}
.periodo {
    text-align: center;
    margin-bottom: 20px;
    color: #555;
}
.cards {
    width: 100%;
    margin-bottom: 20px;
}
.card {
    border: 1px solid #ccc;
    padding: 10px;
    width: 23%;
    display: inline-block;
    vertical-align: top;
    text-align: center;
}
.card h3 {
    margin: 0;
    font-size: 14px;
}
.card .valor {
    font-size: 16px;
    font-weight: bold;
}
.receita { color: #198754; }
.despesa { color: #dc3545; }
.saldo { color: #0d6efd; }

.barra {
    background: #eee;
    border-radius: 5px;
    overflow: hidden;
    height: 18px;
}
.barra span {
    display: block;
    height: 100%;
}
.verde { background: #198754; }
.amarelo { background: #ffc107; }
.vermelho { background: #dc3545; }

.titulo {
    margin-top: 25px;
    margin-bottom: 8px;
    font-weight: bold;
}

table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    border: 1px solid #ccc;
    padding: 6px;
}
th {
    background: #f2f2f2;
}
</style>
</head>

<body>

<h1>Relatório Executivo Financeiro</h1>
<div class="periodo">
    <?= str_pad($mes, 2, '0', STR_PAD_LEFT) ?>/<?= $ano ?>
</div>

<!-- CARDS -->
<div class="cards">
    <div class="card">
        <h3>Receitas</h3>
        <div class="valor receita">
            R$ <?= number_format($resumo['receitas'], 2, ',', '.') ?>
        </div>
    </div>

    <div class="card">
        <h3>Despesas</h3>
        <div class="valor despesa">
            R$ <?= number_format($resumo['despesas'], 2, ',', '.') ?>
        </div>
    </div>

    <div class="card">
        <h3>Saldo</h3>
        <div class="valor saldo">
            R$ <?= number_format($resumo['saldo'], 2, ',', '.') ?>
        </div>
    </div>

    <div class="card">
        <h3>Orçamento</h3>
        <div class="valor">
            <?= number_format($orcamentoGeral['percentual'], 1) ?>%
        </div>
    </div>
</div>

<!-- BARRA ORÇAMENTO -->
<div class="titulo">Consumo do Orçamento</div>

<?php
$p = $orcamentoGeral['percentual'];
$classe = $p <= 70 ? 'verde' : ($p <= 100 ? 'amarelo' : 'vermelho');
?>

<div class="barra">
    <span class="<?= $classe ?>" style="width: <?= min($p,100) ?>%"></span>
</div>

<p>
    R$ <?= number_format($orcamentoGeral['real'], 2, ',', '.') ?>
    de
    R$ <?= number_format($orcamentoGeral['orcado'], 2, ',', '.') ?>
</p>

<!-- ESTOURADOS -->
<?php if (!empty($estourados)): ?>
<div class="titulo">⚠️ Categorias que estouraram o orçamento</div>

<table>
    <thead>
        <tr>
            <th>Categoria</th>
            <th>Orçado</th>
            <th>Real</th>
            <th>%</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($estourados as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['nome']) ?></td>
            <td>R$ <?= number_format($e['orcado'], 2, ',', '.') ?></td>
            <td>R$ <?= number_format($e['total_real'], 2, ',', '.') ?></td>
            <td><?= number_format($e['percentual'], 1) ?>%</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

</body>
</html>
