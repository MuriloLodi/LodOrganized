<?php
$subtotal = 0;
foreach ($itens as $i) $subtotal += (float)$i['total'];
$desconto = (float)($proposta['desconto'] ?? 0);
$total = max($subtotal - $desconto, 0);
?>

<div class="d-flex justify-content-between align-items-start align-items-md-center gap-3 mb-4 flex-wrap">
  <div>
    <h1 class="mb-1">Proposta #<?= (int)$proposta['numero'] ?>/<?= (int)$proposta['ano'] ?></h1>
    <div class="text-muted">Edite itens, desconto e status</div>
  </div>

  <a class="btn btn-outline-secondary" href="/financas/public/?url=propostas">Voltar</a>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">

        <form method="POST" action="/financas/public/?url=<?= $proposta['id'] ? 'propostas-update' : 'propostas-store' ?>">
          <?php if ($proposta['id']): ?>
            <input type="hidden" name="id" value="<?= (int)$proposta['id'] ?>">
          <?php endif; ?>

          <div class="row g-2">
            <div class="col-md-4">
              <label class="form-label">Data</label>
              <input type="date" name="data_emissao" class="form-control" value="<?= htmlspecialchars($proposta['data_emissao']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Validade (dias)</label>
              <input type="number" name="validade_dias" class="form-control" value="<?= (int)$proposta['validade_dias'] ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Desconto (R$)</label>
              <input type="text" name="desconto" class="form-control" value="<?= number_format((float)$proposta['desconto'], 2, ',', '.') ?>">
            </div>
          </div>

          <hr>

          <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Itens</strong>
            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="addItem()">+ Adicionar item</button>
          </div>

          <div id="itensWrap">
            <?php
              $linhas = !empty($itens) ? $itens : [['descricao'=>'', 'qtd'=>1, 'valor_unit'=>0]];
              foreach ($linhas as $idx => $i):
            ?>
              <div class="row g-2 align-items-end mb-2 item-row">
                <div class="col-md-6">
                  <label class="form-label">Descrição</label>
                  <input name="itens_desc[]" class="form-control" value="<?= htmlspecialchars($i['descricao'] ?? '') ?>" placeholder="Ex: Site institucional (setup)">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Qtd</label>
                  <input name="itens_qtd[]" class="form-control" value="<?= number_format((float)($i['qtd'] ?? 1), 2, ',', '.') ?>">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Valor unit.</label>
                  <input name="itens_valor[]" class="form-control" value="<?= number_format((float)($i['valor_unit'] ?? 0), 2, ',', '.') ?>">
                </div>
                <div class="col-md-1">
                  <button type="button" class="btn btn-outline-danger w-100" onclick="removeItem(this)">×</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="mt-3">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3"><?= htmlspecialchars($proposta['observacoes'] ?? '') ?></textarea>
          </div>

          <div class="d-flex gap-2 mt-3 flex-wrap">
            <button class="btn btn-primary">Salvar</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h5 class="mb-3">Resumo</h5>

        <div class="d-flex justify-content-between">
          <span class="text-muted">Subtotal</span>
          <strong>R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
        </div>
        <div class="d-flex justify-content-between mt-1">
          <span class="text-muted">Desconto</span>
          <strong>R$ <?= number_format($desconto, 2, ',', '.') ?></strong>
        </div>
        <hr>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Total</span>
          <strong class="fs-5">R$ <?= number_format($total, 2, ',', '.') ?></strong>
        </div>

        <?php if (!empty($proposta['id'])): ?>
          <hr>
          <form method="POST" action="/financas/public/?url=propostas-status" class="d-flex gap-2">
            <input type="hidden" name="id" value="<?= (int)$proposta['id'] ?>">
            <select name="status" class="form-select">
              <?php foreach (['rascunho','enviado','aprovado','recusado'] as $s): ?>
                <option value="<?= $s ?>" <?= $proposta['status']===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-outline-primary">OK</button>
          </form>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<script>
function addItem(){
  const wrap = document.getElementById('itensWrap');
  const div = document.createElement('div');
  div.className = 'row g-2 align-items-end mb-2 item-row';
  div.innerHTML = `
    <div class="col-md-6">
      <label class="form-label">Descrição</label>
      <input name="itens_desc[]" class="form-control" placeholder="Ex: Mensalidade suporte">
    </div>
    <div class="col-md-2">
      <label class="form-label">Qtd</label>
      <input name="itens_qtd[]" class="form-control" value="1,00">
    </div>
    <div class="col-md-3">
      <label class="form-label">Valor unit.</label>
      <input name="itens_valor[]" class="form-control" value="0,00">
    </div>
    <div class="col-md-1">
      <button type="button" class="btn btn-outline-danger w-100" onclick="removeItem(this)">×</button>
    </div>
  `;
  wrap.appendChild(div);
}
function removeItem(btn){
  const row = btn.closest('.item-row');
  if(!row) return;
  const all = document.querySelectorAll('.item-row');
  if(all.length <= 1) return;
  row.remove();
}
</script>
