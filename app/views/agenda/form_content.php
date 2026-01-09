<?php
$isEdit = !empty($ag['id']);
$action = $isEdit ? '/financas/public/?url=agenda-update' : '/financas/public/?url=agenda-store';

function dbToLocal($s) {
  $s = trim((string)$s);
  if ($s === '') return '';
  return substr(str_replace(' ', 'T', $s), 0, 16);
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<style>
/* ===== PADRÃO FRONT (igual o que você pediu) ===== */
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

.help{
  color:#6c757d;
  font-size: .875rem;
}

.modal-content{
  border-radius: 18px;
  border: 1px solid rgba(0,0,0,.08);
  box-shadow: 0 18px 44px rgba(0,0,0,.18);
}

@media (max-width: 576px){
  .btn-stack .btn{ width: 100%; }
}
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
  <div>
    <h1 class="page-title"><?= $isEdit ? 'Editar agendamento' : 'Novo agendamento' ?></h1>
    <div class="page-sub">Defina horário, cliente, status e notificação</div>
  </div>

  <div class="d-flex gap-2 flex-wrap btn-stack">
    <a class="btn btn-outline-secondary" href="/financas/public/?url=agenda">Voltar</a>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= h($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>">
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int)$ag['id'] ?>">
  <?php endif; ?>

  <div class="row g-3">
    <!-- DADOS -->
    <div class="col-lg-7">
      <div class="card-soft micro">
        <div class="card-body">

          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <h5 class="section-title mb-0">Dados do agendamento</h5>
            <span class="text-muted small"><?= $isEdit ? 'Editando' : 'Novo' ?></span>
          </div>

          <div class="row g-2">
            <div class="col-12">
              <label class="form-label">Título *</label>
              <input class="form-control" name="titulo" required value="<?= h($ag['titulo'] ?? '') ?>" placeholder="Ex: Reunião, Consulta, Visita...">
            </div>

            <div class="col-md-6">
              <label class="form-label">Início *</label>
              <input type="datetime-local" class="form-control" name="data_inicio" required
                     value="<?= h(dbToLocal($ag['data_inicio'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Fim *</label>
              <input type="datetime-local" class="form-control" name="data_fim" required
                     value="<?= h(dbToLocal($ag['data_fim'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Status</label>
              <?php $st = $ag['status'] ?? 'marcado'; ?>
              <select class="form-select" name="status">
                <?php foreach (['marcado','confirmado','concluido','faltou','cancelado'] as $s): ?>
                  <option value="<?= $s ?>" <?= $st===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="help mt-1">Use “cancelado” ou “faltou” para histórico.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Notificar antes (min)</label>
              <input type="number" class="form-control" name="notificar_minutos" min="0"
                     value="<?= (int)($ag['notificar_minutos'] ?? 60) ?>">
              <div class="help mt-1">0 desativa notificação.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Local</label>
              <input class="form-control" name="local" value="<?= h($ag['local'] ?? '') ?>" placeholder="Ex: Online / Endereço">
            </div>

            <div class="col-12">
              <label class="form-label">Descrição / Observações</label>
              <textarea class="form-control" rows="4" name="descricao" placeholder="Detalhes do atendimento, links, lembretes..."><?= h($ag['descricao'] ?? '') ?></textarea>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- CLIENTE + AÇÕES -->
    <div class="col-lg-5">
      <div class="card-soft micro">
        <div class="card-body">

          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <h5 class="section-title mb-0">Cliente</h5>
            <span class="text-muted small">Opcional</span>
          </div>

          <div class="row g-2">
            <div class="col-12">
              <label class="form-label">Nome</label>
              <input class="form-control" name="cliente_nome" value="<?= h($ag['cliente_nome'] ?? '') ?>" placeholder="Nome do cliente">
            </div>

            <div class="col-12">
              <label class="form-label">E-mail</label>
              <input class="form-control" name="cliente_email" value="<?= h($ag['cliente_email'] ?? '') ?>" placeholder="email@exemplo.com">
            </div>

            <div class="col-12">
              <label class="form-label">Telefone</label>
              <input class="form-control" name="cliente_telefone" value="<?= h($ag['cliente_telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
            </div>
          </div>

          <div class="d-flex gap-2 mt-3 flex-wrap btn-stack">
            <button class="btn btn-primary">
              Salvar
            </button>

            <?php if ($isEdit): ?>
              <a class="btn btn-outline-danger"
                 href="/financas/public/?url=agenda-delete&id=<?= (int)$ag['id'] ?>"
                 onclick="return confirm('Excluir agendamento?')">
                 Excluir
              </a>
            <?php endif; ?>
          </div>

          <div class="help mt-3">
            Dica: bloqueios e agendamentos ocupados são respeitados no público.
          </div>

        </div>
      </div>
    </div>
  </div>
</form>
