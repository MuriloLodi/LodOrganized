<?php
$u = $_SESSION['usuario'] ?? [];

// ====== CABEÇALHO DO PDF (AGORA É O USUÁRIO) ======
$perfil = [
  'nome'   => ($u['nome'] ?? 'Usuário'),
  'email'  => ($u['email'] ?? ''),
  // como seu PerfilController não salva telefone na sessão, aqui fica opcional:
  'telefone' => ($u['telefone'] ?? ''),
];

$validade = (int)($proposta['validade_dias'] ?? 15);
$total    = (float)($proposta['total'] ?? 0);

// ====== IMAGEM NO PDF (SÓ AVATAR DO USUÁRIO) ======
// DOMPDF: melhor usar base64 (data URI)

$projectRoot = dirname(__DIR__, 3); // app/views/propostas -> volta 3 = raiz do projeto
$publicDir   = $projectRoot . DIRECTORY_SEPARATOR . 'public';

$imgFile = '';

// avatar do usuário: public/uploads/avatars/{id}/avatar_xxx.jpg
if (!empty($u['avatar'])) {
  $try = $publicDir . DIRECTORY_SEPARATOR . 'uploads'
    . DIRECTORY_SEPARATOR . 'avatars'
    . DIRECTORY_SEPARATOR . (int)($u['id'] ?? 0)
    . DIRECTORY_SEPARATOR . $u['avatar'];

  if (is_file($try)) $imgFile = $try;
}

function pdfDataUriFromFile($filepath) {
  if (!$filepath || !is_file($filepath)) return '';
  $mime = @mime_content_type($filepath);
  if (!$mime) return '';
  $data = base64_encode(file_get_contents($filepath));
  return 'data:' . $mime . ';base64,' . $data;
}

$imgDataUri = $imgFile ? pdfDataUriFromFile($imgFile) : '';

function iniciaisPdf($nome) {
  $nome = trim((string)$nome);
  if ($nome === '') return 'US';
  $p = preg_split('/\s+/', $nome);
  $a = strtoupper(substr($p[0] ?? 'U', 0, 1));
  $b = strtoupper(substr($p[count($p)-1] ?? 'S', 0, 1));
  return $a . $b;
}

$iniciais = iniciaisPdf($perfil['nome']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color:#2f2f2f; margin:0; }

    .topbar { background:#3a3f46; color:#fff; padding:18px 22px; }
    .topTable { width:100%; border-collapse:collapse; }
    .topTable td { vertical-align:top; }

    .brandTitle { font-size:20px; font-weight:700; letter-spacing:.4px; margin:0; padding:0; }
    .brandSubtitle { font-size:12px; opacity:.85; margin-top:3px; }

    .meta { text-align:right; font-size:12px; line-height:1.45; opacity:.95; }
    .meta b { color:#fff; }

    .headerInfo { margin-top:10px; font-size:12px; line-height:1.5; opacity:.95; }

    .logoBox{
      width:52px; height:52px;
      border-radius:14px;
      background: rgba(255,255,255,.14);
      border:1px solid rgba(255,255,255,.25);
      text-align:center;
      font-weight:700;
      font-size:16px;
      line-height:52px;
      overflow:hidden;
    }
    .logoImg{
      width:52px; height:52px;
      object-fit:cover;
      border-radius:14px;
      display:block;
    }

    .section-title{
      background:#3a3f46; color:#fff; padding:9px 12px;
      font-weight:700; margin-top:16px; letter-spacing:.35px;
    }

    .box{ border:1px solid #e2e4e8; padding:12px; }
    .grid{ width:100%; border-collapse:collapse; }
    .grid td{ padding:6px 8px; border-bottom:1px solid #f0f1f3; }
    .grid tr:last-child td{ border-bottom:0; }

    .label{ width:150px; color:#6b7280; font-weight:700; }

    table.items{ width:100%; border-collapse:collapse; margin-top:10px; }
    table.items th, table.items td{ border:1px solid #e2e4e8; padding:8px; }
    table.items th{ background:#f3f4f6; color:#111827; font-weight:700; }

    .right{ text-align:right; }
    .total-row td{ font-weight:700; }
    .total-label{ background:#3a3f46; color:#fff; font-weight:700; }

    .muted{ color:#6b7280; }
    .pre{ white-space:pre-line; }
  </style>
</head>

<body>

  <div class="topbar">
    <table class="topTable">
      <tr>
        <td style="width:70px;">
          <?php if ($imgDataUri): ?>
            <img class="logoImg" src="<?= $imgDataUri ?>" alt="Avatar">
          <?php else: ?>
            <div class="logoBox"><?= htmlspecialchars($iniciais) ?></div>
          <?php endif; ?>
        </td>

        <td>
          <div class="brandTitle"><?= htmlspecialchars($perfil['nome']) ?></div>
          <div class="brandSubtitle">PROPOSTA / ORÇAMENTO</div>
        </td>

        <td class="meta" style="width:230px;">
          <div><b>Nº:</b> <?= htmlspecialchars($proposta['numero'] ?? '') ?></div>
          <div><b>Emissão:</b>
            <?= !empty($proposta['data_emissao']) ? date('d/m/Y', strtotime($proposta['data_emissao'])) : date('d/m/Y') ?>
          </div>
          <div><b>Validade:</b> <?= (int)$validade ?> dias</div>
        </td>
      </tr>
    </table>

    <div class="headerInfo">
      <?php if (!empty($perfil['email'])): ?>
        <b>E-mail:</b> <?= htmlspecialchars($perfil['email']) ?><br>
      <?php endif; ?>
      <?php if (!empty($perfil['telefone'])): ?>
        <b>Telefone:</b> <?= htmlspecialchars($perfil['telefone']) ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="section-title">DADOS DO CLIENTE</div>
  <div class="box">
    <table class="grid">
      <tr><td class="label">CLIENTE:</td><td><?= htmlspecialchars($proposta['cliente_nome'] ?? '') ?></td></tr>
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
          <th style="width:55px;" class="right">ITEM</th>
          <th>DESCRIÇÃO</th>
          <th style="width:85px;" class="right">QTD.</th>
          <th style="width:120px;" class="right">VALOR UNIT.</th>
          <th style="width:130px;" class="right">VALOR PARCIAL</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach (($itens ?? []) as $it): ?>
          <tr>
            <td class="right"><?= $i++ ?></td>
            <td><?= htmlspecialchars($it['descricao'] ?? '') ?></td>
            <td class="right"><?= number_format((float)($it['quantidade'] ?? 0), 2, ',', '.') ?></td>
            <td class="right">R$ <?= number_format((float)($it['valor_unit'] ?? 0), 2, ',', '.') ?></td>
            <td class="right">R$ <?= number_format((float)($it['valor_total'] ?? 0), 2, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>

        <tr class="total-row">
          <td colspan="3"></td>
          <td class="total-label right">VALOR TOTAL</td>
          <td class="right">R$ <?= number_format((float)$total, 2, ',', '.') ?></td>
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
