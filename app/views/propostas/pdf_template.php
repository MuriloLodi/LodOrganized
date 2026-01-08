<?php
$empresa = [
  'nome' => $u['nome_empresa'] ?? 'Minha Empresa',
  'endereco' => $u['endereco_empresa'] ?? '',
  'responsavel' => $u['nome'] ?? '',
  'contato' => $u['telefone'] ?? ($u['email'] ?? '')
];

$validade = (int)($proposta['validade_dias'] ?? 15);
$total = (float)($proposta['total'] ?? 0);
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <style>
    body{ font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color:#333; }
    .topbar{ background:#6b6b6b; color:#fff; padding:18px 20px; }
    .toprow{ display:flex; align-items:flex-start; justify-content:space-between; }
    .brand{ font-size:20px; font-weight:700; letter-spacing:.5px; }
    .brand small{ display:block; font-size:12px; font-weight:400; opacity:.9; }
    .meta{ text-align:right; font-size:12px; line-height:1.35; opacity:.95; }

    .section-title{
      background:#6b6b6b; color:#fff; padding:8px 12px;
      font-weight:700; margin-top:18px; letter-spacing:.5px;
    }
    .box{ border:1px solid #ddd; padding:12px; }
    .grid{ width:100%; border-collapse:collapse; }
    .grid td{ padding:6px 8px; border-bottom:1px solid #eee; }
    .label{ width:140px; color:#666; font-weight:700; }

    table.items{ width:100%; border-collapse:collapse; margin-top:10px; }
    table.items th, table.items td{ border:1px solid #777; padding:8px; }
    table.items th{ background:#6b6b6b; color:#fff; font-weight:700; }
    .right{ text-align:right; }
    .total-row td{ font-weight:700; }
    .total-label{ background:#6b6b6b; color:#fff; }

    .muted{ color:#666; }
    .pre{ white-space:pre-line; }
  </style>
</head>
<body>

  <div class="topbar">
    <div class="toprow">
      <div>
        <div class="brand">
          <?= htmlspecialchars($empresa['nome']) ?>
          <small>PROPOSTA / ORÇAMENTO</small>
        </div>
      </div>
      <div class="meta">
        <div><b>Nº:</b> <?= htmlspecialchars($proposta['numero']) ?></div>
        <div><b>Emissão:</b> <?= date('d/m/Y', strtotime($proposta['data_emissao'])) ?></div>
        <div><b>Validade:</b> <?= $validade ?> dias</div>
      </div>
    </div>
    <div class="meta" style="text-align:left; margin-top:10px;">
      <?= htmlspecialchars($empresa['endereco']) ?><br>
      <b>Responsável:</b> <?= htmlspecialchars($empresa['responsavel']) ?><br>
      <b>Contato:</b> <?= htmlspecialchars($empresa['contato']) ?>
    </div>
  </div>

  <div class="section-title">DADOS DO CLIENTE</div>
  <div class="box">
    <table class="grid">
      <tr><td class="label">CLIENTE:</td><td><?= htmlspecialchars($proposta['cliente_nome']) ?></td></tr>
      <tr><td class="label">E-MAIL:</td><td><?= htmlspecialchars($proposta['cliente_email'] ?? '') ?></td></tr>
      <tr><td class="label">TELEFONE:</td><td><?= htmlspecialchars($proposta['cliente_telefone'] ?? '') ?></td></tr>
      <tr><td class="label">ENDEREÇO:</td><td><?= htmlspecialchars($proposta['cliente_endereco'] ?? '') ?></td></tr>
      <tr><td class="label">FORMA PAGAMENTO:</td><td><?= htmlspecialchars($proposta['forma_pagamento'] ?? '') ?></td></tr>
    </table>
  </div>

  <div class="section-title">ITENS</div>
  <div class="box">
    <table class="items">
      <thead>
        <tr>
          <th style="width:60px;">ITEM</th>
          <th>DESCRIÇÃO</th>
          <th style="width:90px;">QTD.</th>
          <th style="width:130px;">VALOR UNIT.</th>
          <th style="width:140px;">VALOR PARCIAL</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach ($itens as $it): ?>
          <tr>
            <td class="right"><?= $i++ ?></td>
            <td><?= htmlspecialchars($it['descricao']) ?></td>
            <td class="right"><?= number_format((float)$it['quantidade'], 2, ',', '.') ?></td>
            <td class="right">R$ <?= number_format((float)$it['valor_unit'], 2, ',', '.') ?></td>
            <td class="right">R$ <?= number_format((float)$it['valor_total'], 2, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>

        <tr class="total-row">
          <td colspan="3"></td>
          <td class="total-label right">VALOR TOTAL</td>
          <td class="right">R$ <?= number_format($total, 2, ',', '.') ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="section-title">OBSERVAÇÕES</div>
  <div class="box pre muted">
    <?= htmlspecialchars($proposta['observacoes'] ?? '') ?>
  </div>

  <div class="section-title">CONSIDERAÇÕES FINAIS</div>
  <div class="box pre">
    <?= htmlspecialchars($proposta['consideracoes'] ?? '') ?>
  </div>

</body>
</html>
