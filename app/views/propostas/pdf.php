<?php
// Espera receber do controller:
// $empresa, $cliente, $itens, $total, $observacoes, $textoFinal
function moeda($v) {
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 28px 28px 24px 28px; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color:#333; font-size: 12px; }
    .topbar { background:#6f6f6f; color:#fff; padding:16px 18px; }
    .top-grid { width:100%; }
    .logo-box { width:55%; vertical-align:middle; }
    .info-box { width:45%; vertical-align:middle; text-align:left; border-left:2px solid rgba(255,255,255,.4); padding-left:14px; }
    .brand { font-size:26px; font-weight:800; letter-spacing:.5px; line-height:1.1; }
    .brand small { display:block; font-size:12px; font-weight:400; opacity:.9; margin-top:2px; }
    .info { font-size:12px; line-height:1.45; opacity:.95; }

    .section-title { background:#6f6f6f; color:#fff; padding:10px 14px; margin-top:18px; font-weight:800; letter-spacing:.6px; font-size:14px; }
    .box { border:1px solid #d8d8d8; padding:12px 14px; }

    .row { margin: 6px 0; }
    .label { display:inline-block; width:120px; font-weight:700; letter-spacing:.3px; color:#555; }
    .line { display:inline-block; width: calc(100% - 130px); border-bottom:1px solid #bdbdbd; height:16px; vertical-align:bottom; }

    .table { width:100%; border-collapse:collapse; margin-top:10px; }
    .table th { background:#6f6f6f; color:#fff; padding:10px 8px; font-size:12px; text-transform:uppercase; letter-spacing:.4px; }
    .table td { border:1px solid #bfbfbf; padding:10px 8px; vertical-align:middle; }
    .table td.center { text-align:center; }
    .table td.right { text-align:right; }
    .muted { color:#666; }

    .total-row td { font-weight:800; }
    .total-label { background:#6f6f6f; color:#fff; text-align:center; font-weight:900; }

    .obs { line-height:1.6; font-size:12px; color:#444; }
    .final { line-height:1.55; font-size:12px; color:#444; }
</style>
</head>
<body>

<!-- HEADER -->
<div class="topbar">
    <table class="top-grid" cellpadding="0" cellspacing="0">
        <tr>
            <td class="logo-box">
                <div class="brand">
                    <?= htmlspecialchars($empresa['nome']) ?>
                    <small>MÓVEIS PLANEJADOS</small>
                </div>
            </td>
            <td class="info-box">
                <div class="info">
                    <?= htmlspecialchars($empresa['endereco']) ?><br>
                    <?= htmlspecialchars($empresa['responsavel']) ?><br>
                    <?= htmlspecialchars($empresa['contato']) ?>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- DADOS PESSOAIS -->
<div class="section-title">DADOS PESSOAIS</div>
<div class="box">
    <div class="row"><span class="label">CLIENTE:</span> <span class="line"><?= htmlspecialchars($cliente['nome']) ?></span></div>
    <div class="row"><span class="label">E-MAIL:</span> <span class="line"><?= htmlspecialchars($cliente['email']) ?></span></div>
    <div class="row"><span class="label">TELEFONE:</span> <span class="line"><?= htmlspecialchars($cliente['telefone']) ?></span></div>
    <div class="row"><span class="label">ENDEREÇO:</span> <span class="line"><?= htmlspecialchars($cliente['endereco']) ?></span></div>
    <div class="row"><span class="label">CIDADE:</span> <span class="line"><?= htmlspecialchars($cliente['cidade']) ?></span></div>
    <div class="row"><span class="label">FORMA DE PAGAMENTO:</span> <span class="line"><?= htmlspecialchars($cliente['pagamento']) ?></span></div>
</div>

<!-- ORÇAMENTO -->
<div class="section-title">ORÇAMENTO</div>
<div class="box">
    <table class="table">
        <thead>
            <tr>
                <th style="width:8%">Item</th>
                <th>Descrição</th>
                <th style="width:12%">Quant.</th>
                <th style="width:20%">Valor unitário</th>
                <th style="width:20%">Valor parcial</th>
            </tr>
        </thead>
        <tbody>
        <?php $idx = 1; foreach ($itens as $i): 
            $parcial = (float)$i['qtd'] * (float)$i['unit'];
        ?>
            <tr>
                <td class="center"><?= $idx++ ?></td>
                <td><?= htmlspecialchars($i['descricao']) ?></td>
                <td class="center"><?= (int)$i['qtd'] ?></td>
                <td class="right"><?= moeda($i['unit']) ?></td>
                <td class="right"><?= moeda($parcial) ?></td>
            </tr>
        <?php endforeach; ?>

            <tr class="total-row">
                <td></td>
                <td></td>
                <td></td>
                <td class="total-label">VALOR TOTAL</td>
                <td class="right"><?= moeda($total) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- OBSERVAÇÕES -->
<div class="section-title">OBSERVAÇÕES</div>
<div class="box obs">
    <?php foreach ($observacoes as $o): ?>
        <?= htmlspecialchars($o) ?><br>
    <?php endforeach; ?>
</div>

<!-- CONSIDERAÇÕES FINAIS -->
<div class="section-title">CONSIDERAÇÕES FINAIS</div>
<div class="box final">
    <?= $textoFinal ?>
</div>

</body>
</html>
