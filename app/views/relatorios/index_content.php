<?php
$receitas = (float)($resumo['receitas'] ?? 0);
$despesas = (float)($resumo['despesas'] ?? 0);
$saldo    = (float)($resumo['saldo'] ?? 0);
$qtd      = (int)($resumo['qtd'] ?? 0);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$meses = [
  1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',
  7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez'
];

$hasDompdf = (
    file_exists(__DIR__ . '/../../libs/dompdf/autoload.inc.php') ||
    file_exists(__DIR__ . '/../../libs/dompdf/vendor/autoload.php')
);
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <h1 class="mb-0">Relatórios</h1>
        <div class="text-muted">Central de análises e exportações</div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-secondary" href="/financas/public/?url=relatorios&ano=<?= (int)$f['ano'] ?>&mes=<?= (int)$f['mes'] ?>">
            Atualizar
        </a>

        <a class="btn btn-outline-success"
           href="/financas/public/?url=relatorios-csv&<?= http_build_query($f) ?>">
            CSV
        </a>

        <a class="btn btn-outline-dark <?= $hasDompdf ? '' : 'disabled' ?>"
           href="/financas/public/?url=relatorios-pdf&<?= http_build_query($f) ?>">
            PDF simples
        </a>

        <a class="btn btn-dark <?= $hasDompdf ? '' : 'disabled' ?>"
           href="/financas/public/?url=relatorios-pdf-executivo&<?= http_build_query($f) ?>">
            PDF executivo
        </a>
    </div>
</div>

<?php if (!$hasDompdf): ?>
    <div class="alert alert-warning">
        <strong>PDF desativado:</strong> Dompdf não encontrado no projeto.
        (CSV funciona normalmente)
    </div>
<?php endif; ?>

