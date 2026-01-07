<?php
// garante sessão
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Contas financeiras</h1>
        <div class="text-muted">Gerencie saldos e movimentações</div>
    </div>
</div>

<div class="row g-3">

    <!-- ================= FORM NOVA CONTA ================= -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Nova conta</h5>

                <?php if (!empty($_SESSION['erro'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['erro'];
                        unset($_SESSION['erro']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/financas/public/?url=contas-store">
                    <div class="mb-3">
                        <label class="form-label">Nome da conta</label>
                        <input name="nome" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Saldo inicial</label>
                        <input type="text" name="saldo" class="form-control money-br" inputmode="numeric"
                            placeholder="0,00"
                            value="<?= isset($conta['saldo_atual']) ? number_format((float) $conta['saldo_atual'], 2, ',', '.') : '0,00' ?>">

                    </div>

                    <button class="btn btn-primary w-100">
                        Salvar conta
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= LISTA CONTAS ================= -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold mb-0">Minhas contas</h5>
                    <input type="text" id="buscaConta" class="form-control form-control-sm w-50"
                        placeholder="Buscar conta...">
                </div>

                <?php if (empty($contas)): ?>
                    <div class="text-muted">Nenhuma conta cadastrada.</div>
                <?php else: ?>

                    <div class="list-group list-group-flush">

                        <?php foreach ($contas as $c): ?>
                            <div class="list-group-item conta-item" data-nome="<?= strtolower($c['nome']) ?>">

                                <div class="d-flex flex-column flex-lg-row gap-3 justify-content-between">

                                    <!-- ESQUERDA: EDITAR -->
                                    <form method="POST" action="/financas/public/?url=contas-update"
                                        class="d-flex flex-column flex-md-row gap-2 w-100">

                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">

                                        <input name="nome" class="form-control form-control-sm"
                                            value="<?= htmlspecialchars($c['nome']) ?>">

                                        <button class="btn btn-outline-primary btn-sm w-100 w-md-auto">
                                            Salvar
                                        </button>
                                    </form>

                                    <!-- DIREITA: SALDO + AÇÕES -->
                                    <div class="text-start text-lg-end">

                                        <div class="fw-semibold <?= $c['saldo_atual'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            R$ <?= number_format($c['saldo_atual'], 2, ',', '.') ?>
                                        </div>

                                        <?php if (Conta::canDelete($pdo, $_SESSION['usuario']['id'], $c['id'])): ?>
                                            <form method="POST" action="/financas/public/?url=contas-delete"
                                                onsubmit="return confirm('Excluir conta?')">
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
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- ================= BUSCA ================= -->
<script>
    document.getElementById('buscaConta')?.addEventListener('keyup', e => {
        const termo = e.target.value.toLowerCase();
        document.querySelectorAll('.conta-item').forEach(item => {
            item.style.display = item.dataset.nome.includes(termo) ? '' : 'none';
        });
    });
</script>

<!-- ================= MOBILE REFINEMENT ================= -->
<style>
    .conta-item {
        padding: 12px 0;
    }

    @media (max-width: 576px) {
        .conta-item button {
            font-size: .9rem;
        }
    }
</style>