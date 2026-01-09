<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
  <div>
    <h1 class="mb-1">Admin</h1>
    <div class="text-muted">Área exclusiva do dono do aplicativo</div>
  </div>

  <span class="badge bg-dark align-self-start">
    <i class="bi bi-shield-lock me-1"></i> Acesso restrito
  </span>
</div>

<style>
  /* Padrão front (soft) */
  .card-soft{
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 16px;
    box-shadow: 0 10px 26px rgba(0,0,0,.06);
    overflow: hidden;
  }
  .admin-card{
    transition: transform .15s ease, box-shadow .15s ease;
    background: #fff;
  }
  .admin-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 16px 44px rgba(0,0,0,.10);
  }
  .admin-hero{
    background: linear-gradient(180deg, rgba(13,110,253,.08), rgba(255,255,255,0));
    border-bottom: 1px solid rgba(0,0,0,.06);
  }
  .icon-bubble{
    width: 44px; height: 44px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center; justify-content: center;
    background: rgba(13,110,253,.10);
    color: #0d6efd;
  }
  .mini-list{
    margin: 0;
    padding-left: 1rem;
    color: #6c757d;
    font-size: .92rem;
  }
</style>

<div class="card card-soft mb-3 admin-hero">
  <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
      <div class="fw-semibold">Painel do Administrador</div>
      <div class="text-muted small">
        Gerencie usuários e acompanhe métricas gerais do sistema.
      </div>
    </div>
    <div class="text-muted small">
      <i class="bi bi-info-circle me-1"></i> Ações aqui afetam todo o app
    </div>
  </div>
</div>

<div class="row g-3">

  <!-- Usuários -->
  <div class="col-lg-6">
    <div class="card card-soft admin-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-start gap-3 mb-2">
          <div class="icon-bubble">
            <i class="bi bi-people"></i>
          </div>
          <div class="flex-grow-1">
            <h5 class="fw-semibold mb-1">Usuários</h5>
            <div class="text-muted">Listar, bloquear/desbloquear e resetar senha</div>
          </div>
        </div>

        <ul class="mini-list mb-3">
          <li>Controle de acesso e status</li>
          <li>Reset de senha quando necessário</li>
          <li>Visão rápida por usuário</li>
        </ul>

        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-primary" href="/financas/public/?url=admin-usuarios">
            <i class="bi bi-arrow-right-circle me-1"></i> Abrir usuários
          </a>
          <a class="btn btn-outline-secondary" href="/financas/public/?url=admin-usuarios">
            Ver lista
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Métricas -->
  <div class="col-lg-6">
    <div class="card card-soft admin-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-start gap-3 mb-2">
          <div class="icon-bubble" style="background: rgba(25,135,84,.10); color:#198754;">
            <i class="bi bi-graph-up"></i>
          </div>
          <div class="flex-grow-1">
            <h5 class="fw-semibold mb-1">Métricas</h5>
            <div class="text-muted">Visão geral de uso do app</div>
          </div>
        </div>

        <ul class="mini-list mb-3">
          <li>Uso por período</li>
          <li>Indicadores gerais do sistema</li>
          <li>Insights para melhorias</li>
        </ul>

        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-outline-primary" href="/financas/public/?url=admin-metricas">
            <i class="bi bi-speedometer2 me-1"></i> Ver métricas
          </a>
          <a class="btn btn-outline-secondary" href="/financas/public/?url=admin-metricas">
            Abrir painel
          </a>
        </div>
      </div>
    </div>
  </div>

</div>
