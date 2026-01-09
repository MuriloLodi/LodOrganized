<?php
$isEdit = !empty($proposta['id']);
$action = $isEdit ? '/financas/public/?url=propostas-update' : '/financas/public/?url=propostas-store';

$clientes = $clientes ?? []; // vem do controller
$idClienteAtual = (int)($proposta['id_cliente'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="mb-0"><?= $isEdit ? 'Editar proposta' : 'Nova proposta' ?></h1>
    <div class="text-muted">Preencha dados e itens, depois gere o PDF</div>
  </div>

  <div class="d-flex gap-2">
    <?php if ($isEdit): ?>
      <a class="btn btn-outline-dark"
         href="/financas/public/?url=propostas-pdf&id=<?= (int)$proposta['id'] ?>">
        <i class="bi bi-file-earmark-pdf"></i> Baixar PDF
      </a>
    <?php endif; ?>
    <a class="btn btn-outline-secondary" href="/financas/public/?url=propostas">
      Voltar
    </a>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>">
  
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int)$proposta['id'] ?>">
  <?php endif; ?>

  <!-- ✅ guarda o vínculo do cliente (se selecionar) -->
  <input type="hidden" name="id_cliente" id="id_cliente" value="<?= $idClienteAtual ? (int)$idClienteAtual : '' ?>">
<input type="hidden" name="oportunidade_id" value="<?= (int)($oportunidade_id ?? 0) ?>">

  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-body">

          <div class="row g-2">
            <div class="col-6">
              <label class="form-label">Número</label>
              <input name="numero" class="form-control"
                     value="<?= htmlspecialchars($proposta['numero'] ?? '') ?>"
                     <?= $isEdit ? 'readonly' : '' ?>>
            </div>

            <div class="col-6">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <?php
                $st = $proposta['status'] ?? 'rascunho';
                $ops = ['rascunho','enviado','aprovado','recusado'];
                foreach ($ops as $o):
                ?>
                  <option value="<?= $o ?>" <?= $st===$o?'selected':'' ?>><?= ucfirst($o) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-6">
              <label class="form-label">Emissão</label>
              <input type="date" name="data_emissao" class="form-control"
                     value="<?= htmlspecialchars($proposta['data_emissao'] ?? date('Y-m-d')) ?>">
            </div>

            <div class="col-6">
              <label class="form-label">Validade (dias)</label>
              <input type="number" name="validade_dias" class="form-control"
                     value="<?= (int)($proposta['validade_dias'] ?? 15) ?>">
            </div>
          </div>

          <hr class="my-3">

          <div class="d-flex align-items-center justify-content-between">
            <h6 class="fw-semibold mb-2">Cliente</h6>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimparCliente">
              Limpar seleção
            </button>
          </div>

          <!-- ✅ Cliente existente -->
          <div class="mb-2">
            <label class="form-label">Cliente existente</label>
            <select class="form-select" id="selectCliente">
              <option value="">— Selecionar —</option>
              <?php foreach ($clientes as $c): ?>
                <?php $cid = (int)($c['id'] ?? 0); ?>
                <option value="<?= $cid ?>"
                        <?= $idClienteAtual === $cid ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['nome'] ?? '') ?>
                  <?php if (!empty($c['documento'])): ?> • <?= htmlspecialchars($c['documento']) ?><?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="text-muted small mt-1">Ao selecionar, os campos abaixo são preenchidos automaticamente.</div>
          </div>

          <div class="mb-2">
            <label class="form-label">Nome</label>
            <input name="cliente_nome" id="cliente_nome" class="form-control" required
                   value="<?= htmlspecialchars($proposta['cliente_nome'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">E-mail</label>
            <input name="cliente_email" id="cliente_email" class="form-control"
                   value="<?= htmlspecialchars($proposta['cliente_email'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Telefone</label>
            <input name="cliente_telefone" id="cliente_telefone" class="form-control"
                   value="<?= htmlspecialchars($proposta['cliente_telefone'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Endereço</label>
            <input name="cliente_endereco" id="cliente_endereco" class="form-control"
                   value="<?= htmlspecialchars($proposta['cliente_endereco'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Forma de pagamento</label>
            <input name="forma_pagamento" class="form-control"
                   value="<?= htmlspecialchars($proposta['forma_pagamento'] ?? '') ?>">
          </div>

        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0 fw-semibold">Itens</h5>
            <button class="btn btn-outline-primary btn-sm" type="button" id="btnAddItem">
              <i class="bi bi-plus-lg"></i> Adicionar item
            </button>
          </div>

          <div class="table-responsive-mobile">
            <table class="table align-middle" id="tblItens">
              <thead class="table-light">
                <tr>
                  <th style="width:48px">#</th>
                  <th>Descrição</th>
                  <th style="width:120px" class="text-end">Qtd</th>
                  <th style="width:160px" class="text-end">Valor unit.</th>
                  <th style="width:48px"></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($itens)) $itens = []; ?>
                <?php foreach ($itens as $idx => $it): ?>
                  <tr>
                    <td class="text-muted"><?= $idx+1 ?></td>
                    <td>
                      <input class="form-control" name="itens[<?= $idx ?>][descricao]"
                             value="<?= htmlspecialchars($it['descricao'] ?? '') ?>"
                             placeholder="Ex: Cozinha completa" required>
                    </td>
                    <td>
                      <input class="form-control text-end money-br" name="itens[<?= $idx ?>][quantidade]"
                             value="<?= number_format((float)($it['quantidade'] ?? 1), 2, ',', '.') ?>"
                             placeholder="1,00">
                    </td>
                    <td>
                      <input class="form-control text-end money-br" name="itens[<?= $idx ?>][valor_unit]"
                             value="<?= number_format((float)($it['valor_unit'] ?? 0), 2, ',', '.') ?>"
                             placeholder="0,00">
                    </td>
                    <td class="text-end">
                      <button class="btn btn-outline-danger btn-sm btnDelItem" type="button">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-end mt-2">
            <div class="text-end">
              <div class="text-muted small">Total</div>
              <div class="fs-4 fw-bold" id="totalPreview">
                R$ <?= number_format((float)($proposta['total'] ?? 0), 2, ',', '.') ?>
              </div>
              <div class="text-muted small">O total final será recalculado ao salvar.</div>
            </div>
          </div>

        </div>
      </div>

      <div class="card">
        <div class="card-body">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Observações</label>
              <textarea name="observacoes" rows="6" class="form-control"><?= htmlspecialchars($proposta['observacoes'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Considerações finais</label>
              <textarea name="consideracoes" rows="6" class="form-control"><?= htmlspecialchars($proposta['consideracoes'] ?? '') ?></textarea>
            </div>
          </div>

          <div class="d-flex gap-2 mt-3">
            <button class="btn btn-primary">
              <i class="bi bi-check2-circle"></i> Salvar
            </button>

            <?php if ($isEdit): ?>
              <a class="btn btn-outline-dark"
                 href="/financas/public/?url=propostas-pdf&id=<?= (int)$proposta['id'] ?>">
                 <i class="bi bi-file-earmark-pdf"></i> Baixar PDF
              </a>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</form>

<script>
(function(){
  // ✅ clientes disponíveis (para preencher campos)
  const CLIENTES = <?= json_encode($clientes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  const sel = document.getElementById('selectCliente');
  const hid = document.getElementById('id_cliente');

  const inNome = document.getElementById('cliente_nome');
  const inEmail = document.getElementById('cliente_email');
  const inTel = document.getElementById('cliente_telefone');
  const inEnd = document.getElementById('cliente_endereco');

  const btnLimpar = document.getElementById('btnLimparCliente');

  function findClienteById(id){
    id = parseInt(id || "0", 10);
    if(!id) return null;
    return CLIENTES.find(c => parseInt(c.id, 10) === id) || null;
  }

  function aplicarCliente(cli){
    if(!cli) return;
    hid.value = cli.id || '';
    inNome.value = cli.nome || '';
    inEmail.value = cli.email || '';
    inTel.value = cli.telefone || '';
    inEnd.value = cli.endereco || '';
  }

  function limparCliente(){
  if(sel) sel.value = '';
  if(hid) hid.value = '';

  // ✅ agora limpa os campos também
  inNome.value = '';
  inEmail.value = '';
  inTel.value = '';
  inEnd.value = '';
}


  sel?.addEventListener('change', function(){
    const cli = findClienteById(this.value);
    if(cli) aplicarCliente(cli);
    else { hid.value = ''; }
  });

  btnLimpar?.addEventListener('click', function(){
    limparCliente();
  });

  // se ao abrir a tela já tiver id_cliente preenchido (edição), preenche
  if(hid?.value){
    const cli = findClienteById(hid.value);
    if(cli){
      if(sel) sel.value = String(cli.id);
      aplicarCliente(cli);
    }
  }

  // ===== itens (igual seu atual) =====
  const tbl = document.getElementById('tblItens');
  const btnAdd = document.getElementById('btnAddItem');

  function renumera(){
    const rows = tbl.querySelectorAll('tbody tr');
    rows.forEach((tr, i) => {
      tr.querySelector('td').textContent = (i+1);
      tr.querySelectorAll('input').forEach(inp => {
        inp.name = inp.name.replace(/itens\[\d+\]/, 'itens['+i+']');
      });
    });
  }

  btnAdd?.addEventListener('click', function(){
    const tbody = tbl.querySelector('tbody');
    const i = tbody.querySelectorAll('tr').length;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="text-muted">${i+1}</td>
      <td><input class="form-control" name="itens[${i}][descricao]" placeholder="Ex: Serviço X" required></td>
      <td><input class="form-control text-end money-br" name="itens[${i}][quantidade]" value="1,00" placeholder="1,00"></td>
      <td><input class="form-control text-end money-br" name="itens[${i}][valor_unit]" value="0,00" placeholder="0,00"></td>
      <td class="text-end">
        <button class="btn btn-outline-danger btn-sm btnDelItem" type="button"><i class="bi bi-x-lg"></i></button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  tbl?.addEventListener('click', function(e){
    const btn = e.target.closest('.btnDelItem');
    if(!btn) return;
    btn.closest('tr').remove();
    renumera();
  });
})();
</script>
