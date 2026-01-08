<?php
$q = trim($_GET['q'] ?? '');
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h1 class="mb-0">Clientes</h1>
    <div class="text-muted">Cadastre e gerencie seus clientes</div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente"
            onclick="openNovoCliente()">
      <i class="bi bi-plus-lg me-1"></i> Novo cliente
    </button>
  </div>
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
      <input type="hidden" name="url" value="clientes">
      <div class="col-md-6">
        <label class="form-label">Buscar</label>
        <input class="form-control" name="q" value="<?= htmlspecialchars($q) ?>"
               placeholder="Nome, e-mail, telefone ou documento...">
      </div>
      <div class="col-md-auto">
        <button class="btn btn-outline-secondary">
          <i class="bi bi-search me-1"></i> Filtrar
        </button>
        <?php if ($q !== ''): ?>
          <a class="btn btn-link" href="/financas/public/?url=clientes">Limpar</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <?php if (empty($clientes)): ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-people fs-1 d-block mb-2"></i>
        Nenhum cliente encontrado.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Cliente</th>
              <th class="d-none d-md-table-cell">Contato</th>
              <th class="d-none d-lg-table-cell">Documento</th>
              <th class="d-none d-lg-table-cell">Criado</th>
              <th class="text-end" style="width:160px;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clientes as $c): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= htmlspecialchars($c['nome']) ?></div>
                  <div class="text-muted small"><?= htmlspecialchars($c['endereco'] ?? '') ?></div>
                </td>

                <td class="d-none d-md-table-cell">
                  <div><?= htmlspecialchars($c['email'] ?? '') ?></div>
                  <div class="text-muted small"><?= htmlspecialchars($c['telefone'] ?? '') ?></div>
                </td>

                <td class="d-none d-lg-table-cell">
                  <?= htmlspecialchars($c['documento'] ?? '') ?>
                </td>

                <td class="d-none d-lg-table-cell text-muted small">
                  <?= !empty($c['criado_em']) ? date('d/m/Y', strtotime($c['criado_em'])) : '' ?>
                </td>

                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary"
                          data-bs-toggle="modal" data-bs-target="#modalCliente"
                          onclick='openEditarCliente(<?= json_encode($c, JSON_UNESCAPED_UNICODE) ?>)'>
                    <i class="bi bi-pencil"></i>
                  </button>

                  <form method="POST" action="/financas/public/?url=clientes-delete" class="d-inline"
                        onsubmit="return confirm('Remover este cliente?')">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash"></i>
                    </button>
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
        <h5 class="modal-title" id="modalClienteTitle">Novo cliente</h5>
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
