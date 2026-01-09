<?php
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$isEdit  = !empty($proposta['id']);
$action  = $isEdit ? '/financas/public/?url=propostas-update' : '/financas/public/?url=propostas-store';

$clientes = $clientes ?? [];
$itens    = $itens ?? [];

$idClienteAtual = (int)($proposta['id_cliente'] ?? 0);
?>

<style>
/* ===== PADRÃO VISUAL (igual dashboard/lista) ===== */
.page-title{ font-weight: 900; letter-spacing: -.5px; margin:0; }
.page-sub{ color:#6c757d; }

.card-soft{
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 18px;
  background:#fff;
  box-shadow: 0 10px 26px rgba(0,0,0,.06);
}
.card-soft .card-body{ padding: 1.25rem; }

.micro{ transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease; }
.micro:hover{
  transform: translateY(-1px);
  box-shadow: 0 14px 34px rgba(0,0,0,.10);
  border-color: rgba(13,110,253,.25);
}

.btn{ border-radius: 12px; font-weight: 650; }
.btn:active{ transform: translateY(1px); }

.section-title{
  font-weight: 850;
  letter-spacing: -.2px;
  margin: 0;
}

.mini-hint{ color:#6c757d; font-size:.9rem; }

.hr-soft{ border-top: 1px solid rgba(0,0,0,.08); }

.table-responsive-mobile{
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.table-responsive-mobile table{ min-width: 880px; }

.table thead th{ white-space: nowrap; }
.row-hover tr{ transition: background-color .12s ease; }
.row-hover tr:hover{ background: rgba(13,110,253,.04); }

.badge-soft{
  border-radius: 999px;
  border: 1px solid rgba(0,0,0,.10);
  padding: .35rem .6rem;
  font-weight: 850;
  text-transform: lowercase;
}

@media (max-width: 576px){
  .btn-stack .btn{ width: 100%; }
}
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
  <div>
    <h1 class="page-title"><?= $isEdit ? 'Editar proposta' : 'Nova proposta' ?></h1>
    <div class="page-sub">Preencha dados e itens, depois gere o PDF</div>
  </div>

  <div class="d-flex gap-2 flex-wrap btn-stack">
    <?php if ($isEdit): ?>
      <a class="btn btn-outline-dark"
         href="/financas/public/?url=propostas-pdf&id=<?= (int)$proposta['id'] ?>">
        Baixar PDF
      </a>
    <?php endif; ?>

    <a class="btn btn-outline-secondary" href="/financas/public/?url=propostas">
      Voltar
    </a>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= h($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<form method="POST" action="<?= h($action) ?>">
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int)$proposta['id'] ?>">
  <?php endif; ?>

  <!-- vínculo com cliente (se selecionar) -->
  <input type="hidden" name="id_cliente" id="id_cliente" value="<?= $idClienteAtual ? (int)$idClienteAtual : '' ?>">
  <input type="hidden" name="oportunidade_id" value="<?= (int)($oportunidade_id ?? 0) ?>">

  <div class="row g-3">
    <!-- COL ESQ -->
    <div class="col-lg-4">
      <div class="card-soft micro h-100">
        <div class="card-body">

          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <div class="section-title">Dados</div>
              <div class="mini-hint">Número, status e validade</div>
            </div>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <label class="form-label">Número</label>
              <input name="numero" class="form-control"
                     value="<?= h($proposta['numero'] ?? '') ?>"
                     <?= $isEdit ? 'readonly' : '' ?>>
              <?php if ($isEdit): ?>
                <div class="form-text">O número não pode ser alterado.</div>
              <?php endif; ?>
            </div>

            <div class="col-6">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <?php
                  $st = $proposta['status'] ?? 'rascunho';
                  $ops = ['rascunho','enviado','aprovado','recusado'];
                  foreach ($ops as $o):
                ?>
                  <option value="<?= h($o) ?>" <?= $st===$o?'selected':'' ?>><?= ucfirst($o) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-6">
              <label class="form-label">Emissão</label>
              <input type="date" name="data_emissao" class="form-control"
                     value="<?= h($proposta['data_emissao'] ?? date('Y-m-d')) ?>">
            </div>

            <div class="col-6">
              <label class="form-label">Validade (dias)</label>
              <input type="number" name="validade_dias" class="form-control"
                     value="<?= (int)($proposta['validade_dias'] ?? 15) ?>">
            </div>
          </div>

          <hr class="hr-soft my-3">

          <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <div>
              <div class="section-title">Cliente</div>
              <div class="mini-hint">Selecione ou preencha manualmente</div>
            </div>

            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimparCliente">
              Limpar seleção
            </button>
          </div>

          <div class="mt-2">
            <label class="form-label">Cliente existente</label>
            <select class="form-select" id="selectCliente">
              <option value="">— Selecionar —</option>
              <?php foreach ($clientes as $c): ?>
                <?php $cid = (int)($c['id'] ?? 0); ?>
                <option value="<?= $cid ?>" <?= $idClienteAtual === $cid ? 'selected' : '' ?>>
                  <?= h($c['nome'] ?? '') ?>
                  <?php if (!empty($c['documento'])): ?> • <?= h($c['documento']) ?><?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Ao selecionar, os campos abaixo são preenchidos automaticamente.</div>
          </div>

          <div class="mt-2">
            <label class="form-label">Nome</label>
            <input name="cliente_nome" id="cliente_nome" class="form-control" required
                   value="<?= h($proposta['cliente_nome'] ?? '') ?>">
          </div>

          <div class="mt-2">
            <label class="form-label">E-mail</label>
            <input name="cliente_email" id="cliente_email" class="form-control"
                   value="<?= h($proposta['cliente_email'] ?? '') ?>">
          </div>

          <div class="mt-2">
            <label class="form-label">Telefone</label>
            <input name="cliente_telefone" id="cliente_telefone" class="form-control"
                   value="<?= h($proposta['cliente_telefone'] ?? '') ?>">
          </div>

          <div class="mt-2">
            <label class="form-label">Endereço</label>
            <input name="cliente_endereco" id="cliente_endereco" class="form-control"
                   value="<?= h($proposta['cliente_endereco'] ?? '') ?>">
          </div>

          <div class="mt-2">
            <label class="form-label">Forma de pagamento</label>
            <input name="forma_pagamento" class="form-control"
                   value="<?= h($proposta['forma_pagamento'] ?? '') ?>">
          </div>

        </div>
      </div>
    </div>

    <!-- COL DIR -->
    <div class="col-lg-8">
      <!-- ITENS -->
      <div class="card-soft micro mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div>
              <div class="section-title">Itens</div>
              <div class="mini-hint">Descrição, quantidade e valor unitário</div>
            </div>

            <button class="btn btn-outline-primary btn-sm" type="button" id="btnAddItem">
              + Adicionar item
            </button>
          </div>

          <div class="table-responsive-mobile">
            <table class="table align-middle mb-0 row-hover" id="tblItens">
              <thead class="table-light">
                <tr>
                  <th style="width:48px">#</th>
                  <th>Descrição</th>
                  <th style="width:120px" class="text-end">Qtd</th>
                  <th style="width:160px" class="text-end">Valor unit.</th>
                  <th style="width:56px" class="text-end"> </th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($itens as $idx => $it): ?>
                  <tr>
                    <td class="text-muted"><?= (int)$idx + 1 ?></td>
                    <td>
                      <input class="form-control"
                             name="itens[<?= (int)$idx ?>][descricao]"
                             value="<?= h($it['descricao'] ?? '') ?>"
                             placeholder="Ex: Serviço X" required>
                    </td>
                    <td>
                      <input class="form-control text-end money-br"
                             name="itens[<?= (int)$idx ?>][quantidade]"
                             value="<?= number_format((float)($it['quantidade'] ?? 1), 2, ',', '.') ?>"
                             placeholder="1,00">
                    </td>
                    <td>
                      <input class="form-control text-end money-br"
                             name="itens[<?= (int)$idx ?>][valor_unit]"
                             value="<?= number_format((float)($it['valor_unit'] ?? 0), 2, ',', '.') ?>"
                             placeholder="0,00">
                    </td>
                    <td class="text-end">
                      <button class="btn btn-outline-danger btn-sm btnDelItem" type="button">Remover</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-end mt-3">
            <?php
              $totalPreview = (float)($proposta['total'] ?? 0);
            ?>
            <div class="text-end">
              <div class="text-muted small">Total (preview)</div>
              <div class="fs-4 fw-bold" id="totalPreview">R$ <?= number_format($totalPreview, 2, ',', '.') ?></div>
              <div class="text-muted small">O total final será recalculado ao salvar.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- TEXTOS -->
      <div class="card-soft micro">
        <div class="card-body">
          <div class="section-title mb-2">Detalhes</div>
          <div class="mini-hint mb-3">Observações e considerações que vão no PDF</div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Observações</label>
              <textarea name="observacoes" rows="6" class="form-control"><?= h($proposta['observacoes'] ?? '') ?></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Considerações finais</label>
              <textarea name="consideracoes" rows="6" class="form-control"><?= h($proposta['consideracoes'] ?? '') ?></textarea>
            </div>
          </div>

          <div class="d-flex gap-2 flex-wrap mt-3 btn-stack">
            <button class="btn btn-primary">Salvar</button>

            <?php if ($isEdit): ?>
              <a class="btn btn-outline-dark"
                 href="/financas/public/?url=propostas-pdf&id=<?= (int)$proposta['id'] ?>">
                 Baixar PDF
              </a>
            <?php endif; ?>

            <a class="btn btn-outline-secondary" href="/financas/public/?url=propostas">Cancelar</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
(function(){
  // clientes para preencher campos (sem mexer no backend)
  const CLIENTES = <?= json_encode($clientes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  const sel = document.getElementById('selectCliente');
  const hid = document.getElementById('id_cliente');

  const inNome  = document.getElementById('cliente_nome');
  const inEmail = document.getElementById('cliente_email');
  const inTel   = document.getElementById('cliente_telefone');
  const inEnd   = document.getElementById('cliente_endereco');

  const btnLimpar = document.getElementById('btnLimparCliente');

  function findClienteById(id){
    id = parseInt(id || "0", 10);
    if(!id) return null;
    return (CLIENTES || []).find(c => parseInt(c.id || 0, 10) === id) || null;
  }

  function aplicarCliente(cli){
    if(!cli) return;
    hid.value   = cli.id || '';
    inNome.value  = cli.nome || '';
    inEmail.value = cli.email || '';
    inTel.value   = cli.telefone || '';
    inEnd.value   = cli.endereco || '';
  }

  function limparCliente(){
    if(sel) sel.value = '';
    if(hid) hid.value = '';
    inNome.value = '';
    inEmail.value = '';
    inTel.value = '';
    inEnd.value = '';
  }

  sel?.addEventListener('change', function(){
    const cli = findClienteById(this.value);
    if(cli) aplicarCliente(cli);
    else if(hid) hid.value = '';
  });

  btnLimpar?.addEventListener('click', function(){
    limparCliente();
  });

  // se abrir em edição e já tiver id_cliente, tenta preencher
  if(hid?.value){
    const cli = findClienteById(hid.value);
    if(cli){
      if(sel) sel.value = String(cli.id);
      aplicarCliente(cli);
    }
  }

  // itens
  const tbl = document.getElementById('tblItens');
  const btnAdd = document.getElementById('btnAddItem');

  function renumera(){
    const rows = tbl.querySelectorAll('tbody tr');
    rows.forEach((tr, i) => {
      const firstTd = tr.querySelector('td');
      if(firstTd) firstTd.textContent = (i+1);

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
        <button class="btn btn-outline-danger btn-sm btnDelItem" type="button">Remover</button>
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
