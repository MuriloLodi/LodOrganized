<?php
$q = trim($_GET['q'] ?? '');
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<style>
/* ===== PADRÃO VISUAL (mesmo padrão que apliquei nas outras telas) ===== */
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
    <h1 class="page-title">Clientes</h1>
    <div class="page-sub">Cadastre e gerencie seus clientes</div>
  </div>

  <div class="d-flex gap-2 flex-wrap btn-stack">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente" onclick="openNovoCliente()">
      Novo cliente
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
      <div class="text-muted small">Nome, e-mail, telefone ou documento</div>
    </div>

    <form class="row g-2 align-items-end" method="GET" action="/financas/public/">
      <input type="hidden" name="url" value="clientes">

      <div class="col-md-7">
        <label class="form-label">Pesquisar</label>
        <input class="form-control" name="q" value="<?= h($q) ?>" placeholder="Digite para filtrar...">
      </div>

      <div class="col-md-auto d-flex gap-2 flex-wrap">
        <button class="btn btn-outline-secondary">
          Filtrar
        </button>

        <?php if ($q !== ''): ?>
          <a class="btn btn-outline-secondary" href="/financas/public/?url=clientes">
            Limpar
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- LISTA -->
<div class="card-soft micro">
  <div class="card-body">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
      <div class="section-title">Lista de clientes</div>
      <div class="text-muted small"><?= $q !== '' ? 'Filtro aplicado' : 'Todos os clientes' ?></div>
    </div>

    <?php if (empty($clientes)): ?>
      <div class="empty-state text-center text-muted">
        <div class="fw-semibold mb-1">Nenhum cliente encontrado</div>
        <div class="small">Cadastre um cliente novo ou ajuste o filtro.</div>
      </div>
    <?php else: ?>
      <div class="table-responsive-mobile">
        <table class="table align-middle mb-0 row-hover">
          <thead class="table-light">
            <tr>
              <th>Cliente</th>
              <th class="d-none d-md-table-cell">Contato</th>
              <th class="d-none d-lg-table-cell">Documento</th>
              <th class="d-none d-lg-table-cell">Criado</th>
              <th class="text-end" style="width: 180px;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clientes as $c): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= h($c['nome'] ?? '') ?></div>
                  <?php if (!empty($c['endereco'])): ?>
                    <div class="text-muted small"><?= h($c['endereco']) ?></div>
                  <?php endif; ?>
                </td>

                <td class="d-none d-md-table-cell">
                  <div><?= h($c['email'] ?? '') ?></div>
                  <div class="text-muted small"><?= h($c['telefone'] ?? '') ?></div>
                </td>

                <td class="d-none d-lg-table-cell">
                  <?= h($c['documento'] ?? '') ?>
                </td>

                <td class="d-none d-lg-table-cell text-muted small">
                  <?= !empty($c['criado_em']) ? date('d/m/Y', strtotime($c['criado_em'])) : '' ?>
                </td>

                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary"
                          data-bs-toggle="modal" data-bs-target="#modalCliente"
                          onclick='openEditarCliente(<?= json_encode($c, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'>
                    Editar
                  </button>

                  <form method="POST" action="/financas/public/?url=clientes-delete" class="d-inline"
                        onsubmit="return confirm('Remover este cliente?')">
                    <input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- MODAL (NOVO / EDITAR) -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0" id="modalClienteTitle">Novo cliente</h5>
          <div class="text-muted small">Preencha os dados abaixo</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" id="formCliente" action="/financas/public/?url=clientes-store">
        <div class="modal-body">
          <input type="hidden" name="id" id="cliente_id">

          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Nome *</label>
              <input class="form-control" name="nome" id="cliente_nome" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Documento</label>
              <input class="form-control" name="documento" id="cliente_documento" placeholder="CPF/CNPJ (opcional)">
            </div>

            <div class="col-md-6">
              <label class="form-label">E-mail</label>
              <input class="form-control" type="email" name="email" id="cliente_email">
            </div>

            <div class="col-md-6">
              <label class="form-label">Telefone</label>
              <input class="form-control" name="telefone" id="cliente_telefone">
            </div>

            <div class="col-12">
              <label class="form-label">Endereço</label>
              <input class="form-control" name="endereco" id="cliente_endereco">
            </div>

            <div class="col-12">
              <label class="form-label">Observações</label>
              <textarea class="form-control" rows="3" name="observacoes" id="cliente_observacoes"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" id="btnSalvarCliente">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openNovoCliente() {
  document.getElementById('modalClienteTitle').innerText = 'Novo cliente';
  document.getElementById('formCliente').action = '/financas/public/?url=clientes-store';

  document.getElementById('cliente_id').value = '';
  document.getElementById('cliente_nome').value = '';
  document.getElementById('cliente_documento').value = '';
  document.getElementById('cliente_email').value = '';
  document.getElementById('cliente_telefone').value = '';
  document.getElementById('cliente_endereco').value = '';
  document.getElementById('cliente_observacoes').value = '';
}

function openEditarCliente(c) {
  document.getElementById('modalClienteTitle').innerText = 'Editar cliente';
  document.getElementById('formCliente').action = '/financas/public/?url=clientes-update';

  document.getElementById('cliente_id').value = c.id || '';
  document.getElementById('cliente_nome').value = c.nome || '';
  document.getElementById('cliente_documento').value = c.documento || '';
  document.getElementById('cliente_email').value = c.email || '';
  document.getElementById('cliente_telefone').value = c.telefone || '';
  document.getElementById('cliente_endereco').value = c.endereco || '';
  document.getElementById('cliente_observacoes').value = c.observacoes || '';
}
</script>
