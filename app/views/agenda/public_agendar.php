<?php
$token = $_GET['token'] ?? '';
$nomeDono = $user['nome'] ?? 'Profissional';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Agendar com <?= htmlspecialchars($nomeDono) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4" style="max-width:720px;">
  <div class="mb-3">
    <h2 class="mb-0">Agendar com <?= htmlspecialchars($nomeDono) ?></h2>
    <div class="text-muted">Escolha uma data e horário disponível</div>
  </div>

  <?php if (!empty($_SESSION['erro_publico'])): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($_SESSION['erro_publico']); unset($_SESSION['erro_publico']); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['sucesso_publico'])): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($_SESSION['sucesso_publico']); unset($_SESSION['sucesso_publico']); ?>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="/financas/public/?url=agendar-store" id="formPublico">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Data</label>
            <input type="date" class="form-control" name="data" id="dt" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Horário</label>
            <select class="form-select" name="hora" id="hr" required>
              <option value="">Selecione a data...</option>
            </select>
          </div>
        </div>

        <div class="row g-2 mt-1">
          <div class="col-md-6">
            <label class="form-label">Duração (min)</label>
            <select class="form-select" name="duracao" id="dur">
              <option value="30">30</option>
              <option value="45">45</option>
              <option value="60" selected>60</option>
              <option value="90">90</option>
            </select>
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <button type="button" class="btn btn-outline-secondary w-100" id="btnReload">
              Ver horários
            </button>
          </div>
        </div>

        <hr class="my-3">

        <div class="mb-2">
          <label class="form-label">Seu nome</label>
          <input class="form-control" name="nome" required>
        </div>

        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">E-mail (opcional)</label>
            <input class="form-control" name="email" type="email">
          </div>
          <div class="col-md-6">
            <label class="form-label">Telefone (opcional)</label>
            <input class="form-control" name="telefone">
          </div>
        </div>

        <div class="mt-2">
          <label class="form-label">Observação (opcional)</label>
          <textarea class="form-control" name="observacao" rows="3" placeholder="Ex: Quero falar sobre..."></textarea>
        </div>

        <button class="btn btn-primary w-100 mt-3">
          Confirmar agendamento
        </button>

        <div class="text-muted small mt-3">
          Horários disponíveis respeitam agendamentos existentes e bloqueios (feriados/pausas).
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  const token = <?= json_encode($token) ?>;
  const dt = document.getElementById('dt');
  const hr = document.getElementById('hr');
  const dur = document.getElementById('dur');
  const btn = document.getElementById('btnReload');

  async function loadSlots(){
    const date = dt.value;
    const d = dur.value || 60;

    hr.innerHTML = `<option value="">Carregando...</option>`;

    if(!date){
      hr.innerHTML = `<option value="">Selecione a data...</option>`;
      return;
    }

    const url = `/financas/public/?url=agendar-slots&token=${encodeURIComponent(token)}&date=${encodeURIComponent(date)}&dur=${encodeURIComponent(d)}`;

    try{
      const res = await fetch(url, { cache: "no-store" });
      const json = await res.json();

      if(!json.ok || !json.slots || json.slots.length === 0){
        hr.innerHTML = `<option value="">Nenhum horário disponível</option>`;
        return;
      }

      hr.innerHTML = `<option value="">Selecione...</option>` + json.slots.map(s => `<option value="${s}">${s}</option>`).join('');
    }catch(e){
      hr.innerHTML = `<option value="">Erro ao carregar horários</option>`;
    }
  }

  dt.addEventListener('change', loadSlots);
  dur.addEventListener('change', loadSlots);
  btn.addEventListener('click', loadSlots);

})();
</script>

</body>
</html>