<!-- FILTROS -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="fw-semibold">Filtros</div>
            <div class="text-muted small">Período padrão: mês selecionado</div>
        </div>

        <form method="GET" action="/financas/public/">
            <input type="hidden" name="url" value="relatorios">

            <div class="row g-2">
                <div class="col-sm-6 col-md-2">
                    <label class="form-label">Mês</label>
                    <select name="mes" class="form-select">
                        <?php for($m=1;$m<=12;$m++): ?>
                            <option value="<?= $m ?>" <?= (int)$f['mes']===$m?'selected':'' ?>>
                                <?= $meses[$m] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-sm-6 col-md-2">
                    <label class="form-label">Ano</label>
                    <input type="number" name="ano" class="form-control" value="<?= (int)$f['ano'] ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Data início</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?= h($f['data_inicio']) ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Data fim</label>
                    <input type="date" name="data_fim" class="form-control" value="<?= h($f['data_fim']) ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Conta</label>
                    <select name="id_conta" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach($contas as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ($f['id_conta'] ?? '') == $c['id'] ? 'selected':'' ?>>
                                <?= h($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Categoria</label>
                    <select name="id_categoria" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach($categorias as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ($f['id_categoria'] ?? '') == $c['id'] ? 'selected':'' ?>>
                                <?= h($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="R" <?= ($f['tipo'] ?? '')==='R'?'selected':'' ?>>Receita</option>
                        <option value="D" <?= ($f['tipo'] ?? '')==='D'?'selected':'' ?>>Despesa</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pago" <?= ($f['status'] ?? '')==='pago'?'selected':'' ?>>Pago</option>
                        <option value="pendente" <?= ($f['status'] ?? '')==='pendente'?'selected':'' ?>>Pendente</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Busca rápida (front)</label>
                    <input type="text" id="q" class="form-control" placeholder="Descrição / conta / categoria...">
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap mt-3">
                <button class="btn btn-primary">Aplicar filtros</button>
                <a class="btn btn-outline-secondary" href="/financas/public/?url=relatorios">Limpar</a>
            </div>
        </form>
    </div>
</div>

<!-- RESUMO -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Receitas</div>
                <div class="fs-3 text-success fw-semibold">R$ <?= number_format($receitas,2,',','.') ?></div>
                <div class="text-muted small">Qtd: <?= (int)$qtd ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Despesas</div>
                <div class="fs-3 text-danger fw-semibold">R$ <?= number_format($despesas,2,',','.') ?></div>
                <div class="text-muted small">Qtd: <?= (int)$qtd ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Saldo</div>
                <div class="fs-3 fw-semibold <?= $saldo>=0?'text-primary':'text-danger' ?>">
                    R$ <?= number_format($saldo,2,',','.') ?>
                </div>
                <div class="text-muted small">Período: <?= h($f['data_inicio']) ?> → <?= h($f['data_fim']) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- BLOCOS: POR CATEGORIA / POR CONTA -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="fw-semibold mb-2">Top categorias</div>
                <?php if (empty($porCategoria)): ?>
                    <div class="text-muted">Sem dados.</div>
                <?php else: ?>
                    <?php
                    // pega top 8
                    $top = array_slice($porCategoria, 0, 8);
                    $max = 0;
                    foreach($top as $t){ $max = max($max, (float)$t['total']); }
                    if ($max <= 0) $max = 1;
                    ?>
                    <?php foreach($top as $t): ?>
                        <?php
                        $nome = $t['nome'] ?? 'Sem categoria';
                        $total = (float)$t['total'];
                        $w = min(($total / $max) * 100, 100);
                        $isR = ($t['tipo'] ?? '') === 'R';
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <div class="fw-semibold"><?= h($nome) ?></div>
                                <div class="<?= $isR?'text-success':'text-danger' ?>">
                                    R$ <?= number_format($total,2,',','.') ?>
                                </div>
                            </div>
                            <div class="progress" style="height:10px;">
                                <div class="progress-bar" style="width:<?= (float)$w ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="fw-semibold mb-2">Por conta</div>
                <?php if (empty($porConta)): ?>
                    <div class="text-muted">Sem dados.</div>
                <?php else: ?>
                    <?php
                    $top = array_slice($porConta, 0, 8);
                    $max = 0;
                    foreach($top as $t){ $max = max($max, (float)$t['total']); }
                    if ($max <= 0) $max = 1;
                    ?>
                    <?php foreach($top as $t): ?>
                        <?php
                        $nome = $t['nome'] ?? 'Sem conta';
                        $total = (float)$t['total'];
                        $w = min(($total / $max) * 100, 100);
                        $isR = ($t['tipo'] ?? '') === 'R';
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <div class="fw-semibold"><?= h($nome) ?></div>
                                <div class="<?= $isR?'text-success':'text-danger' ?>">
                                    R$ <?= number_format($total,2,',','.') ?>
                                </div>
                            </div>
                            <div class="progress" style="height:10px;">
                                <div class="progress-bar" style="width:<?= (float)$w ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- TABELA / PREVIEW -->
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div class="fw-semibold">Lançamentos (preview)</div>
            <div class="text-muted small">Use a busca para filtrar na tela</div>
        </div>

        <div class="table-responsive-mobile">
            <table class="table table-hover align-middle mb-0" id="tbl">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Conta</th>
                        <th>Status</th>
                        <th class="text-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($lancamentos)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Sem lançamentos para este filtro.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($lancamentos as $l): ?>
                        <tr data-q="<?= strtolower(
                            ($l['descricao'] ?? '').' '.($l['categoria'] ?? '').' '.($l['conta'] ?? '')
                        ) ?>">
                            <td><?= date('d/m/Y', strtotime($l['data'])) ?></td>
                            <td><?= h($l['descricao']) ?></td>
                            <td><?= h($l['categoria']) ?></td>
                            <td><?= h($l['conta']) ?></td>
                            <td>
                                <?php if (($l['status'] ?? 'pago') === 'pendente'): ?>
                                    <span class="badge bg-warning text-dark">pendente</span>
                                <?php else: ?>
                                    <span class="badge bg-success">pago</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end <?= ($l['tipo'] ?? 'D') === 'R' ? 'text-success':'text-danger' ?>">
                                <?= ($l['tipo'] ?? 'D') === 'R' ? '+':'-' ?>
                                R$ <?= number_format((float)$l['valor'],2,',','.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
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
</style>

<script>
(function(){
    const input = document.getElementById('q');
    if(!input) return;

    input.addEventListener('input', function(){
        const term = (this.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#tbl tbody tr');

        rows.forEach(r => {
            const q = (r.getAttribute('data-q') || '');
            if(!term) { r.style.display = ''; return; }
            r.style.display = q.includes(term) ? '' : 'none';
        });
    });
})();
</script>
