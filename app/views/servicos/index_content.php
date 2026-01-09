<?php
$q = trim($_GET['q'] ?? '');
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<style>
/* ===== PADRÃO VISUAL (igual tela Clientes) ===== */
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

.section-title{ font-weight: 850; letter-spacing: -.2px; margin:0; }

.table-responsive-mobile{
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.table-responsive-mobile table{ min-width: 920px; }

.table thead th{ white-space: nowrap; }
.row-hover tr{ transition: background-color .12s ease; }
.row-hover tr:hover{ background: rgba(13,110,253,.04); }

.empty-state{
  padding: 3rem 1rem;
  border-radius: 16px;
  border: 1px dashed rgba(0,0,0,.15);
  background: rgba(0,0,0,.02);
}

.modal-content{
  border-radius: 18px;
  border: 1px solid rgba(0,0,0,.08);
  box-shadow: 0 18px 44px rgba(0,0,0,.18);
}
.modal-header{ border-bottom: 1px solid rgba(0,0,0,.08); }
.modal-footer{ border-top: 1px solid rgba(0,0,0,.08); }

@media (max-width: 576px){
  .btn-stack .btn{ width: 100%; }
}
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
  <div>
    <h1 class="page-title">Serviços</h1>
    <div class="page-sub">Cadastre e gerencie seus serviços</div>
  </div>

  <div class="d-flex gap-2 flex-wrap btn-stack">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalServicoNovo">
      Novo serviço
    </button>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= h($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['sucesso'])): ?>
  <div class="alert alert-success">
    <?= h($_SESSION['sucesso']); unset($_SESSION['sucesso']); ?>
  </div>
<?php endif; ?>

<!-- FILTRO -->
<div class="card-soft micro mb-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
      <div class="section-title">Busca</div>
      <div class="text-muted small">Nome ou descrição</div>
    </div>

    <form class="row g-2 align-items-end" method="GET" action="/financas/public/">
      <input type="hidden" name="url" value="servicos">

      <div class="col-12 col-md-8">
        <label class="form-label">Pesquisar</label>
        <input class="form-control" name="q" value="<?= h($q) ?>" placeholder="Digite para filtrar...">
      </div>

      <div class="col-12 col-md-4 d-flex gap-2 flex-wrap">
        <button class="btn btn-outline-secondary w-100" type="submit">
          Filtrar
        </button>
        <a class="btn btn-outline-secondary" href="/financas/public/?url=servicos" title="Limpar">
          Limpar
        </a>
      </div>
    </form>
  </div>
</div>

<!-- LISTA -->
<div class="card-soft micro">
  <div class="card-body">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
      <div class="section-title">Lista de serviços</div>
      <div class="text-muted small"><?= $q !== '' ? 'Filtro aplicado' : 'Todos os serviços' ?></div>
    </div>

    <?php if (empty($servicos)): ?>
      <div class="empty-state text-center text-muted">
        <div class="fw-semibold mb-1">Nenhum serviço encontrado</div>
        <div class="small">Cadastre um serviço novo ou ajuste o filtro.</div>
      </div>
    <?php else: ?>
      <div class="table-responsive-mobile">
        <table class="table align-middle mb-0 row-hover">
          <thead class="table-light">
            <tr>
              <th>Serviço</th>
              <th style="width: 160px;">Preço</th>
              <th style="width: 160px;">Duração</th>
              <th style="width: 140px;">Status</th>
              <th style="width: 220px;" class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($servicos as $s): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= h($s['nome'] ?? '') ?></div>
                  <?php if (!empty($s['descricao'])): ?>
                    <div class="text-muted small text-truncate" style="max-width: 560px;">
                      <?= h($s['descricao']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <td>R$ <?= number_format((float)($s['preco'] ?? 0), 2, ',', '.') ?></td>

                <td>
                  <?php if (!empty($s['duracao_min'])): ?>
                    <?= (int)$s['duracao_min'] ?> min
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if ((int)($s['ativo'] ?? 0) === 1): ?>
                    <span class="badge bg-success">Ativo</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Inativo</span>
                  <?php endif; ?>
                </td>

                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary"
                     href="/financas/public/?url=servicos-toggle&id=<?= (int)($s['id'] ?? 0) ?>">
                    <?= ((int)($s['ativo'] ?? 0) === 1) ? 'Inativar' : 'Ativar' ?>
                  </a>

                  <button class="btn btn-sm btn-outline-primary"
                          data-bs-toggle="modal"
                          data-bs-target="#modalServicoEditar"
                          data-id="<?= (int)($s['id'] ?? 0) ?>"
                          data-nome="<?= h($s['nome'] ?? '') ?>"
                          data-descricao="<?= h($s['descricao'] ?? '') ?>"
                          data-preco="<?= h((string)($s['preco'] ?? '')) ?>"
                          data-duracao="<?= h((string)($s['duracao_min'] ?? '')) ?>"
                          data-ativo="<?= (int)($s['ativo'] ?? 0) ?>">
                    Editar
                  </button>

                  <a class="btn btn-sm btn-outline-danger"
                     href="/financas/public/?url=servicos-delete&id=<?= (int)($s['id'] ?? 0) ?>"
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
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="POST" action="/financas/public/?url=servicos-store">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0">Novo serviço</h5>
          <div class="text-muted small">Preencha os dados abaixo</div>
        </div>
        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-7">
            <label class="form-label">Nome *</label>
            <input class="form-control" name="nome" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Preço</label>
            <input class="form-control money-br" inputmode="numeric" name="preco" placeholder="0,00">
          </div>

          <div class="col-md-2">
            <label class="form-label">Duração (min)</label>
            <input class="form-control" name="duracao_min" type="number" min="0" step="1" placeholder="0">
          </div>

          <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea class="form-control" name="descricao" rows="3" placeholder="Opcional"></textarea>
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
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="POST" action="/financas/public/?url=servicos-update">
      <input type="hidden" name="id" id="editId">

      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0">Editar serviço</h5>
          <div class="text-muted small">Ajuste e salve as alterações</div>
        </div>
        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-7">
            <label class="form-label">Nome *</label>
            <input class="form-control" name="nome" id="editNome" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Preço</label>
            <input class="form-control money-br" inputmode="numeric" name="preco" id="editPreco" placeholder="0,00">
          </div>

          <div class="col-md-2">
            <label class="form-label">Duração (min)</label>
            <input class="form-control" name="duracao_min" id="editDuracao" type="number" min="0" step="1" placeholder="0">
          </div>

          <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea class="form-control" name="descricao" id="editDescricao" rows="3" placeholder="Opcional"></textarea>
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
