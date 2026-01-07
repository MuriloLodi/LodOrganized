<?php
$totalReceitas = 0;
$totalDespesas = 0;

foreach ($lancamentos as $l) {
    // só conta pagos no resumo (pra não bagunçar saldo/real)
    if (($l['status'] ?? 'pago') !== 'pago') continue;
    if ($l['tipo'] === 'R') $totalReceitas += (float)$l['valor'];
    else $totalDespesas += (float)$l['valor'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="mb-0">Lançamentos</h1>
    <div class="text-muted">Recorrentes, parcelas, transferências e anexos</div>
  </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<ul class="nav nav-pills mb-3 gap-2" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-lanc">+ Lançamento</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-transf">↔ Transferência</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-parc">💳 Parcelas</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rec">🔁 Recorrente</button>
  </li>
</ul>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="tab-content">

          <!-- ===================== NOVO LANÇAMENTO ===================== -->
          <div class="tab-pane fade show active" id="tab-lanc">
            <h5 class="fw-semibold mb-3">Novo lançamento</h5>

            <form method="POST" action="/financas/public/?url=lancamentos-store">
              <div class="row g-2">

                <div class="col-6">
                  <label class="form-label">Tipo</label>
                  <select name="tipo" class="form-select" required>
                    <option value="">Selecione</option>
                    <option value="R">Receita</option>
                    <option value="D">Despesa</option>
                  </select>
                </div>

                <div class="col-6">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select">
                    <option value="pago">Pago</option>
                    <option value="pendente">Pendente</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Data</label>
                  <input type="date" name="data" class="form-control" required>
                </div>

                <div class="col-12">
                  <label class="form-label">Conta</label>
                  <select name="id_conta" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $c): ?>
                      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Categoria</label>
                  <select name="id_categoria" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($categorias as $cat): ?>
                      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Valor</label>
                  <input name="valor" class="form-control input-money" placeholder="0,00" required>
                </div>

                <div class="col-12">
                  <label class="form-label">Descrição</label>
                  <input type="text" name="descricao" class="form-control">
                </div>
              </div>

              <button class="btn btn-primary w-100 mt-3">Salvar</button>
            </form>
          </div>

          <!-- ===================== TRANSFERÊNCIA ===================== -->
          <div class="tab-pane fade" id="tab-transf">
            <h5 class="fw-semibold mb-3">Transferência entre contas</h5>

            <form method="POST" action="/financas/public/?url=lancamentos-transferencia-store">
              <div class="row g-2">

                <div class="col-6">
                  <label class="form-label">Conta origem</label>
                  <select name="id_conta_origem" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $c): ?>
                      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-6">
                  <label class="form-label">Conta destino</label>
                  <select name="id_conta_destino" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $c): ?>
                      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Data</label>
                  <input type="date" name="data" class="form-control" required>
                </div>

                <div class="col-12">
                  <label class="form-label">Valor</label>
                  <input name="valor" class="form-control input-money" placeholder="0,00" required>
                </div>

                <div class="col-12">
                  <label class="form-label">Descrição (opcional)</label>
                  <input type="text" name="descricao" class="form-control">
                </div>

              </div>

              <button class="btn btn-primary w-100 mt-3">Transferir</button>
            </form>
          </div>

          <!-- ===================== PARCELAS ===================== -->
          <div class="tab-pane fade" id="tab-parc">
            <h5 class="fw-semibold mb-3">Criar parcelamento</h5>

            <form method="POST" action="/financas/public/?url=lancamentos-parcelas-store">
              <div class="row g-2">

                <div class="col-6">
                  <label class="form-label">Tipo</label>
                  <select name="tipo" class="form-select" required>
                    <option value="D" selected>Despesa</option>
                    <option value="R">Receita</option>
                  </select>
                </div>

                <div class="col-6">
                  <label class="form-label">Parcelas</label>
                  <input type="number" name="total_parcelas" class="form-control" min="2" value="2" required>
                </div>

                <div class="col-12">
                  <label class="form-label">Conta</label>
                  <select name="id_conta" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $c): ?>
                      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Categoria</label>
                  <select name="id_categoria" class="form-select">
                    <option value="">(Opcional)</option>
                    <?php foreach ($categorias as $cat): ?>
                      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Valor total</label>
                  <input name="valor_total" class="form-control input-money" placeholder="0,00" required>
                </div>

                <div class="col-12">
                  <label class="form-label">Data da 1ª parcela</label>
                  <input type="date" name="data_inicio" class="form-control" required>
                </div>

                <div class="col-12">
                  <label class="form-label">Descrição</label>
                  <input type="text" name="descricao" class="form-control" placeholder="Ex: Notebook, Curso...">
                </div>

                <div class="col-12">
                  <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="pagar_primeira" id="pagar_primeira" checked>
                    <label class="form-check-label" for="pagar_primeira">
                      Marcar 1ª parcela como <b>paga</b> agora (atualiza saldo)
                    </label>
                  </div>
                </div>

              </div>

              <button class="btn btn-primary w-100 mt-3">Gerar parcelas</button>
            </form>
          </div>

          <!-- ===================== RECORRENTE ===================== -->
          <div class="tab-pane fade" id="tab-rec">
            <h5 class="fw-semibold mb-3">Criar recorrência</h5>

            <form method="POST" action="/financas/public/?url=recorrencias-store">
              <div class="row g-2">

                <div class="col-6">
                  <label class="form-label">Tipo</label>
                  <select name="tipo" class="form-select" required>
                    <option value="">Selecione</option>
                    <option value="R">Receita</option>
                    <option value="D">Despesa</option>
                  </select>
                </div>

                <div class="col-6">
                  <label class="form-label">Frequência</label>
                  <select name="frequencia" class="form-select" id="freq" required>
                    <option value="mensal" selected>Mensal</option>
                    <option value="semanal">Semanal</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Conta</label>
                  <select name="id_conta" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $c): ?>
                      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Categoria</label>
                  <select name="id_categoria" class="form-select">
                    <option value="">(Opcional)</option>
                    <?php foreach ($categorias as $cat): ?>
                      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Valor</label>
                  <input name="valor" class="form-control input-money" placeholder="0,00" required>
                </div>

                <div class="col-12" id="boxDiaMes">
                  <label class="form-label">Dia do mês</label>
                  <input type="number" name="dia_mes" class="form-control" min="1" max="28" value="5">
                </div>

                <div class="col-12 d-none" id="boxDiaSemana">
                  <label class="form-label">Dia da semana</label>
                  <select name="dia_semana" class="form-select">
                    <option value="1">Segunda</option>
                    <option value="2">Terça</option>
                    <option value="3">Quarta</option>
                    <option value="4">Quinta</option>
                    <option value="5">Sexta</option>
                    <option value="6">Sábado</option>
                    <option value="7">Domingo</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Descrição</label>
                  <input type="text" name="descricao" class="form-control" placeholder="Ex: Aluguel, Academia...">
                </div>

              </div>

              <button class="btn btn-primary w-100 mt-3">Salvar recorrência</button>
            </form>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center">
              <strong>Gerar mês</strong>
              <span class="text-muted small">cria lançamentos sem duplicar</span>
            </div>

            <form method="POST" action="/financas/public/?url=recorrencias-gerar-mes" class="row g-2 mt-2">
              <div class="col-6">
                <input type="number" name="ano" class="form-control" value="<?= (int)date('Y') ?>" required>
              </div>
              <div class="col-6">
                <input type="number" name="mes" class="form-control" value="<?= (int)date('m') ?>" min="1" max="12" required>
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="marcar_pago" id="marcar_pago" checked>
                  <label class="form-check-label" for="marcar_pago">Gerar como <b>pago</b> (atualiza saldo)</label>
                </div>
              </div>
              <div class="col-12">
                <button class="btn btn-outline-primary w-100">Gerar lançamentos do mês</button>
              </div>
            </form>

            <?php if (!empty($recorrencias)): ?>
              <div class="mt-3 small text-muted">
                Recorrências ativas: <b><?= count($recorrencias) ?></b>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- HISTÓRICO -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">

        <button class="btn btn-outline-secondary btn-sm mb-3"
                data-bs-toggle="collapse"
                data-bs-target="#filtrosLanc">
          Filtros
        </button>

        <div id="filtrosLanc" class="collapse mb-4">
          <form method="GET" class="row g-2">
            <input type="hidden" name="url" value="lancamentos">

            <div class="col-md-3">
              <label class="form-label">Data inicial</label>
              <input type="date" name="data_inicio" class="form-control" value="<?= $_GET['data_inicio'] ?? '' ?>">
            </div>

            <div class="col-md-3">
              <label class="form-label">Data final</label>
              <input type="date" name="data_fim" class="form-control" value="<?= $_GET['data_fim'] ?? '' ?>">
            </div>

            <div class="col-md-3">
              <label class="form-label">Conta</label>
              <select name="id_conta" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($contas as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ($_GET['id_conta'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Categoria</label>
              <select name="id_categoria" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= $cat['id'] ?>" <?= ($_GET['id_categoria'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-12 d-flex gap-2 mt-2">
              <button class="btn btn-primary btn-sm">Filtrar</button>
              <a href="/financas/public/?url=lancamentos" class="btn btn-outline-secondary btn-sm">Limpar</a>
            </div>
          </form>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-4">
            <div class="alert alert-success mb-0">
              Receitas<br>
              <strong>R$ <?= number_format($totalReceitas,2,',','.') ?></strong>
            </div>
          </div>

          <div class="col-md-4">
            <div class="alert alert-danger mb-0">
              Despesas<br>
              <strong>R$ <?= number_format($totalDespesas,2,',','.') ?></strong>
            </div>
          </div>

          <div class="col-md-4">
            <div class="alert alert-primary mb-0">
              Saldo<br>
              <strong>R$ <?= number_format($totalReceitas - $totalDespesas,2,',','.') ?></strong>
            </div>
          </div>
        </div>

        <div class="table-responsive-mobile">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Conta</th>
                <th>Status</th>
                <th class="text-end">Valor</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($lancamentos)): ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">Nenhum lançamento encontrado.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($lancamentos as $l): ?>
                  <?php
                    $status = $l['status'] ?? 'pago';
                    $catNome = $l['categoria'] ?? '—';
                    if (!empty($l['transferencia_id'])) $catNome = 'Transferência';
                    if (!empty($l['parcelamento_id'])) $catNome = 'Parcelado';
                    if (!empty($l['recorrencia_id'])) $catNome = 'Recorrente';
                  ?>
                  <tr>
                    <td><?= date('d/m/Y', strtotime($l['data'])) ?></td>
                    <td><?= htmlspecialchars($l['descricao'] ?? '') ?></td>
                    <td><?= htmlspecialchars($catNome) ?></td>
                    <td><?= htmlspecialchars($l['conta'] ?? '') ?></td>

                    <td>
                      <?php if ($status === 'pago'): ?>
                        <span class="badge bg-success">pago</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">pendente</span>
                      <?php endif; ?>
                    </td>

                    <td class="text-end <?= $l['tipo']=='R'?'text-success':'text-danger' ?>">
                      <?= $l['tipo']=='R'?'+':'-' ?>
                      R$ <?= number_format((float)$l['valor'],2,',','.') ?>
                    </td>

                    <td class="text-end">
                      <a href="/financas/public/?url=lancamentos-toggle-status&id=<?= $l['id'] ?>"
                         class="btn btn-outline-primary btn-sm">
                        <?= $status === 'pago' ? 'Marcar pendente' : 'Marcar pago' ?>
                      </a>

                      <a href="/financas/public/?url=lancamentos-edit&id=<?= $l['id'] ?>"
                         class="btn btn-outline-secondary btn-sm">
                        Editar
                      </a>

                      <a href="/financas/public/?url=lancamentos-delete&id=<?= $l['id'] ?>"
                         class="btn btn-outline-danger btn-sm"
                         onclick="return confirm('Excluir este lançamento?')">
                        Excluir
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
/* ====== TABLE MOBILE FIX ====== */
.table-responsive-mobile {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.table-responsive-mobile table {
  min-width: 900px;
}

/* nav pills mais “dashboard style” */
.nav-pills .nav-link{
  border: 1px solid rgba(0,0,0,.08);
}
.nav-pills .nav-link.active{
  border-color: transparent;
}
</style>

<script>
(function(){
  const freq = document.getElementById('freq');
  const boxDiaMes = document.getElementById('boxDiaMes');
  const boxDiaSemana = document.getElementById('boxDiaSemana');

  function sync(){
    if (!freq) return;
    if (freq.value === 'semanal') {
      boxDiaMes.classList.add('d-none');
      boxDiaSemana.classList.remove('d-none');
    } else {
      boxDiaSemana.classList.add('d-none');
      boxDiaMes.classList.remove('d-none');
    }
  }
  if (freq) {
    freq.addEventListener('change', sync);
    sync();
  }
})();
</script>
