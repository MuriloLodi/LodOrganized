<?php
$u = $_SESSION['usuario'] ?? [];
$foto = avatarUrl($u);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Perfil</h1>
        <div class="text-muted">Atualize seus dados e sua foto</div>
    </div>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['sucesso'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['sucesso']); unset($_SESSION['sucesso']); ?>
    </div>
<?php endif; ?>

<div class="row g-3">
    <!-- Avatar -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Foto do perfil</h5>

                <div class="d-flex align-items-center gap-3">
                    <?php if ($foto): ?>
                        <img src="<?= $foto ?>" class="avatar-lg" alt="Avatar">
                    <?php else: ?>
                        <div class="avatar-lg avatar-fallback">
                            <?= iniciais($u['nome'] ?? 'Usuário') ?>
                        </div>
                    <?php endif; ?>

                    <div class="w-100">
                        <form method="POST" action="/financas/public/?url=perfil-avatar" enctype="multipart/form-data">
                            <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm" required>
                            <button class="btn btn-primary btn-sm w-100 mt-2">Atualizar foto</button>
                        </form>

                        <?php if ($foto): ?>
                            <form method="POST" action="/financas/public/?url=perfil-avatar-delete" class="mt-2"
                                  onsubmit="return confirm('Remover foto do perfil?')">
                                <button class="btn btn-outline-danger btn-sm w-100">Remover foto</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-muted small mt-3">
                    JPG, PNG ou WEBP • até 3MB
                </div>
            </div>
        </div>
    </div>

    <!-- Dados -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Seus dados</h5>

                <form method="POST" action="/financas/public/?url=perfil-update">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input class="form-control" name="nome" required value="<?= htmlspecialchars($u['nome'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input class="form-control" type="email" name="email" required value="<?= htmlspecialchars($u['email'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <div class="border rounded-3 p-3 mt-2">
                                <div class="fw-semibold mb-2">Segurança</div>

                                <label class="form-label">Senha atual (obrigatória para trocar e-mail/senha)</label>
                                <input class="form-control" type="password" name="senha_atual" placeholder="••••••••">

                                <div class="row g-2 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label">Nova senha (opcional)</label>
                                        <input class="form-control" type="password" name="nova_senha" placeholder="mín. 6 caracteres">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirmar nova senha</label>
                                        <input class="form-control" type="password" name="nova_senha_confirm" placeholder="repita a nova senha">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <button class="btn btn-primary">Salvar alterações</button>
                        <a class="btn btn-outline-secondary" href="/financas/public/?url=dashboard">Voltar</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
