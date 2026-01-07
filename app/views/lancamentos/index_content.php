<?php
$totalReceitas = 0;
$totalDespesas = 0;

foreach ($lancamentos as $l) {
    if ($l['tipo'] === 'R') {
        $totalReceitas += $l['valor'];
    } else {
        $totalDespesas += $l['valor'];
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Lançamentos</h1>
        <div class="text-muted">Controle completo das movimentações</div>
    </div>
</div>

<div class="row g-3">

    <!-- NOVO LANÇAMENTO -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Novo lançamento</h5>

                <?php if (!empty($_SESSION['erro'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['erro'];
                        unset($_SESSION['erro']); ?>
                    </div>
                <?php endif; ?>

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
                            <label class="form-label">Data</label>
                            <input type="date" name="data" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Conta</label>
                            <select name="id_conta" class="form-select" required>
                                <option value="">Selecione</option>
                                <?php foreach ($contas as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Categoria</label>
                            <select name="id_categoria" class="form-select" required>
                                <option value="">Selecione</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>">
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Valor</label>
                            <input type="text" name="valor" class="form-control money-br" inputmode="numeric"
                                placeholder="0,00" autocomplete="off" required>
                            <small class="text-muted">Use vírgula para centavos. Ex: 12,50</small>

                        </div>

                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <input type="text" name="descricao" class="form-control">
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 mt-3">
                        Salvar lançamento
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- HISTÓRICO -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">

                <!-- FILTROS -->
                <button class="btn btn-outline-secondary btn-sm mb-3" data-bs-toggle="collapse"
                    data-bs-target="#filtrosLanc">
                    Filtros
                </button>

                <div id="filtrosLanc" class="collapse mb-4">
                    <form method="GET" class="row g-2">
                        <input type="hidden" name="url" value="lancamentos">

                        <div class="col-md-3">
                            <label class="form-label">Data inicial</label>
                            <input type="date" name="data_inicio" class="form-control"
                                value="<?= $_GET['data_inicio'] ?? '' ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Data final</label>
                            <input type="date" name="data_fim" class="form-control"
                                value="<?= $_GET['data_fim'] ?? '' ?>">
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
                            <a href="/financas/public/?url=lancamentos" class="btn btn-outline-secondary btn-sm">
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>

                <!-- RESUMO -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="alert alert-success mb-0">
                            Receitas<br>
                            <strong>R$ <?= number_format($totalReceitas, 2, ',', '.') ?></strong>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="alert alert-danger mb-0">
                            Despesas<br>
                            <strong>R$ <?= number_format($totalDespesas, 2, ',', '.') ?></strong>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="alert alert-primary mb-0">
                            Saldo<br>
                            <strong>R$ <?= number_format($totalReceitas - $totalDespesas, 2, ',', '.') ?></strong>
                        </div>
                    </div>
                </div>

                <!-- TABELA -->
                <div class="table-responsive-mobile">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th>Conta</th>
                                <th class="text-end">Valor</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lancamentos as $l): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($l['data'])) ?></td>
                                    <td><?= htmlspecialchars($l['descricao']) ?></td>
                                    <td><?= htmlspecialchars($l['categoria']) ?></td>
                                    <td><?= htmlspecialchars($l['conta']) ?></td>
                                    <td class="text-end <?= $l['tipo'] == 'R' ? 'text-success' : 'text-danger' ?>">
                                        <?= $l['tipo'] == 'R' ? '+' : '-' ?>
                                        R$ <?= number_format($l['valor'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-end">
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
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>

<style>
    .table-responsive-mobile {
        width: 100%;
        overflow-x: auto;
    }

    .table-responsive-mobile table {
        min-width: 900px;
    }
</style>
