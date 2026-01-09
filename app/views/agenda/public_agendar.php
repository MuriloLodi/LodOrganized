<?php
$token = $_GET['token'] ?? '';
$nomeDono = $user['nome'] ?? 'Profissional';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Agendar com <?= h($nomeDono) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<style>
  /* ====== PADRÃO FRONT (public) ====== */
  :root{
    --soft-border: rgba(0,0,0,.08);
    --soft-shadow: 0 16px 44px rgba(0,0,0,.10);
    --soft-shadow-sm: 0 10px 26px rgba(0,0,0,.08);
  }

  .page-wrap{ max-width: 820px; }

  .hero{
    border: 1px solid var(--soft-border);
    border-radius: 22px;
    background: radial-gradient(1200px 420px at 0% 0%, rgba(13,110,253,.12), transparent 60%),
                radial-gradient(900px 380px at 100% 10%, rgba(25,135,84,.10), transparent 55%),
                #fff;
    box-shadow: var(--soft-shadow-sm);
  }

  .hero-title{
    font-weight: 900;
    letter-spacing: -.6px;
    margin: 0;
  }

  .hero-sub{ color:#6c757d; }

  .card-soft{
    border: 1px solid var(--soft-border);
    border-radius: 22px;
    box-shadow: var(--soft-shadow);
    overflow: hidden;
    background:#fff;
  }

  .card-soft .card-body{ padding: 1.25rem; }

  .pill{
    border: 1px solid var(--soft-border);
    border-radius: 999px;
    padding: .35rem .6rem;
    background: rgba(255,255,255,.7);
    color: #6c757d;
    font-weight: 700;
    font-size: .85rem;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
  }

  .step{
    border: 1px solid var(--soft-border);
    border-radius: 18px;
    padding: .9rem;
    background: #fff;
  }

  .step .title{
    font-weight: 850;
    letter-spacing: -.2px;
    margin: 0;
  }

  .help{ color:#6c757d; font-size: .9rem; }

  .btn{
    border-radius: 14px;
    font-weight: 750;
  }

  .form-control, .form-select{
    border-radius: 14px;
    border-color: var(--soft-border);
  }

  .form-control:focus, .form-select:focus{
    box-shadow: 0 0 0 .2rem rgba(13,110,253,.15);
  }

  .divider{
    height: 1px;
    background: rgba(0,0,0,.08);
    margin: 1rem 0;
  }

  .hint-box{
    border: 1px dashed rgba(0,0,0,.15);
    background: rgba(0,0,0,.02);
    border-radius: 16px;
    padding: .85rem;
    color: #6c757d;
    font-size: .9rem;
  }

  .sticky-cta{
    position: sticky;
    bottom: 0;
    background: linear-gradient(to top, rgba(248,249,250,1), rgba(248,249,250,.70), rgba(248,249,250,0));
    padding-top: .75rem;
    margin-top: 1rem;
  }

  /* melhor leitura no mobile */
  @media (max-width: 576px){
    .card-soft .card-body{ padding: 1rem; }
    .hero{ border-radius: 18px; }
    .card-soft{ border-radius: 18px; }
  }
</style>

<div class="container py-4 page-wrap">

  <!-- HEADER / HERO -->
  <div class="hero p-3 p-md-4 mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <h1 class="hero-title h3 mb-1">Agendar com <?= h($nomeDono) ?></h1>
        <div class="hero-sub">Escolha uma data e um horário disponível</div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <span class="pill"><i class="bi bi-shield-check"></i> Reservas seguras</span>
        <span class="pill"><i class="bi bi-clock-history"></i> Em poucos cliques</span>
      </div>
    </div>
  </div>

  <?php if (!empty($_SESSION['erro_publico'])): ?>
    <div class="alert alert-danger">
      <?= h($_SESSION['erro_publico']); unset($_SESSION['erro_publico']); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['sucesso_publico'])): ?>
    <div class="alert alert-success">
      <?= h($_SESSION['sucesso_publico']); unset($_SESSION['sucesso_publico']); ?>
    </div>
  <?php endif; ?>

  <div class="card-soft">
    <div class="card-body">

      <form method="POST" action="/financas/public/?url=agendar-store" id="formPublico" novalidate>
        <input type="hidden" name="token" value="<?= h($token) ?>">

        <!-- PASSO 1 -->
        <div class="step mb-3">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div>
              <div class="title">1) Escolha data e duração</div>
              <div class="help">A lista de horários atualiza automaticamente</div>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnReload">
              <i class="bi bi-arrow-repeat me-1"></i> Ver horários
            </button>
          </div>

          <div class="row g-2">
            <div class="col-md-4">
              <label class="form-label">Data</label>
              <input type="date" class="form-control" name="data" id="dt" required>
              <div class="invalid-feedback">Selecione uma data.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Duração</label>
              <select class="form-select" name="duracao" id="dur">
                <option value="30">30 min</option>
                <option value="45">45 min</option>
                <option value="60" selected>60 min</option>
                <option value="90">90 min</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Horário</label>
              <select class="form-select" name="hora" id="hr" required disabled>
                <option value="">Selecione a data...</option>
              </select>
              <div class="invalid-feedback">Selecione um horário.</div>
            </div>
          </div>

          <div class="hint-box mt-3">
            <i class="bi bi-info-circle me-1"></i>
            Horários disponíveis respeitam agendamentos existentes e bloqueios (pausas/feriados).
          </div>
        </div>

        <!-- PASSO 2 -->
        <div class="step">
          <div class="title mb-2">2) Seus dados</div>

          <div class="mb-2">
            <label class="form-label">Seu nome *</label>
            <input class="form-control" name="nome" id="nm" required placeholder="Ex: João Silva">
            <div class="invalid-feedback">Informe seu nome.</div>
          </div>

          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">E-mail (opcional)</label>
              <input class="form-control" name="email" type="email" placeholder="exemplo@email.com">
            </div>
            <div class="col-md-6">
              <label class="form-label">Telefone (opcional)</label>
              <input class="form-control" name="telefone" placeholder="(00) 00000-0000">
            </div>
          </div>

          <div class="mt-2">
            <label class="form-label">Observação (opcional)</label>
            <textarea class="form-control" name="observacao" rows="3" placeholder="Ex: Quero falar sobre..."></textarea>
          </div>

          <div class="sticky-cta">
            <button class="btn btn-primary w-100 mt-2" id="btnConfirmar" disabled>
              <i class="bi bi-check2-circle me-1"></i> Confirmar agendamento
            </button>

            <div class="help small mt-2 text-center">
              Ao confirmar, você reserva o horário selecionado.
            </div>
          </div>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
