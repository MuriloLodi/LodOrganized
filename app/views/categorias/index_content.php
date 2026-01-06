<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Categorias</h1>
        <div class="text-muted">Organize suas receitas e despesas</div>
    </div>
</div>

<div class="row g-3">

<!-- FORM -->
<div class="col-lg-4">
    <div class="card h-100">
        <div class="card-body">
            <h5 class="fw-semibold mb-3">Nova categoria</h5>

            <?php if (!empty($_SESSION['erro'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/financas/public/?url=categorias-store">
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input name="nome" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <option value="">Selecione</option>
                        <option value="R">Receita</option>
                        <option value="D">Despesa</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ícone</label>
                    <select name="icone" class="form-select">
                        <option value="bi-tag">🏷️ Padrão</option>
                        <option value="bi-cup-hot">☕ Alimentação</option>
                        <option value="bi-house">🏠 Casa</option>
                        <option value="bi-cart">🛒 Compras</option>
                        <option value="bi-car-front">🚗 Transporte</option>
                    </select>
                </div>

                <button class="btn btn-primary w-100">
                    Salvar categoria
                </button>
            </form>
        </div>
    </div>
</div>

<!-- LISTA -->
<div class="col-lg-8">
    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0">Minhas categorias</h5>
                <input type="text" id="buscaCategoria"
                       class="form-control form-control-sm w-50"
                       placeholder="Buscar categoria...">
            </div>

            <?php if (empty($categorias)): ?>
                <div class="text-muted">Nenhuma categoria cadastrada.</div>
            <?php else: ?>
                <div class="categoria-scroll-mobile">
                <div class="list-group list-group-flush">
<?php foreach ($categorias as $c): ?>
    <div class="list-group-item categoria-item"
         data-nome="<?= strtolower($c['nome']) ?>">

        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">

            <!-- ESQUERDA -->
            <div class="d-flex align-items-start gap-3 flex-wrap flex-lg-nowrap w-100">

                <i class="bi <?= $c['icone'] ?> fs-4 mt-1"></i>

                <form method="POST"
                      action="/financas/public/?url=categorias-update"
                      class="d-flex flex-column flex-md-row gap-2 w-100">

                    <input type="hidden" name="id" value="<?= $c['id'] ?>">

                    <input name="nome"
                           class="form-control form-control-sm"
                           value="<?= htmlspecialchars($c['nome']) ?>">

                    <select name="icone" class="form-select form-select-sm">
                        <option value="<?= $c['icone'] ?>"><?= $c['icone'] ?></option>
                        <option value="bi-cup-hot">☕</option>
                        <option value="bi-house">🏠</option>
                        <option value="bi-cart">🛒</option>
                        <option value="bi-car-front">🚗</option>
                    </select>

                    <button class="btn btn-outline-primary btn-sm w-100 w-md-auto">
                        Salvar
                    </button>
                </form>
            </div>

            <!-- DIREITA -->
            <div class="text-lg-end text-start">

                <div class="fw-semibold">
                    R$ <?= number_format($c['total'], 2, ',', '.') ?>
                </div>

                <?php if (Categoria::canDelete($pdo, $_SESSION['usuario']['id'], $c['id'])): ?>
                    <form method="POST"
                          action="/financas/public/?url=categorias-delete"
                          onsubmit="return confirm('Excluir categoria?')">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button class="btn btn-outline-danger btn-sm w-100 w-lg-auto mt-1">
                            Excluir
                        </button>
                    </form>
                <?php else: ?>
                    <span class="badge bg-secondary mt-1">Em uso</span>
                <?php endif; ?>

            </div>

        </div>
    </div>
<?php endforeach; ?>
</div>

                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</div>

<!-- BUSCA -->
<script>
document.getElementById('buscaCategoria').addEventListener('keyup', e => {
    const termo = e.target.value.toLowerCase();
    document.querySelectorAll('.categoria-item').forEach(item => {
        item.style.display =
            item.dataset.nome.includes(termo) ? '' : 'none';
    });
});
</script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
