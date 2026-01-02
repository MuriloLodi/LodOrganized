<h1 class="mb-4">Categorias</h1>

<div class="row">
    <!-- FORM -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5>Nova categoria</h5>

                <?php if (!empty($_SESSION['erro'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/financas/public/?url=categorias-store">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Selecione</option>
                            <option value="R">Receita</option>
                            <option value="D">Despesa</option>
                        </select>
                    </div>

                    <button class="btn btn-primary w-100">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- LISTA -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Minhas categorias</h5>

                <?php if (empty($categorias)): ?>
                    <p class="text-muted">Nenhuma categoria cadastrada.</p>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Tipo</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categorias as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['nome']) ?></td>
                                <td>
                                    <?php if ($c['tipo'] === 'R'): ?>
                                        <span class="badge bg-success">Receita</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Despesa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