(function(){
  const token = <?= json_encode($token) ?>;

  const form = document.getElementById('formPublico');
  const dt   = document.getElementById('dt');
  const hr   = document.getElementById('hr');
  const dur  = document.getElementById('dur');
  const btn  = document.getElementById('btnReload');
  const btnConfirmar = document.getElementById('btnConfirmar');
  const nm = document.getElementById('nm');

  function setLoading(state){
    if(state){
      hr.disabled = true;
      hr.innerHTML = `<option value="">Carregando...</option>`;
    }
  }

  function setEmpty(msg){
    hr.disabled = true;
    hr.innerHTML = `<option value="">${msg}</option>`;
  }

  function setOptions(slots){
    hr.disabled = false;
    hr.innerHTML = `<option value="">Selecione...</option>` +
      slots.map(s => `<option value="${s}">${s}</option>`).join('');
  }

  function updateConfirmState(){
    const ok = !!(dt.value && hr.value && (nm.value || '').trim().length >= 2);
    btnConfirmar.disabled = !ok;
  }

  async function loadSlots(){
    const date = dt.value;
    const d = dur.value || 60;

    if(!date){
      setEmpty('Selecione a data...');
      updateConfirmState();
      return;
    }

    setLoading(true);

    const url = `/financas/public/?url=agendar-slots&token=${encodeURIComponent(token)}&date=${encodeURIComponent(date)}&dur=${encodeURIComponent(d)}`;

    try{
      const res = await fetch(url, { cache: "no-store" });
      const json = await res.json();

      if(!json || !json.ok || !Array.isArray(json.slots) || json.slots.length === 0){
        setEmpty('Nenhum horário disponível');
      } else {
        setOptions(json.slots);
      }
    }catch(e){
      setEmpty('Erro ao carregar horários');
    } finally {
      updateConfirmState();
    }
  }

  // auto: hoje como default (não altera backend)
  try{
    const today = new Date();
    const pad = n => String(n).padStart(2,'0');
    const iso = `${today.getFullYear()}-${pad(today.getMonth()+1)}-${pad(today.getDate())}`;
    if(!dt.value) dt.value = iso;
  }catch(e){}

  dt.addEventListener('change', loadSlots);
  dur.addEventListener('change', loadSlots);
  btn.addEventListener('click', loadSlots);

  hr.addEventListener('change', updateConfirmState);
  nm.addEventListener('input', updateConfirmState);

  // validação bootstrap (front)
  form.addEventListener('submit', function(e){
    if(!form.checkValidity()){
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
    updateConfirmState();
  });

  // carrega slots ao abrir já com a data preenchida
  loadSlots();
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
