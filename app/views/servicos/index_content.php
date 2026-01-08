<?php
$q = trim($_GET['q'] ?? '');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="mb-0">Serviços</h1>
    <div class="text-muted">Cadastre e gerencie seus serviços</div>
  </div>

  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalServicoNovo">
    <i class="bi bi-plus-lg me-1"></i> Novo serviço
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

<div class="card mb-3">
  <div class="card-body">
    <form class="row g-2 align-items-end" method="GET" action="/financas/public/">
      <input type="hidden" name="url" value="servicos">
      <div class="col-12 col-md-8">
        <label class="form-label">Buscar</label>
        <input class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nome ou descrição...">
      </div>
      <div class="col-12 col-md-4 d-flex gap-2">
        <button class="btn btn-outline-secondary w-100" type="submit">
          <i class="bi bi-search me-1"></i> Filtrar
        </button>
        <a class="btn btn-outline-secondary" href="/financas/public/?url=servicos" title="Limpar">
          <i class="bi bi-x-lg"></i>
        </a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <?php if (empty($servicos)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-briefcase fs-1 d-block mb-2"></i>
        Nenhum serviço encontrado.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Serviço</th>
              <th style="width:140px;">Preço</th>
              <th style="width:140px;">Duração</th>
              <th style="width:120px;">Status</th>
              <th style="width:200px;" class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($servicos as $s): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= htmlspecialchars($s['nome']) ?></div>
                  <?php if (!empty($s['descricao'])): ?>
                    <div class="text-muted small text-truncate" style="max-width:520px;">
                      <?= htmlspecialchars($s['descricao']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <td>R$ <?= number_format((float)$s['preco'], 2, ',', '.') ?></td>

                <td>
                  <?= $s['duracao_min'] ? (int)$s['duracao_min'] . ' min' : '<span class="text-muted">—</span>' ?>
                </td>

                <td>
                  <?php if ((int)$s['ativo'] === 1): ?>
                    <span class="badge bg-success">Ativo</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Inativo</span>
                  <?php endif; ?>
                </td>

                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary"
                     href="/financas/public/?url=servicos-toggle&id=<?= (int)$s['id'] ?>">
                    <?= ((int)$s['ativo'] === 1) ? 'Inativar' : 'Ativar' ?>
                  </a>

                  <button class="btn btn-sm btn-outline-primary"
                          data-bs-toggle="modal"
                          data-bs-target="#modalServicoEditar"
                          data-id="<?= (int)$s['id'] ?>"
                          data-nome="<?= htmlspecialchars($s['nome'], ENT_QUOTES) ?>"
                          data-descricao="<?= htmlspecialchars($s['descricao'] ?? '', ENT_QUOTES) ?>"
                          data-preco="<?= htmlspecialchars((string)$s['preco'], ENT_QUOTES) ?>"
                          data-duracao="<?= htmlspecialchars((string)($s['duracao_min'] ?? ''), ENT_QUOTES) ?>"
                          data-ativo="<?= (int)$s['ativo'] ?>">
                    Editar
                  </button>

                  <a class="btn btn-sm btn-outline-danger"
                     href="/financas/public/?url=servicos-delete&id=<?= (int)$s['id'] ?>"
                     onclick="return confirm('Remover este serviço?');">
                    Excluir
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL NOVO -->
<div class="modal fade" id="modalServicoNovo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" action="/financas/public/?url=servicos-store">
      <div class="modal-header">
        <h5 class="modal-title">Novo serviço</h5>
        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-7">
            <label class="form-label">Nome</label>
            <input class="form-control" name="nome" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Preço</label>
            <input class="form-control" name="preco" placeholder="0,00">
          </div>

          <div class="col-md-2">
            <label class="form-label">Duração (min)</label>
            <input class="form-control" name="duracao_min" type="number" min="0" step="1">
          </div>

          <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea class="form-control" name="descricao" rows="3"></textarea>
          </div>

          <div class="col-12">
            <div class="form-check mt-1">
              <input class="form-check-input" type="checkbox" name="ativo" id="novoAtivo" checked>
              <label class="form-check-label" for="novoAtivo">Ativo</label>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary">Salvar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modalServicoEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" action="/financas/public/?url=servicos-update">
      <input type="hidden" name="id" id="editId">

      <div class="modal-header">
        <h5 class="modal-title">Editar serviço</h5>
        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-7">
            <label class="form-label">Nome</label>
            <input class="form-control" name="nome" id="editNome" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Preço</label>
            <input class="form-control" name="preco" id="editPreco" placeholder="0,00">
          </div>

          <div class="col-md-2">
            <label class="form-label">Duração (min)</label>
            <input class="form-control" name="duracao_min" id="editDuracao" type="number" min="0" step="1">
          </div>

          <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea class="form-control" name="descricao" id="editDescricao" rows="3"></textarea>
          </div>

          <div class="col-12">
            <div class="form-check mt-1">
              <input class="form-check-input" type="checkbox" name="ativo" id="editAtivo">
              <label class="form-check-label" for="editAtivo">Ativo</label>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary">Salvar alterações</button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  const modal = document.getElementById('modalServicoEditar');
  if (!modal) return;

  modal.addEventListener('show.bs.modal', function (ev) {
    const btn = ev.relatedTarget;
    if (!btn) return;

    document.getElementById('editId').value = btn.getAttribute('data-id') || '';
    document.getElementById('editNome').value = btn.getAttribute('data-nome') || '';
    document.getElementById('editDescricao').value = btn.getAttribute('data-descricao') || '';
    document.getElementById('editPreco').value = btn.getAttribute('data-preco') || '';
    document.getElementById('editDuracao').value = btn.getAttribute('data-duracao') || '';
    document.getElementById('editAtivo').checked = (btn.getAttribute('data-ativo') === '1');
  });
})();
</script>
