<?php
$etapas = $etapas ?? \Oportunidade::etapas();
$cardsPorEtapa = $cardsPorEtapa ?? [];
$clientes = $clientes ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="mb-0">Funil de Vendas</h1>
    <div class="text-muted">Arraste os cards entre etapas e organize sua negociação</div>
  </div>

  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaOportunidade">
    <i class="bi bi-plus-lg"></i> Nova oportunidade
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
  .kanban-board { display:flex; gap:14px; overflow:auto; padding-bottom:10px; }
  .kanban-col { min-width: 290px; max-width: 320px; flex: 0 0 auto; }
  .kanban-head {
    background:#fff; border:1px solid rgba(0,0,0,.08);
    border-radius:14px; padding:10px 12px; margin-bottom:10px;
    display:flex; justify-content:space-between; align-items:center;
  }
  .kanban-drop {
    background: rgba(255,255,255,.6);
    border: 1px dashed rgba(0,0,0,.12);
    border-radius:14px;
    padding:10px;
    min-height: 120px;
  }
  .kanban-card {
    background:#fff;
    border:1px solid rgba(0,0,0,.08);
    border-radius:14px;
    padding:10px 10px;
    margin-bottom:10px;
    cursor: grab;
    box-shadow: 0 4px 16px rgba(0,0,0,.04);
  }
  .kanban-card.dragging { opacity: .65; }
  .kanban-title { font-weight:700; margin-bottom:4px; }
  .kanban-meta { font-size:12px; color:#6b7280; }
  .kanban-actions { margin-top:8px; display:flex; gap:6px; }
  .drop-hover { background: rgba(13,110,253,.06); border-color: rgba(13,110,253,.35); }
</style>

<div class="kanban-board">
  <?php foreach ($etapas as $key => $label): 
      $cards = $cardsPorEtapa[$key] ?? [];
      $totalEtapa = 0;
      foreach ($cards as $c) $totalEtapa += (float)($c['valor'] ?? 0);
  ?>
    <div class="kanban-col">
      <div class="kanban-head">
        <div class="fw-semibold"><?= htmlspecialchars($label) ?></div>
        <div class="text-muted small">
          <?= count($cards) ?> • R$ <?= number_format($totalEtapa, 2, ',', '.') ?>
        </div>
      </div>

      <div class="kanban-drop" data-etapa="<?= htmlspecialchars($key) ?>">
        <?php foreach ($cards as $c): ?>
          <div class="kanban-card" draggable="true" data-id="<?= (int)$c['id'] ?>">
            <div class="kanban-title"><?= htmlspecialchars($c['titulo'] ?? '') ?></div>

            <div class="kanban-meta">
              <?php if (!empty($c['cliente_nome'])): ?>
                <div><i class="bi bi-person"></i> <?= htmlspecialchars($c['cliente_nome']) ?></div>
              <?php endif; ?>

              <?php if (!empty($c['valor'])): ?>
                <div><i class="bi bi-currency-dollar"></i> R$ <?= number_format((float)$c['valor'], 2, ',', '.') ?></div>
              <?php endif; ?>

              <?php if (!empty($c['data_prevista'])): ?>
                <div><i class="bi bi-calendar-event"></i> <?= date('d/m/Y', strtotime($c['data_prevista'])) ?></div>
              <?php endif; ?>
            </div>

            <div class="kanban-actions">
              <button class="btn btn-outline-secondary btn-sm btn-edit"
                      type="button"
                      data-bs-toggle="modal"
                      data-bs-target="#modalEditar"
                      data-id="<?= (int)$c['id'] ?>"
                      data-titulo="<?= htmlspecialchars($c['titulo'] ?? '', ENT_QUOTES) ?>"
                      data-descricao="<?= htmlspecialchars($c['descricao'] ?? '', ENT_QUOTES) ?>"
                      data-valor="<?= !empty($c['valor']) ? number_format((float)$c['valor'], 2, ',', '.') : '' ?>"
                      data-id_cliente="<?= (int)($c['id_cliente'] ?? 0) ?>"
                      data-data_prevista="<?= htmlspecialchars($c['data_prevista'] ?? '') ?>"
              >
                <i class="bi bi-pencil"></i>
              </button>

              <form method="POST" action="/financas/public/?url=funil-delete" onsubmit="return confirm('Remover oportunidade?')">
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
              <a class="btn btn-outline-primary btn-sm"
   href="/financas/public/?url=propostas-new&op=<?= (int)$c['id'] ?>">
  <i class="bi bi-file-earmark-text"></i>
</a>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
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

      // se mudou de coluna -> salva origem e destino
      if (sourceCol && sourceCol !== destCol) {
        updates.push({
          etapa: sourceCol.dataset.etapa,
          ids: idsFromColumn(sourceCol)
        });
      }

      // sempre salva destino
      updates.push({
        etapa: destCol.dataset.etapa,
        ids: idsFromColumn(destCol)
      });

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
      if (offset < 0 && offset > closest.offset) {
        return { offset: offset, element: child };
      }
      return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
  }
})();
</script>

