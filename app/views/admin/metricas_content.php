<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
  <div>
    <h1 class="mb-1">Métricas</h1>
    <div class="text-muted">Visão geral do uso</div>
  </div>

  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-secondary" href="/financas/public/?url=admin">
      <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
  </div>
</div>

<style>
  .card-soft{
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 16px;
    box-shadow: 0 10px 26px rgba(0,0,0,.06);
    overflow: hidden;
    background: #fff;
  }
  .kpi{
    transition: transform .15s ease, box-shadow .15s ease;
  }
  .kpi:hover{
    transform: translateY(-2px);
    box-shadow: 0 16px 44px rgba(0,0,0,.10);
  }
  .kpi .label{
    color:#6c757d;
    font-size:.85rem;
  }
  .kpi .value{
    font-size:1.6rem;
    font-weight:800;
    line-height:1.1;
  }
  .kpi .hint{
    color:#6c757d;
    font-size:.82rem;
  }
  .kpi-hero{
    background: linear-gradient(180deg, rgba(13,110,253,.08), rgba(255,255,255,0));
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 16px;
  }
  .chip{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    padding:.35rem .6rem;
    border-radius: 999px;
    font-size: .82rem;
    border: 1px solid rgba(0,0,0,.08);
    background:#fff;
    color:#495057;
  }
</style>

<!-- RESUMO (front) -->
<div class="kpi-hero p-3 mb-3 d-flex justify-content-between align-items-start flex-wrap gap-2">
  <div>
    <div class="fw-semibold">Painel de métricas</div>
    <div class="text-muted small">Números gerais para acompanhamento rápido</div>
  </div>

  <div class="d-flex gap-2 flex-wrap">
    <span class="chip">
      <i class="bi bi-shield-lock"></i> Admin
    </span>
    <span class="chip">
      <i class="bi bi-activity"></i> Atualizado na hora
    </span>
  </div>
</div>

<div class="row g-3">

  <div class="col-12 col-md-6 col-xl-3">
    <div class="card-soft kpi p-3 h-100">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="label mb-1">Usuários</div>
          <div class="value"><?= (int)($metricas['totalUsuarios'] ?? 0) ?></div>
          <div class="hint">Total cadastrados</div>
        </div>
        <div class="text-primary fs-4">
          <i class="bi bi-people"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-3">
    <div class="card-soft kpi p-3 h-100">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="label mb-1">Ativos (7 dias)</div>
          <div class="value"><?= (int)($metricas['ativos7d'] ?? 0) ?></div>
          <div class="hint">Usuários com atividade recente</div>
        </div>
        <div class="text-success fs-4">
          <i class="bi bi-activity"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-2">
    <div class="card-soft kpi p-3 h-100">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="label mb-1">Propostas</div>
          <div class="value"><?= (int)($metricas['totalPropostas'] ?? 0) ?></div>
          <div class="hint">Criadas no sistema</div>
        </div>
        <div class="text-dark fs-4">
          <i class="bi bi-file-earmark-text"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-2">
    <div class="card-soft kpi p-3 h-100">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="label mb-1">Clientes</div>
          <div class="value"><?= (int)($metricas['totalClientes'] ?? 0) ?></div>
          <div class="hint">Cadastrados</div>
        </div>
        <div class="text-primary fs-4">
          <i class="bi bi-person-badge"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-2">
    <div class="card-soft kpi p-3 h-100">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="label mb-1">Serviços</div>
          <div class="value"><?= (int)($metricas['totalServicos'] ?? 0) ?></div>
          <div class="hint">Ativos e inativos</div>
        </div>
        <div class="text-success fs-4">
          <i class="bi bi-briefcase"></i>
        </div>
      </div>
    </div>
  </div>

</div>

<div class="text-muted small mt-3">
  Observação: estes números são apenas visão geral (sem filtros avançados).
</div>
