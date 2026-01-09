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
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h1 class="mb-0">Agendamentos</h1>
    <div class="text-muted">Crie agendamentos, bloqueie horários e use seu link público</div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-primary" href="/financas/public/?url=agenda-new">
      <i class="bi bi-plus-lg"></i> Novo agendamento
    </a>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['sucesso'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_SESSION['sucesso']); unset($_SESSION['sucesso']); ?></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabAg">Agendamentos</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBloq">Bloqueios</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabLink">Link público</button>
  </li>
</ul>

<div class="tab-content">
  <!-- AGENDAMENTOS -->
  <div class="tab-pane fade show active" id="tabAg">
    <div class="card mb-3">
      <div class="card-body">
        <form class="row g-2" method="GET" action="/financas/public/">
          <input type="hidden" name="url" value="agenda">
          <div class="col-md-3">
            <label class="form-label">Busca</label>
            <input class="form-control" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="cliente, título...">
          </div>
          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <?php $stSel = $_GET['status'] ?? ''; ?>
              <option value="">Todos</option>
              <?php foreach (['marcado','confirmado','concluido','faltou','cancelado'] as $st): ?>
                <option value="<?= $st ?>" <?= $stSel===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">De</label>
            <input type="date" class="form-control" name="de" value="<?= htmlspecialchars($_GET['de'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Até</label>
            <input type="date" class="form-control" name="ate" value="<?= htmlspecialchars($_GET['ate'] ?? '') ?>">
          </div>
          <div class="col-md-3 d-flex align-items-end gap-2">
            <button class="btn btn-outline-primary w-100">Filtrar</button>
            <a class="btn btn-outline-secondary" href="/financas/public/?url=agenda">Limpar</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <?php if (empty($agendamentos)): ?>
          <div class="text-muted">Nenhum agendamento encontrado.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Quando</th>
                  <th>Cliente</th>
                  <th>Título</th>
                  <th>Status</th>
                  <th class="text-end">Ações</th>
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
                      <div class="fw-semibold"><?= $quando ?></div>
                      <div class="text-muted small"><?= $diaSemanaNome[(int)date('w',$ini)] ?></div>
                    </td>
                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($a['cliente_nome'] ?: '-') ?></div>
                      <div class="text-muted small"><?= htmlspecialchars($a['cliente_email'] ?: '') ?></div>
                      <div class="text-muted small"><?= htmlspecialchars($a['cliente_telefone'] ?: '') ?></div>
                    </td>
                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($a['titulo']) ?></div>
                      <?php if (!empty($a['descricao'])): ?>
                        <div class="text-muted small"><?= nl2br(htmlspecialchars($a['descricao'])) ?></div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge <?= $badge($a['status']) ?>"><?= $labelSt($a['status']) ?></span>
                    </td>
                    <td class="text-end">
                      <div class="btn-group">
                        <a class="btn btn-sm btn-outline-secondary" href="/financas/public/?url=agenda-edit&id=<?= (int)$a['id'] ?>">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"></button>
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
        <div class="card">
          <div class="card-body">
            <h5 class="fw-semibold mb-3">Criar bloqueio por período</h5>
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
        <div class="card">
          <div class="card-body">
            <h5 class="fw-semibold mb-3">Criar bloqueio semanal</h5>
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
        <div class="card">
          <div class="card-body">
            <h5 class="fw-semibold mb-3">Seus bloqueios</h5>

            <?php if (empty($bloqueios)): ?>
              <div class="text-muted">Nenhum bloqueio cadastrado.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Título</th>
                      <th>Tipo</th>
                      <th>Período / Semana</th>
                      <th class="text-end">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($bloqueios as $b): ?>
                      <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($b['titulo']) ?></td>
                        <td><?= $b['tipo'] === 'semanal' ? 'Semanal' : 'Período' ?></td>
                        <td class="text-muted">
                          <?php if ($b['tipo'] === 'semanal'): ?>
                            <?= $diaSemanaNome[(int)$b['dia_semana']] ?> • <?= substr($b['hora_inicio'],0,5) ?> - <?= substr($b['hora_fim'],0,5) ?>
                          <?php else: ?>
                            <?= date('d/m/Y H:i', strtotime($b['data_inicio'])) ?> → <?= date('d/m/Y H:i', strtotime($b['data_fim'])) ?>
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
    <div class="card">
      <div class="card-body">
        <h5 class="fw-semibold mb-2">Seu link público</h5>
        <div class="text-muted mb-3">Compartilhe com seus clientes para eles agendarem sozinhos.</div>

        <div class="input-group">
          <input class="form-control" id="publicLink" value="<?= htmlspecialchars($publicLink ?? '') ?>" readonly>
          <button class="btn btn-outline-primary" type="button" id="btnCopyLink">
            <i class="bi bi-copy"></i> Copiar
          </button>
          <a class="btn btn-outline-secondary" target="_blank" href="<?= htmlspecialchars($publicLink ?? '') ?>">
            <i class="bi bi-box-arrow-up-right"></i> Abrir
          </a>
        </div>

        <div class="text-muted small mt-3">
          Horários padrão do público: Seg-Sex 09:00-18:00, Sáb 09:00-12:00, Dom fechado.  
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
      await navigator.clipboard.writeText(inp.value);
      btn.innerHTML = '<i class="bi bi-check2"></i> Copiado';
      setTimeout(()=> btn.innerHTML = '<i class="bi bi-copy"></i> Copiar', 1500);
    } catch(e) {
      inp.select();
      document.execCommand('copy');
    }
  });
})();
</script>
