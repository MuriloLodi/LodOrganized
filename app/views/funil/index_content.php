<?php
$etapas = $etapas ?? \Oportunidade::etapas();
$cardsPorEtapa = $cardsPorEtapa ?? [];
$clientes = $clientes ?? [];
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
  <div>
    <h1 class="mb-0">Funil de Vendas</h1>
    <div class="text-muted">Arraste os cards entre etapas e organize sua negociação</div>
  </div>

  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaOportunidade">
    <i class="bi bi-plus-lg me-1"></i> Nova oportunidade
  </button>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['sucesso'])): ?>
  <div class="alert alert-success">
    <?= htmlspecialchars($_SESSION['sucesso']); unset($_SESSION['sucesso']); ?>
  </div>
<?php endif; ?>

<style>
  /* ===== PADRÃO FRONT (Kanban) ===== */
  :root{
    --soft-border: rgba(0,0,0,.08);
    --soft-shadow-sm: 0 10px 26px rgba(0,0,0,.08);
    --soft-shadow: 0 16px 44px rgba(0,0,0,.10);
  }

  .kanban-wrap{
    border: 1px solid var(--soft-border);
    border-radius: 18px;
    background: #fff;
    box-shadow: var(--soft-shadow-sm);
    overflow: hidden;
  }

  .kanban-toolbar{
    padding: 12px 14px;
    border-bottom: 1px solid var(--soft-border);
    background: rgba(248,249,250,.55);
  }

  .kanban-board{
    display:flex;
    gap: 14px;
    overflow:auto;
    padding: 14px;
    -webkit-overflow-scrolling: touch;
  }

  .kanban-col{
    min-width: 300px;
    max-width: 340px;
    flex: 0 0 auto;
  }

  .kanban-head{
    background:#fff;
    border:1px solid var(--soft-border);
    border-radius:16px;
    padding:10px 12px;
    margin-bottom:10px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow: 0 8px 18px rgba(0,0,0,.05);
  }

  .kanban-head .title{
    font-weight: 850;
    letter-spacing: -.2px;
    margin: 0;
  }

  .kanban-head .meta{
    color:#6c757d;
    font-size: .85rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .kanban-drop{
    background: rgba(248,249,250,.55);
    border: 1px dashed rgba(0,0,0,.14);
    border-radius:16px;
    padding:10px;
    min-height: 140px;
  }

  .kanban-card{
    background:#fff;
    border:1px solid var(--soft-border);
    border-radius:16px;
    padding:10px 10px;
    margin-bottom:10px;
    cursor: grab;
    box-shadow: 0 10px 22px rgba(0,0,0,.06);
  }
  .kanban-card:last-child{ margin-bottom: 0; }

  .kanban-card.dragging{ opacity:.65; }

  .kanban-title{
    font-weight: 850;
    letter-spacing: -.2px;
    margin-bottom: 6px;
    line-height: 1.15;
  }

  .kanban-meta{
    font-size: .86rem;
    color:#6c757d;
    display:flex;
    flex-direction: column;
    gap: 2px;
  }
  .kanban-meta i{ margin-right: 6px; }

  .kanban-actions{
    margin-top:10px;
    display:flex;
    gap:6px;
    flex-wrap: wrap;
  }

  .drop-hover{
    background: rgba(13,110,253,.06);
    border-color: rgba(13,110,253,.35);
  }

  .pill{
    border: 1px solid var(--soft-border);
    border-radius: 999px;
    padding: .28rem .55rem;
    background: rgba(255,255,255,.65);
    color: #6c757d;
    font-weight: 750;
    font-size: .8rem;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
  }

  @media (max-width: 576px){
    .kanban-board{ padding: 12px; }
    .kanban-col{ min-width: 280px; }
    .kanban-head, .kanban-drop, .kanban-card{ border-radius: 14px; }
  }
</style>

<div class="kanban-wrap">
  <div class="kanban-toolbar d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
      <span class="pill"><i class="bi bi-arrows-move"></i> Arraste para mover</span>
      <span class="pill"><i class="bi bi-lightning-charge"></i> Salva automaticamente</span>
    </div>
    <div class="text-muted small">
      Dica: solte o card na posição desejada dentro da etapa.
    </div>
  </div>

  <div class="kanban-board">
    <?php foreach ($etapas as $key => $label):
      $cards = $cardsPorEtapa[$key] ?? [];
      $totalEtapa = 0;
      foreach ($cards as $c) $totalEtapa += (float)($c['valor'] ?? 0);
    ?>
      <div class="kanban-col">
        <div class="kanban-head">
          <div class="title"><?= htmlspecialchars($label) ?></div>
          <div class="meta">
            <?= count($cards) ?> • R$ <?= number_format($totalEtapa, 2, ',', '.') ?>
          </div>
        </div>

        <div class="kanban-drop" data-etapa="<?= htmlspecialchars($key) ?>">
          <?php if (empty($cards)): ?>
            <div class="text-muted small p-2">
              Sem oportunidades aqui.
            </div>
          <?php endif; ?>

          <?php foreach ($cards as $c): ?>
            <div class="kanban-card" draggable="true" data-id="<?= (int)$c['id'] ?>">
              <div class="kanban-title"><?= htmlspecialchars($c['titulo'] ?? '') ?></div>

              <div class="kanban-meta">
                <?php if (!empty($c['cliente_nome'])): ?>
                  <div><i class="bi bi-person"></i><?= htmlspecialchars($c['cliente_nome']) ?></div>
                <?php endif; ?>

                <?php if (!empty($c['valor'])): ?>
                  <div><i class="bi bi-cash-coin"></i>R$ <?= number_format((float)$c['valor'], 2, ',', '.') ?></div>
                <?php endif; ?>

                <?php if (!empty($c['data_prevista'])): ?>
                  <div><i class="bi bi-calendar-event"></i><?= date('d/m/Y', strtotime($c['data_prevista'])) ?></div>
                <?php endif; ?>
              </div>

              <div class="kanban-actions">
                <button class="btn btn-outline-secondary btn-sm"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditar"
                        data-id="<?= (int)$c['id'] ?>"
                        data-titulo="<?= htmlspecialchars($c['titulo'] ?? '', ENT_QUOTES) ?>"
                        data-descricao="<?= htmlspecialchars($c['descricao'] ?? '', ENT_QUOTES) ?>"
                        data-valor="<?= !empty($c['valor']) ? number_format((float)$c['valor'], 2, ',', '.') : '' ?>"
                        data-id_cliente="<?= (int)($c['id_cliente'] ?? 0) ?>"
                        data-data_prevista="<?= htmlspecialchars($c['data_prevista'] ?? '') ?>">
                  <i class="bi bi-pencil"></i>
                </button>

                <a class="btn btn-outline-primary btn-sm"
                   href="/financas/public/?url=propostas-new&op=<?= (int)$c['id'] ?>"
                   title="Gerar proposta">
                  <i class="bi bi-file-earmark-text"></i>
                </a>

                <form method="POST"
                      action="/financas/public/?url=funil-delete"
                      onsubmit="return confirm('Remover oportunidade?')">
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <button class="btn btn-outline-danger btn-sm" type="submit" title="Excluir">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- MODAL: NOVA -->
<div class="modal fade" id="modalNovaOportunidade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="/financas/public/?url=funil-store">
      <div class="modal-header">
        <h5 class="modal-title">Nova oportunidade</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Título</label>
          <input class="form-control" name="titulo" required>
        </div>

        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Etapa</label>
            <select class="form-select" name="etapa">
              <?php foreach ($etapas as $k => $lab): ?>
                <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($lab) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Valor (opcional)</label>
            <input class="form-control money-br" name="valor" placeholder="0,00">
          </div>
        </div>

        <div class="row g-2 mt-1">
          <div class="col-12">
            <label class="form-label">Cliente (opcional)</label>
            <select class="form-select" name="id_cliente">
              <option value="0">—</option>
              <?php foreach ($clientes as $cl): ?>
                <option value="<?= (int)$cl['id'] ?>"><?= htmlspecialchars($cl['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Data prevista (opcional)</label>
            <input class="form-control" type="date" name="data_prevista">
          </div>

          <div class="col-12">
            <label class="form-label">Descrição (opcional)</label>
            <textarea class="form-control" name="descricao" rows="3"></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="/financas/public/?url=funil-update">
      <div class="modal-header">
        <h5 class="modal-title">Editar oportunidade</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="edit_id">

        <div class="mb-2">
          <label class="form-label">Título</label>
          <input class="form-control" name="titulo" id="edit_titulo" required>
        </div>

        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Valor</label>
            <input class="form-control money-br" name="valor" id="edit_valor" placeholder="0,00">
          </div>
          <div class="col-6">
            <label class="form-label">Data prevista</label>
            <input class="form-control" type="date" name="data_prevista" id="edit_data_prevista">
          </div>
        </div>

        <div class="mt-2">
          <label class="form-label">Cliente</label>
          <select class="form-select" name="id_cliente" id="edit_id_cliente">
            <option value="0">—</option>
            <?php foreach ($clientes as $cl): ?>
              <option value="<?= (int)$cl['id'] ?>"><?= htmlspecialchars($cl['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mt-2">
          <label class="form-label">Descrição</label>
          <textarea class="form-control" name="descricao" id="edit_descricao" rows="3"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  // ===== Drag & Drop (mantém seu backend /funil-move) =====
  let dragged = null;
  let sourceCol = null;

  function idsFromColumn(col){
    return Array.from(col.querySelectorAll('.kanban-card')).map(c => c.dataset.id);
  }

  document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragstart', () => {
      dragged = card;
      sourceCol = card.closest('.kanban-drop');
      card.classList.add('dragging');
    });

    card.addEventListener('dragend', () => {
      card.classList.remove('dragging');
      dragged = null;
      sourceCol = null;
    });
  });

  document.querySelectorAll('.kanban-drop').forEach(col => {
    col.addEventListener('dragover', (e) => {
      e.preventDefault();
      col.classList.add('drop-hover');

      const after = getDragAfterElement(col, e.clientY);
      if(!dragged) return;

      if(after == null) col.appendChild(dragged);
      else col.insertBefore(dragged, after);
    });

    col.addEventListener('dragleave', () => col.classList.remove('drop-hover'));

    col.addEventListener('drop', async (e) => {
      e.preventDefault();
      col.classList.remove('drop-hover');
      if(!dragged) return;

      const destCol = col;
      const updates = [];

      if (sourceCol && sourceCol !== destCol) {
        updates.push({ etapa: sourceCol.dataset.etapa, ids: idsFromColumn(sourceCol) });
      }
      updates.push({ etapa: destCol.dataset.etapa, ids: idsFromColumn(destCol) });

      try {
        await fetch('/financas/public/?url=funil-move', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ updates })
        });
      } catch (err) {
        console.error(err);
        alert('Falha ao salvar posição. Recarregue a página.');
      }
    });
  });

  function getDragAfterElement(container, y) {
    const els = [...container.querySelectorAll('.kanban-card:not(.dragging)')];
    return els.reduce((closest, child) => {
      const box = child.getBoundingClientRect();
      const offset = y - box.top - box.height / 2;
      if (offset < 0 && offset > closest.offset) return { offset, element: child };
      return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
  }

  // ===== Modal editar (preenche campos) =====
  const modal = document.getElementById('modalEditar');
  if(modal){
    modal.addEventListener('show.bs.modal', function(ev){
      const btn = ev.relatedTarget;
      if(!btn) return;

      document.getElementById('edit_id').value = btn.getAttribute('data-id') || '';
      document.getElementById('edit_titulo').value = btn.getAttribute('data-titulo') || '';
      document.getElementById('edit_descricao').value = btn.getAttribute('data-descricao') || '';
      document.getElementById('edit_valor').value = btn.getAttribute('data-valor') || '';
      document.getElementById('edit_id_cliente').value = btn.getAttribute('data-id_cliente') || '0';
      document.getElementById('edit_data_prevista').value = btn.getAttribute('data-data_prevista') || '';
    });
  }
})();
</script>
