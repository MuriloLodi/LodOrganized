<?php
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
    .title { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
    .sub { color:#555; margin-bottom: 14px; }
    .kpi { margin: 8px 0 14px; }
    .kpi span { display:inline-block; margin-right: 18px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border:1px solid #ddd; padding: 6px; }
    th { background:#f3f3f3; text-align:left; }
    .r { text-align:right; }
</style>
</head>
<body>

<div class="title">Relatório mensal (simples)</div>
<div class="sub">Período: <?= h($f['data_inicio']) ?> → <?= h($f['data_fim']) ?></div>

<div class="kpi">
    <span><b>Receitas:</b> R$ <?= number_format((float)$resumo['receitas'],2,',','.') ?></span>
    <span><b>Despesas:</b> R$ <?= number_format((float)$resumo['despesas'],2,',','.') ?></span>
    <span><b>Saldo:</b> R$ <?= number_format((float)$resumo['saldo'],2,',','.') ?></span>
</div>

<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Descrição</th>
            <th>Categoria</th>
            <th>Conta</th>
            <th>Status</th>
            <th class="r">Valor</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($rows as $l): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($l['data'])) ?></td>
                <td><?= h($l['descricao']) ?></td>
                <td><?= h($l['categoria']) ?></td>
                <td><?= h($l['conta']) ?></td>
                <td><?= h($l['status'] ?? 'pago') ?></td>
                <td class="r">
                    <?= ($l['tipo'] ?? 'D') === 'R' ? '+':'-' ?>
                    R$ <?= number_format((float)$l['valor'],2,',','.') ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
