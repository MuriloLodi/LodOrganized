<?php
// Esperado no controller:
// $lancamento, $contas, $categorias
// + anexos:
$anexos = $anexos ?? []; // se você não estiver passando ainda, não quebra
$statusAtual = $lancamento['status'] ?? 'pago';

function _badgeStatus($s){
  if ($s === 'pago') return '<span class="badge bg-success-subtle text-success border align-self-center">pago</span>';
  return '<span class="badge bg-secondary-subtle text-secondary border align-self-center">pendente</span>';
}
?>

<style>
/* ===== PADRÃO APP (igual dashboards) ===== */
body{ background:#f8fafc; }

.page-title{ font-weight:900; letter-spacing:-.5px; }
.section-title{ font-weight:900; letter-spacing:-.4px; }

.card-soft{
  background:#fff;
  border-radius:18px;
  border:1px solid rgba(0,0,0,.05);
  box-shadow:0 10px 28px rgba(0,0,0,.06);
}

.form-label{
  font-size:.72rem;
  text-transform:uppercase;
  letter-spacing:.08em;
  color:#6c757d;
}

.btn{ border-radius:12px; font-weight:650; }
.btn:active{ transform: translateY(1px); }

.micro{
  transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
}
.micro:hover{
  transform: translateY(-1px);
  box-shadow:0 14px 36px rgba(0,0,0,.10);
  border-color: rgba(13,110,253,.25);
}

.kpi-pill{
  border-radius:999px;
  border:1px solid rgba(0,0,0,.08);
  padding:.35rem .6rem;
  background:#fff;
}

.filebox input[type="file"]{
  cursor:pointer;
}

.list-group-item{
  border-color: rgba(0,0,0,.06);
}

.thumb{
  width:56px;height:56px;object-fit:cover;border-radius:12px;
  border:1px solid rgba(0,0,0,.08);
}

@media (max-width: 576px){
  .btn{ width:100%; }
  .actions-row .btn{ width:100%; }
}
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="page-title mb-1">Editar lançamento</h1>
    <div class="text-muted">Ajuste os dados e gerencie anexos</div>
  </div>

  <div class="d-flex gap-2 flex-wrap align-items-center">
    <a class="btn btn-outline-secondary" href="/financas/public/?url=lancamentos">← Voltar</a>
    <?= _badgeStatus($statusAtual) ?>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<div class="row g-3">

  <!-- FORM PRINCIPAL -->
  <div class="col-lg-7">
    <div class="card-soft p-4 micro">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <div class="section-title">Dados do lançamento</div>
          <div class="text-muted small">Edite com cuidado — status pendente não mexe no saldo</div>
        </div>

        <span class="kpi-pill text-muted small">
          ID: <b class="text-dark"><?= (int)$lancamento['id'] ?></b>
        </span>
      </div>

      <form method="POST" action="/financas/public/?url=lancamentos-update">
        <input type="hidden" name="id" value="<?= (int)$lancamento['id'] ?>">

        <div class="row g-2">

          <div class="col-md-4">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select" required>
              <option value="R" <?= $lancamento['tipo'] == 'R' ? 'selected' : '' ?>>Receita</option>
              <option value="D" <?= $lancamento['tipo'] == 'D' ? 'selected' : '' ?>>Despesa</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Conta</label>
            <select name="id_conta" class="form-select" required>
              <?php foreach ($contas as $c): ?>
                <option value="<?= (int)$c['id'] ?>"
                  <?= (int)$c['id'] == (int)$lancamento['id_conta'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Categoria</label>
            <select name="id_categoria" class="form-select">
              <option value="">(Sem categoria)</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"
                  <?= (int)$cat['id'] == (int)$lancamento['id_categoria'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Valor</label>
            <input type="text"
                   name="valor"
                   class="form-control money-br"
                   inputmode="numeric"
                   placeholder="0,00"
                   value="<?= number_format((float)$lancamento['valor'], 2, ',', '.') ?>"
                   required>
            <div class="form-text">Use vírgula para centavos.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Data</label>
            <input type="date" name="data" value="<?= htmlspecialchars($lancamento['data']) ?>" class="form-control" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="pago" <?= $statusAtual === 'pago' ? 'selected' : '' ?>>Pago</option>
              <option value="pendente" <?= $statusAtual === 'pendente' ? 'selected' : '' ?>>Pendente</option>
            </select>
            <div class="form-text">Pendente não mexe no saldo.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Descrição</label>
            <input type="text"
                   name="descricao"
                   value="<?= htmlspecialchars($lancamento['descricao'] ?? '') ?>"
                   class="form-control"
                   placeholder="Ex: Mercado, Salário, Gasolina...">
          </div>
        </div>

        <div class="actions-row d-flex gap-2 mt-3 flex-wrap">
          <button class="btn btn-primary">Salvar alterações</button>

          <a class="btn btn-outline-secondary"
             href="/financas/public/?url=lancamentos-toggle-status&id=<?= (int)$lancamento['id'] ?>">
            <?= $statusAtual === 'pago' ? 'Marcar como pendente' : 'Marcar como pago' ?>
          </a>

          <a class="btn btn-outline-danger"
             href="/financas/public/?url=lancamentos-delete&id=<?= (int)$lancamento['id'] ?>"
             onclick="return confirm('Excluir este lançamento?')">
            Excluir
          </a>
        </div>

      </form>
    </div>
  </div>

  <!-- ANEXOS -->
  <div class="col-lg-5">
    <div class="card-soft p-4 micro">
      <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
        <div>
          <div class="section-title">Anexos</div>
          <div class="text-muted small">JPG, PNG ou PDF (até 5MB)</div>
        </div>
        <span class="kpi-pill text-muted small">
          total: <b class="text-dark"><?= count($anexos) ?></b>
        </span>
      </div>

      <form method="POST"
            action="/financas/public/?url=anexos-upload"
            enctype="multipart/form-data"
            class="d-flex gap-2 flex-wrap filebox">
        <input type="hidden" name="id_lancamento" value="<?= (int)$lancamento['id'] ?>">

        <input type="file"
               name="arquivo"
               class="form-control"
               accept="image/png,image/jpeg,application/pdf"
               required>

        <button class="btn btn-outline-primary">Enviar</button>
      </form>

      <hr class="my-3">

      <?php if (empty($anexos)): ?>
        <div class="text-muted">Nenhum anexo ainda.</div>
      <?php else: ?>
        <div class="list-group list-group-flush">
          <?php foreach ($anexos as $a): ?>
            <?php
              $isImg = strpos($a['mime'] ?? '', 'image/') === 0;
              $urlArquivo = "/financas/public/uploads/" . (int)$_SESSION['usuario']['id'] . "/" . rawurlencode($a['arquivo']);
            ?>
            <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
              <div class="me-2" style="min-width:0; flex:1;">
                <div class="fw-semibold text-truncate">
                  <?= htmlspecialchars($a['arquivo']) ?>
                </div>

                <div class="text-muted small">
                  <?= htmlspecialchars($a['mime'] ?? '') ?>
                  <?php if (!empty($a['tamanho'])): ?>
                    • <?= number_format(((int)$a['tamanho'])/1024, 0, ',', '.') ?> KB
                  <?php endif; ?>
                </div>

                <div class="mt-2 d-flex gap-2 flex-wrap">
                  <a class="btn btn-sm btn-outline-secondary"
                     href="<?= $urlArquivo ?>"
                     target="_blank">
                    Abrir
                  </a>

                  <a class="btn btn-sm btn-outline-danger"
                     href="/financas/public/?url=anexos-delete&id=<?= (int)$a['id'] ?>&l=<?= (int)$lancamento['id'] ?>"
                     onclick="return confirm('Excluir este anexo?')">
                    Excluir
                  </a>
                </div>
              </div>

              <?php if ($isImg): ?>
                <img src="<?= $urlArquivo ?>" alt="anexo" class="thumb">
              <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary border">PDF</span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</div>
