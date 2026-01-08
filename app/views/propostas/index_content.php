<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="mb-0">Orçamentos / Propostas</h1>
    <div class="text-muted">Crie propostas profissionais e gere PDF</div>
  </div>
  <a class="btn btn-primary" href="/financas/public/?url=propostas-new">
    <i class="bi bi-plus-lg"></i> Nova proposta
  </a>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger">
    <?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-body">

    <?php if (empty($propostas)): ?>
      <div class="text-muted">Você ainda não criou nenhuma proposta.</div>
    <?php else: ?>
      <div class="table-responsive-mobile">
        <table class="table align-middle table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Nº</th>
              <th>Cliente</th>
              <th>Emissão</th>
              <th>Status</th>
              <th class="text-end">Total</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($propostas as $p): ?>
            <?php
              $badge = 'bg-secondary';
              if ($p['status'] === 'enviado') $badge = 'bg-primary';
              if ($p['status'] === 'aprovado') $badge = 'bg-success';
              if ($p['status'] === 'recusado') $badge = 'bg-danger';
            ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars($p['numero']) ?></td>
              <td><?= htmlspecialchars($p['cliente_nome']) ?></td>
              <td><?= date('d/m/Y', strtotime($p['data_emissao'])) ?></td>
              <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($p['status']) ?></span></td>
              <td class="text-end">R$ <?= number_format((float)$p['total'], 2, ',', '.') ?></td>
              <td class="text-end">
                <a class="btn btn-outline-secondary btn-sm"
                   href="/financas/public/?url=propostas-edit&id=<?= (int)$p['id'] ?>">
                  <i class="bi bi-pencil"></i>
                </a>

                <a class="btn btn-outline-dark btn-sm"
                   href="/financas/public/?url=propostas-pdf&id=<?= (int)$p['id'] ?>">
                  <i class="bi bi-file-earmark-pdf"></i>
                </a>

                <a class="btn btn-outline-danger btn-sm"
                   href="/financas/public/?url=propostas-delete&id=<?= (int)$p['id'] ?>"
                   onclick="return confirm('Excluir esta proposta?')">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>
