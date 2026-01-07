<?php
// Esperado no controller:
// $lancamento, $contas, $categorias
// + anexos:
$anexos = $anexos ?? []; // se você não estiver passando ainda, não quebra
$statusAtual = $lancamento['status'] ?? 'pago';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="mb-0">Editar lançamento</h1>
    <div class="text-muted">Ajuste os dados e gerencie anexos</div>
  </div>

  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-secondary"
       href="/financas/public/?url=lancamentos">
      ← Voltar
    </a>

    <?php if ($statusAtual === 'pago'): ?>
      <span class="badge bg-success align-self-center">pago</span>
    <?php else: ?>
      <span class="badge bg-secondary align-self-center">pendente</span>
    <?php endif; ?>
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
    <div class="card">
      <div class="card-body">
        <h5 class="fw-semibold mb-3">Dados do lançamento</h5>

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

          <div class="d-flex gap-2 mt-3 flex-wrap">
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
  </div>

  <!-- ANEXOS -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="fw-semibold mb-0">Anexos</h5>
          <span class="text-muted small">JPG, PNG ou PDF (até 5MB)</span>
        </div>

        <form method="POST"
              action="/financas/public/?url=anexos-upload"
              enctype="multipart/form-data"
              class="d-flex gap-2 flex-wrap">
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
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div class="me-2" style="min-width:0;">
                  <div class="fw-semibold text-truncate">
                    <?= htmlspecialchars($a['arquivo']) ?>
                  </div>
                  <div class="text-muted small">
                    <?= htmlspecialchars($a['mime'] ?? '') ?>
                    <?php if (!empty($a['tamanho'])): ?>
                      • <?= number_format(((int)$a['tamanho'])/1024, 0, ',', '.') ?> KB
                    <?php endif; ?>
                  </div>

                  <div class="mt-1 d-flex gap-2 flex-wrap">
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
                  <img src="<?= $urlArquivo ?>" alt="anexo" class="rounded" style="width:56px;height:56px;object-fit:cover;">
                <?php else: ?>
                  <span class="badge bg-secondary">PDF</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>
