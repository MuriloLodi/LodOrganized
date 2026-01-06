<div class="d-flex justify-content-between align-items-start align-items-md-center gap-3 mb-4 flex-wrap">
  <div>
    <h1 class="mb-1">Propostas</h1>
    <div class="text-muted">Orçamentos para enviar para clientes</div>
  </div>
  <a class="btn btn-primary" href="/financas/public/?url=propostas-new">+ Nova proposta</a>
</div>

<?php if (!empty($_SESSION['erro'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div>
<?php endif; ?>

<?php if (empty($propostas)): ?>
  <div class="alert alert-info mb-0">Você ainda não criou nenhuma proposta.</div>
<?php else: ?>
  <div class="table-responsive-mobile">
    <table class="table table-hover align-middle">
      <thead class="table-light">
      <tr>
        <th>Número</th>
        <th>Cliente</th>
        <th>Status</th>
        <th>Emissão</th>
        <th style="width:220px">Ações</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach ($propostas as $p): ?>
        <?php
          $badge = 'bg-secondary';
          if ($p['status']==='enviado') $badge='bg-primary';
          if ($p['status']==='aprovado') $badge='bg-success';
          if ($p['status']==='recusado') $badge='bg-danger';
        ?>
        <tr>
          <td class="fw-semibold">#<?= (int)$p['numero'] ?>/<?= (int)$p['ano'] ?></td>
          <td><?= htmlspecialchars($p['cliente_nome'] ?? '—') ?></td>
          <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($p['status']) ?></span></td>
          <td><?= date('d/m/Y', strtotime($p['data_emissao'])) ?></td>
          <td class="d-flex gap-2 flex-wrap">
            <a class="btn btn-dark btn-sm"
              href="/financas/public/?url=propostas-pdf&id=<?= (int)$p['id'] ?>">
              Baixar PDF
            </a>

            <a class="btn btn-sm btn-outline-primary" href="/financas/public/?url=propostas-edit&id=<?= (int)$p['id'] ?>">Abrir</a>

            <form method="POST" action="/financas/public/?url=propostas-delete" onsubmit="return confirm('Excluir proposta?');">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Excluir</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
