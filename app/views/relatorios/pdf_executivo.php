<?php
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$receitas = (float)($resumo['receitas'] ?? 0);
$despesas = (float)($resumo['despesas'] ?? 0);
$saldo    = (float)($resumo['saldo'] ?? 0);

// top 8 categorias
$topCat = array_slice($porCategoria ?? [], 0, 8);
$maxCat = 1;
foreach($topCat as $t){ $maxCat = max($maxCat, (float)$t['total']); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
    .header { margin-bottom: 12px; }
    .title { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
    .sub { color:#555; }
    .grid { width:100%; margin-top: 12px; }
    .box { border:1px solid #ddd; padding:10px; margin-bottom:10px; border-radius:6px; }
    .kpis { width:100%; }
    .kpis td { border:0; padding:0 10px 0 0; }
    .kpi-title { color:#666; font-size:11px; }
    .kpi-value { font-size:16px; font-weight:bold; margin-top:2px; }
    .pos { color:#0a7; } .neg { color:#c22; } .pri { color:#246; }
    .bar { height:10px; background:#eee; border-radius:20px; overflow:hidden; margin-top:6px; }
    .bar > div { height:10px; background:#777; }
    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #ddd; padding:6px; }
    th { background:#f3f3f3; text-align:left; }
    .r { text-align:right; }
</style>
</head>
<body>

<div class="header">
    <div class="title">Relatório Executivo</div>
    <div class="sub">Período: <?= h($f['data_inicio']) ?> → <?= h($f['data_fim']) ?></div>
</div>

<div class="box">
    <table class="kpis">
        <tr>
            <td>
                <div class="kpi-title">Receitas</div>
                <div class="kpi-value pos">R$ <?= number_format($receitas,2,',','.') ?></div>
            </td>
            <td>
                <div class="kpi-title">Despesas</div>
                <div class="kpi-value neg">R$ <?= number_format($despesas,2,',','.') ?></div>
            </td>
            <td>
                <div class="kpi-title">Saldo</div>
                <div class="kpi-value <?= $saldo>=0?'pri':'neg' ?>">R$ <?= number_format($saldo,2,',','.') ?></div>
            </td>
            <td>
                <div class="kpi-title">Qtd lançamentos</div>
                <div class="kpi-value"><?= (int)($resumo['qtd'] ?? 0) ?></div>
            </td>
        </tr>
    </table>
</div>

<div class="box">
    <div style="font-weight:bold; margin-bottom:6px;">Top categorias</div>
    <?php if (empty($topCat)): ?>
        <div style="color:#666;">Sem dados.</div>
    <?php else: ?>
        <?php foreach($topCat as $t): ?>
            <?php
                $nome = $t['nome'] ?? 'Sem categoria';
                $total = (float)$t['total'];
                $w = min(($total/$maxCat)*100, 100);
            ?>
            <div style="margin-bottom:8px;">
                <div style="display:flex; justify-content:space-between;">
                    <div><b><?= h($nome) ?></b> <span style="color:#666;">(<?= ($t['tipo'] ?? '')==='R'?'Receita':'Despesa' ?>)</span></div>
                    <div><b>R$ <?= number_format($total,2,',','.') ?></b></div>
                </div>
                <div class="bar"><div style="width:<?= (float)$w ?>%"></div></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="box">
    <div style="font-weight:bold; margin-bottom:6px;">Lançamentos (amostra)</div>
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
            <?php foreach(array_slice($rows, 0, 20) as $l): ?>
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
    <div style="margin-top:8px; color:#666;">Mostrando até 20 itens (use CSV para completo).</div>
</div>

</body>
</html>
