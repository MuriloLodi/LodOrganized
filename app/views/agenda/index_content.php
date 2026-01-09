<?php
$badge = function($st) {
  switch ($st) {
    case 'confirmado': return 'bg-primary';
    case 'concluido':  return 'bg-success';
    case 'faltou':     return 'bg-warning text-dark';
    case 'cancelado':  return 'bg-secondary';
    default:           return 'bg-info text-dark'; // marcado
  }
};

$labelSt = function($st) {
  switch ($st) {
    case 'confirmado': return 'Confirmado';
    case 'concluido':  return 'Concluído';
    case 'faltou':     return 'Faltou';
    case 'cancelado':  return 'Cancelado';
    default:           return 'Marcado';
  }
};

$diaSemanaNome = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<style>
/* ===== PADRÃO FRONT ===== */
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

.section-title{ font-weight: 850; letter-spacing: -.2px; margin:0; }

.btn{ border-radius: 12px; font-weight: 650; }
.btn:active{ transform: translateY(1px); }

.help{ color:#6c757d; font-size:.875rem; }

.nav-tabs{
  border-bottom: 1px solid rgba(0,0,0,.08);
}
.nav-tabs .nav-link{
  border: 1px solid transparent;
  border-top-left-radius: 14px;
  border-top-right-radius: 14px;
  color: #495057;
  font-weight: 700;
}
.nav-tabs .nav-link:hover{
  border-color: rgba(13,110,253,.20);
  background: rgba(13,110,253,.04);
}
.nav-tabs .nav-link.active{
  border-color: rgba(0,0,0,.08) rgba(0,0,0,.08) #fff;
  background: #fff;
  color: #0d6efd;
}

/* ====== TABLE MOBILE FIX ====== */
.table-responsive-mobile {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.table-responsive-mobile table { min-width: 900px; }

@media (max-width: 576px){
  .btn-stack .btn{ width:100%; }
}
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
  <div>
    <h1 class="page-title mb-1">Agendamentos</h1>
    <div class="page-sub">Crie agendamentos, bloqueie horários e use seu link público</div>
  </div>

  <div class="d-flex gap-2 flex-wrap btn-stack">
    <a class="btn btn-primary" href="/financas/public/?url=agenda-new">
      <i class="bi bi-plus-lg me-1"></i> Novo agendamento
    </a>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger"><?= h($_SESSION['erro']); unset($_SESSION['erro']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['sucesso'])): ?>
  <div class="alert alert-success"><?= h($_SESSION['sucesso']); unset($_SESSION['sucesso']); ?></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabAg" type="button">Agendamentos</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBloq" type="button">Bloqueios</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabLink" type="button">Link público</button>
  </li>
</ul>

<div class="tab-content">

  <!-- AGENDAMENTOS -->
  <div class="tab-pane fade show active" id="tabAg">
    <div class="card-soft micro mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
          <h5 class="section-title mb-0">Filtros</h5>
          <div class="help">Filtre por período, status ou busca</div>
        </div>

        <form class="row g-2 align-items-end" method="GET" action="/financas/public/">
          <input type="hidden" name="url" value="agenda">

          <div class="col-12 col-md-4">
            <label class="form-label">Busca</label>
            <input class="form-control" name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="cliente, título...">
          </div>

          <div class="col-12 col-md-2">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <?php $stSel = $_GET['status'] ?? ''; ?>
              <option value="">Todos</option>
              <?php foreach (['marcado','confirmado','concluido','faltou','cancelado'] as $st): ?>
                <option value="<?= $st ?>" <?= $stSel===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-6 col-md-2">
            <label class="form-label">De</label>
            <input type="date" class="form-control" name="de" value="<?= h($_GET['de'] ?? '') ?>">
          </div>

          <div class="col-6 col-md-2">
            <label class="form-label">Até</label>
            <input type="date" class="form-control" name="ate" value="<?= h($_GET['ate'] ?? '') ?>">
          </div>

          <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-outline-primary w-100">Filtrar</button>
            <a class="btn btn-outline-secondary w-100" href="/financas/public/?url=agenda">Limpar</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card-soft micro">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
          <h5 class="section-title mb-0">Lista</h5>
          <div class="help">Ações rápidas no menu</div>
        </div>

        <?php if (empty($agendamentos)): ?>
          <div class="text-muted">Nenhum agendamento encontrado.</div>
        <?php else: ?>
          <div class="table-responsive-mobile">
            <table class="table align-middle table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th style="min-width:170px;">Quando</th>
                  <th style="min-width:220px;">Cliente</th>
                  <th style="min-width:260px;">Título</th>
                  <th style="min-width:120px;">Status</th>
                  <th class="text-end" style="min-width:160px;">Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($agendamentos as $a): ?>
                  <?php
                    $ini = strtotime($a['data_inicio']);
                    $fim = strtotime($a['data_fim']);
                    $quando = date('d/m/Y', $ini) . " • " . date('H:i', $ini) . " - " . date('H:i', $fim);
                  ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= h($quando) ?></div>
                      <div class="text-muted small"><?= h($diaSemanaNome[(int)date('w',$ini)]) ?></div>
                    </td>

                    <td>
                      <div class="fw-semibold"><?= h(($a['cliente_nome'] ?? '') ?: '-') ?></div>
                      <div class="text-muted small"><?= h(($a['cliente_email'] ?? '') ?: '') ?></div>
                      <div class="text-muted small"><?= h(($a['cliente_telefone'] ?? '') ?: '') ?></div>
                    </td>

                    <td>
                      <div class="fw-semibold"><?= h($a['titulo'] ?? '') ?></div>
                      <?php if (!empty($a['descricao'])): ?>
                        <div class="text-muted small"><?= nl2br(h($a['descricao'])) ?></div>
                      <?php endif; ?>
                    </td>

                    <td>
                      <span class="badge <?= $badge($a['status'] ?? 'marcado') ?>">
                        <?= h($labelSt($a['status'] ?? 'marcado')) ?>
                      </span>
                    </td>

                    <td class="text-end">
                      <div class="btn-group">
                        <a class="btn btn-sm btn-outline-secondary"
                           href="/financas/public/?url=agenda-edit&id=<?= (int)$a['id'] ?>">
                          <i class="bi bi-pencil"></i>
                        </a>

                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" type="button"></button>

                        <ul class="dropdown-menu dropdown-menu-end">
                          <li><a class="dropdown-item" href="/financas/public/?url=agenda-status&id=<?= (int)$a['id'] ?>&st=confirmado">Marcar como Confirmado</a></li>
                          <li><a class="dropdown-item" href="/financas/public/?url=agenda-status&id=<?= (int)$a['id'] ?>&st=concluido">Marcar como Concluído</a></li>
                          <li><a class="dropdown-item" href="/financas/public/?url=agenda-status&id=<?= (int)$a['id'] ?>&st=faltou">Marcar como Faltou</a></li>
                          <li><a class="dropdown-item" href="/financas/public/?url=agenda-status&id=<?= (int)$a['id'] ?>&st=cancelado">Marcar como Cancelado</a></li>
                          <li><hr class="dropdown-divider"></li>
                          <li>
                            <a class="dropdown-item text-danger"
                               href="/financas/public/?url=agenda-delete&id=<?= (int)$a['id'] ?>"
                               onclick="return confirm('Excluir agendamento?')">
                               Excluir
                            </a>
                          </li>
                        </ul>
                      </div>
                    </td>

                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- BLOQUEIOS -->
  <div class="tab-pane fade" id="tabBloq">
    <div class="row g-3">

      <div class="col-lg-6">
        <div class="card-soft micro h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
              <h5 class="section-title mb-0">Bloqueio por período</h5>
              <div class="help">Impede agendamento no intervalo</div>
            </div>

            <form method="POST" action="/financas/public/?url=agenda-bloqueio-store">
              <input type="hidden" name="tipo" value="periodo">

              <div class="mb-2">
                <label class="form-label">Título</label>
                <input class="form-control" name="titulo" placeholder="Ex: Feriado / Viagem" required>
              </div>

              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label">Início</label>
                  <input type="datetime-local" class="form-control" name="data_inicio" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Fim</label>
                  <input type="datetime-local" class="form-control" name="data_fim" required>
                </div>
              </div>

              <button class="btn btn-outline-primary w-100 mt-3">Salvar bloqueio</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card-soft micro h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
              <h5 class="section-title mb-0">Bloqueio semanal</h5>
              <div class="help">Repete toda semana</div>
            </div>

            <form method="POST" action="/financas/public/?url=agenda-bloqueio-store">
              <input type="hidden" name="tipo" value="semanal">

              <div class="mb-2">
                <label class="form-label">Título</label>
                <input class="form-control" name="titulo" placeholder="Ex: Almoço / Pausa" required>
              </div>

              <div class="row g-2">
                <div class="col-md-4">
                  <label class="form-label">Dia</label>
                  <select class="form-select" name="dia_semana" required>
                    <option value="1">Segunda</option>
                    <option value="2">Terça</option>
                    <option value="3">Quarta</option>
                    <option value="4">Quinta</option>
                    <option value="5">Sexta</option>
                    <option value="6">Sábado</option>
                    <option value="0">Domingo</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Hora início</label>
                  <input type="time" class="form-control" name="hora_inicio" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Hora fim</label>
                  <input type="time" class="form-control" name="hora_fim" required>
                </div>
              </div>

              <button class="btn btn-outline-primary w-100 mt-3">Salvar bloqueio semanal</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card-soft micro">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
              <h5 class="section-title mb-0">Seus bloqueios</h5>
              <div class="help">Remova quando não precisar mais</div>
            </div>

            <?php if (empty($bloqueios)): ?>
              <div class="text-muted">Nenhum bloqueio cadastrado.</div>
            <?php else: ?>
              <div class="table-responsive-mobile">
                <table class="table align-middle table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Título</th>
                      <th style="width:120px;">Tipo</th>
                      <th>Período / Semana</th>
                      <th class="text-end" style="width:90px;">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($bloqueios as $b): ?>
                      <tr>
                        <td class="fw-semibold"><?= h($b['titulo'] ?? '') ?></td>
                        <td><?= ($b['tipo'] ?? '') === 'semanal' ? 'Semanal' : 'Período' ?></td>
                        <td class="text-muted">
                          <?php if (($b['tipo'] ?? '') === 'semanal'): ?>
                            <?= h($diaSemanaNome[(int)($b['dia_semana'] ?? 0)]) ?> • <?= h(substr($b['hora_inicio'] ?? '00:00',0,5)) ?> - <?= h(substr($b['hora_fim'] ?? '00:00',0,5)) ?>
                          <?php else: ?>
                            <?= h(date('d/m/Y H:i', strtotime($b['data_inicio'] ?? ''))) ?> → <?= h(date('d/m/Y H:i', strtotime($b['data_fim'] ?? ''))) ?>
                          <?php endif; ?>
                        </td>
                        <td class="text-end">
                          <a class="btn btn-sm btn-outline-danger"
                             href="/financas/public/?url=agenda-bloqueio-delete&id=<?= (int)$b['id'] ?>"
                             onclick="return confirm('Remover bloqueio?')">
                            <i class="bi bi-trash"></i>
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
      </div>

    </div>
  </div>

  <!-- LINK PÚBLICO -->
  <div class="tab-pane fade" id="tabLink">
    <div class="card-soft micro">
      <div class="card-body">
        <h5 class="section-title mb-1">Seu link público</h5>
        <div class="text-muted mb-3">Compartilhe com seus clientes para eles agendarem sozinhos.</div>

        <div class="input-group">
          <input class="form-control" id="publicLink" value="<?= h($publicLink ?? '') ?>" readonly>
          <button class="btn btn-outline-primary" type="button" id="btnCopyLink">
            <i class="bi bi-copy"></i> Copiar
          </button>
          <a class="btn btn-outline-secondary" target="_blank" href="<?= h($publicLink ?? '') ?>">
            <i class="bi bi-box-arrow-up-right"></i> Abrir
          </a>
        </div>

        <div class="help mt-3">
          Os horários exibidos no link público seguem as configurações do seu perfil.
          Bloqueios e agendamentos ocupados são respeitados.
        </div>
      </div>
    </div>
  </div>

</div>

<script>
(function(){
  const btn = document.getElementById('btnCopyLink');
  const inp = document.getElementById('publicLink');

  btn?.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(inp.value || '');
      btn.innerHTML = '<i class="bi bi-check2"></i> Copiado';
      setTimeout(()=> btn.innerHTML = '<i class="bi bi-copy"></i> Copiar', 1500);
    } catch(e) {
      inp?.select();
      try { document.execCommand('copy'); } catch(_) {}
    }
  });
})();
</script>
