<?php
$isEdit = !empty($ag['id']);
$action = $isEdit ? '/financas/public/?url=agenda-update' : '/financas/public/?url=agenda-store';

function dbToLocal($s) {
  $s = trim((string)$s);
  if ($s === '') return '';
  return substr(str_replace(' ', 'T', $s), 0, 16);
}
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h1 class="mb-0"><?= $isEdit ? 'Editar agendamento' : 'Novo agendamento' ?></h1>
    <div class="text-muted">Defina horário, cliente, status e notificação</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="/financas/public/?url=agenda">Voltar</a>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>">
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int)$ag['id'] ?>">
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-body">
          <div class="mb-2">
            <label class="form-label">Título</label>
            <input class="form-control" name="titulo" required value="<?= htmlspecialchars($ag['titulo'] ?? '') ?>">
          </div>

          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Início</label>
              <input type="datetime-local" class="form-control" name="data_inicio" required
                     value="<?= htmlspecialchars(dbToLocal($ag['data_inicio'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Fim</label>
              <input type="datetime-local" class="form-control" name="data_fim" required
                     value="<?= htmlspecialchars(dbToLocal($ag['data_fim'] ?? '')) ?>">
            </div>
          </div>

          <div class="row g-2 mt-1">
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <?php $st = $ag['status'] ?? 'marcado'; ?>
              <select class="form-select" name="status">
                <?php foreach (['marcado','confirmado','concluido','faltou','cancelado'] as $s): ?>
                  <option value="<?= $s ?>" <?= $st===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Notificar antes (min)</label>
              <input type="number" class="form-control" name="notificar_minutos" min="0"
                     value="<?= (int)($ag['notificar_minutos'] ?? 60) ?>">
            </div>
          </div>

          <div class="mt-2">
            <label class="form-label">Descrição / Observações</label>
            <textarea class="form-control" rows="4" name="descricao"><?= htmlspecialchars($ag['descricao'] ?? '') ?></textarea>
          </div>

          <div class="mt-2">
            <label class="form-label">Local</label>
            <input class="form-control" name="local" value="<?= htmlspecialchars($ag['local'] ?? '') ?>" placeholder="Ex: Online / Endereço">
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="fw-semibold mb-3">Cliente</h5>

          <div class="mb-2">
            <label class="form-label">Nome</label>
            <input class="form-control" name="cliente_nome" value="<?= htmlspecialchars($ag['cliente_nome'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">E-mail</label>
            <input class="form-control" name="cliente_email" value="<?= htmlspecialchars($ag['cliente_email'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Telefone</label>
            <input class="form-control" name="cliente_telefone" value="<?= htmlspecialchars($ag['cliente_telefone'] ?? '') ?>">
          </div>

          <div class="d-flex gap-2 mt-3">
            <button class="btn btn-primary">
              <i class="bi bi-check2-circle"></i> Salvar
            </button>
            <?php if ($isEdit): ?>
              <a class="btn btn-outline-danger"
                 href="/financas/public/?url=agenda-delete&id=<?= (int)$ag['id'] ?>"
                 onclick="return confirm('Excluir agendamento?')">
                 <i class="bi bi-trash"></i> Excluir
              </a>
            <?php endif; ?>
          </div>

          <div class="text-muted small mt-3">
            Dica: o bloqueio de horários impede criação e também impede o público de agendar no período.
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
