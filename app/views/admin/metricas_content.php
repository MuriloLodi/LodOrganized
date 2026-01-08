<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="mb-0">Métricas</h1>
    <div class="text-muted">Visão geral do uso</div>
  </div>
  <a class="btn btn-outline-secondary" href="/financas/public/?url=admin">Voltar</a>
</div>

<div class="row g-3">
  <div class="col-md-3">
    <div class="card"><div class="card-body">
      <div class="text-muted small">Usuários</div>
      <div class="fs-3 fw-bold"><?= (int)$metricas['totalUsuarios'] ?></div>
    </div></div>
  </div>

  <div class="col-md-3">
    <div class="card"><div class="card-body">
      <div class="text-muted small">Ativos (7 dias)</div>
      <div class="fs-3 fw-bold"><?= (int)$metricas['ativos7d'] ?></div>
    </div></div>
  </div>

  <div class="col-md-2">
    <div class="card"><div class="card-body">
      <div class="text-muted small">Propostas</div>
      <div class="fs-3 fw-bold"><?= (int)$metricas['totalPropostas'] ?></div>
    </div></div>
  </div>

  <div class="col-md-2">
    <div class="card"><div class="card-body">
      <div class="text-muted small">Clientes</div>
      <div class="fs-3 fw-bold"><?= (int)$metricas['totalClientes'] ?></div>
    </div></div>
  </div>

  <div class="col-md-2">
    <div class="card"><div class="card-body">
      <div class="text-muted small">Serviços</div>
      <div class="fs-3 fw-bold"><?= (int)$metricas['totalServicos'] ?></div>
    </div></div>
  </div>
</div>
