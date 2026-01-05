<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        h2 {
            text-align: center;
            margin-bottom: 5px;
        }
        .periodo {
            text-align: center;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #444;
            padding: 6px;
        }
        th {
            background: #f0f0f0;
        }
        .right {
            text-align: right;
        }
        .total {
            font-weight: bold;
            background: #eee;
        }
    </style>
</head>
<body>

<h2>Relatório Financeiro Mensal</h2>
<div class="periodo">
    <?= str_pad($mes, 2, '0', STR_PAD_LEFT) ?>/<?= $ano ?>
</div>

<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Descrição</th>
            <th>Tipo</th>
            <th>Conta</th>
            <th>Categoria</th>
            <th class="right">Valor (R$)</th>
        </tr>
    </thead>
    <tbody>

    <?php
    $totalR = 0;
    $totalD = 0;
    ?>

    <?php foreach ($dados as $row): ?>
        <?php
            if ($row['tipo'] === 'R') $totalR += $row['valor'];
            else $totalD += $row['valor'];
        ?>
        <tr>
            <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
            <td><?= htmlspecialchars($row['descricao']) ?></td>
            <td><?= $row['tipo'] === 'R' ? 'Receita' : 'Despesa' ?></td>
            <td><?= htmlspecialchars($row['conta']) ?></td>
            <td><?= htmlspecialchars($row['categoria']) ?></td>
            <td class="right"><?= number_format($row['valor'], 2, ',', '.') ?></td>
        </tr>
    <?php endforeach; ?>

    <tr class="total">
        <td colspan="5">Total de Receitas</td>
        <td class="right"><?= number_format($totalR, 2, ',', '.') ?></td>
    </tr>
    <tr class="total">
        <td colspan="5">Total de Despesas</td>
        <td class="right"><?= number_format($totalD, 2, ',', '.') ?></td>
    </tr>
    <tr class="total">
        <td colspan="5">Saldo</td>
        <td class="right"><?= number_format($totalR - $totalD, 2, ',', '.') ?></td>
    </tr>

    </tbody>
</table>

</body>
</html>
