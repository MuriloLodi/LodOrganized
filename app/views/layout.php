<?php
require_once __DIR__ . '/../models/Orcamento.php';

$anoAtual = (int)date('Y');
$mesAtual = (int)date('m');

$usuarioLogado = isset($_SESSION['usuario']) && !empty($_SESSION['usuario']['id']);

$estourados  = [];
$preventivos = [];
$qtdAlertasOrcamento = 0;

if ($usuarioLogado) {
    $estourados  = Orcamento::estouradosNoMes($pdo, (int)$_SESSION['usuario']['id'], $anoAtual, $mesAtual);
    $preventivos = Orcamento::preventivosNoMes($pdo, (int)$_SESSION['usuario']['id'], $anoAtual, $mesAtual);
    $qtdAlertasOrcamento = count($estourados) + count($preventivos);
}

$rotaAtual = $_GET['url'] ?? 'dashboard';
function menuActive($rotaAtual, $rota) {
    return $rotaAtual === $rota ? 'active fw-semibold' : '';
}
function menuOpenRelatorios($rotaAtual) {
    return in_array($rotaAtual, ['relatorio-csv','relatorio-pdf','relatorio-pdf-executivo'], true);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'Finanças' ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS do app -->
    <link rel="stylesheet" href="/financas/public/css/app.css">

    <style>
        /* ====== LAYOUT PRO ====== */
        body { background: #f6f7fb; }
        #wrapper { min-height: 100vh; }

        /* Sidebar */
        #sidebar-wrapper {
            width: 270px;
            min-width: 270px;
            background: #ffffff !important;
            border-right: 1px solid rgba(0,0,0,.08) !important;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px;
            border-bottom: 1px solid rgba(0,0,0,.06);
        }
        .sidebar-logo {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(13,110,253,.10);
            color: #0d6efd;
            font-size: 18px;
        }
        .sidebar-title {
            line-height: 1.1;
        }
        .sidebar-title strong { font-size: 14px; }
        .sidebar-title small { font-size: 12px; color: #6c757d; }

        .sidebar-menu {
            padding: 10px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            text-decoration: none;
            color: #212529;
            transition: .15s;
            margin-bottom: 6px;
        }
        .sidebar-link:hover {
            background: rgba(13,110,253,.08);
            color: #0d6efd;
        }
        .sidebar-link.active {
            background: rgba(13,110,253,.12);
            color: #0d6efd;
        }
        .sidebar-link .bi {
            font-size: 18px;
            width: 22px;
            text-align: center;
        }

        .submenu {
            margin-left: 34px;
            margin-top: 6px;
            padding-left: 10px;
            border-left: 2px solid rgba(0,0,0,.06);
        }
        .submenu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            text-decoration: none;
            color: #495057;
            margin-bottom: 6px;
            font-size: 14px;
        }
        .submenu a:hover {
            background: rgba(0,0,0,.04);
        }
        .submenu a.active {
            background: rgba(13,110,253,.10);
            color: #0d6efd;
            font-weight: 600;
        }

        /* Topbar */
        .topbar {
            background: #fff !important;
            border-bottom: 1px solid rgba(0,0,0,.08) !important;
        }
        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(25,135,84,.12);
            color: #198754;
            font-weight: 700;
        }

        /* Content */
        main.container-fluid { max-width: 1400px; }

        /* Mobile */
        @media (max-width: 991px) {
            #sidebar-wrapper {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
                z-index: 1045;
                box-shadow: 0 10px 40px rgba(0,0,0,.15);
                transition: .2s;
            }
            #wrapper.toggled #sidebar-wrapper {
                transform: translateX(0);
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.35);
                z-index: 1040;
            }
            #wrapper.toggled .sidebar-overlay {
                display: block;
            }
        }
    </style>
</head>

<body>

<div class="d-flex" id="wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="sidebar-title">
                <strong>Finanças</strong><br>
                <small>Controle do mês</small>
            </div>
        </div>

        <div class="sidebar-menu">
            <a class="sidebar-link <?= menuActive($rotaAtual, 'dashboard') ?>"
               href="/financas/public/?url=dashboard">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <a class="sidebar-link <?= menuActive($rotaAtual, 'contas') ?>"
               href="/financas/public/?url=contas">
                <i class="bi bi-wallet2"></i>
                <span>Contas</span>
            </a>

            <a class="sidebar-link <?= menuActive($rotaAtual, 'lancamentos') ?>"
               href="/financas/public/?url=lancamentos">
                <i class="bi bi-arrow-left-right"></i>
                <span>Lançamentos</span>
            </a>

            <!-- RELATÓRIOS -->
            <?php $openRel = menuOpenRelatorios($rotaAtual); ?>
            <a class="sidebar-link <?= $openRel ? 'active fw-semibold' : '' ?>"
               data-bs-toggle="collapse"
               href="#menuRelatorios"
               role="button"
               aria-expanded="<?= $openRel ? 'true' : 'false' ?>"
               aria-controls="menuRelatorios">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Relatórios</span>
                <span class="ms-auto"><i class="bi bi-chevron-down"></i></span>
            </a>

            <div class="collapse <?= $openRel ? 'show' : '' ?>" id="menuRelatorios">
                <div class="submenu">
                    <a class="<?= menuActive($rotaAtual, 'relatorio-csv') ?>"
                       href="/financas/public/?url=relatorio-csv&ano=<?= date('Y') ?>&mes=<?= date('m') ?>">
                        <i class="bi bi-download"></i>
                        <span>CSV mensal</span>
                    </a>

                    <a class="<?= menuActive($rotaAtual, 'relatorio-pdf') ?>"
                       href="/financas/public/?url=relatorio-pdf&ano=<?= date('Y') ?>&mes=<?= date('m') ?>">
                        <i class="bi bi-filetype-pdf"></i>
                        <span>PDF simples</span>
                    </a>

                    <a class="<?= menuActive($rotaAtual, 'relatorio-pdf-executivo') ?>"
                       href="/financas/public/?url=relatorio-pdf-executivo&ano=<?= date('Y') ?>&mes=<?= date('m') ?>">
                        <i class="bi bi-graph-up"></i>
                        <span>PDF executivo</span>
                    </a>
                </div>
            </div>

            <a class="sidebar-link <?= menuActive($rotaAtual, 'categorias') ?>"
               href="/financas/public/?url=categorias">
                <i class="bi bi-tags"></i>
                <span>Categorias</span>
            </a>

            <a class="sidebar-link <?= menuActive($rotaAtual, 'orcamentos') ?>"
               href="/financas/public/?url=orcamentos">
                <i class="bi bi-pie-chart"></i>
                <span class="d-flex align-items-center gap-2">
                    Orçamento
                    <?php if ($qtdAlertasOrcamento > 0): ?>
                        <span class="badge <?= count($estourados) > 0 ? 'bg-danger' : 'bg-warning text-dark' ?>">
                            <?= $qtdAlertasOrcamento ?>
                        </span>
                    <?php endif; ?>
                </span>
            </a>

            <div class="mt-2 pt-2 border-top" style="border-color: rgba(0,0,0,.06) !important;">
                <a class="sidebar-link text-danger"
                   href="/financas/public/?url=logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sair</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- PAGE CONTENT -->
    <div id="page-content-wrapper" class="w-100">

        <!-- TOPBAR -->
        <nav class="navbar topbar navbar-expand-lg px-3 px-lg-4">
            <button class="btn btn-outline-secondary" id="menu-toggle" type="button">
                <i class="bi bi-list"></i>
            </button>

            <div class="ms-auto d-flex align-items-center gap-2">
                <?php
                    $nomeUser = $usuarioLogado ? ($_SESSION['usuario']['nome'] ?? 'Usuário') : 'Usuário';
                    $iniciais = strtoupper(substr(trim($nomeUser), 0, 1));
                ?>
                <div class="avatar"><?= htmlspecialchars($iniciais) ?></div>

                <div class="dropdown">
                    <a class="dropdown-toggle text-decoration-none text-dark"
                       href="#"
                       data-bs-toggle="dropdown">
                        <?= htmlspecialchars($nomeUser) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item disabled" href="#"><i class="bi bi-person me-2"></i>Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/financas/public/?url=logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Sair
                        </a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- CONTEÚDO -->
        <main class="container-fluid p-3 p-lg-4">
            <?php require $view; ?>
        </main>

        <!-- FOOTER -->
        <footer class="text-center text-muted py-3 border-top" style="border-color: rgba(0,0,0,.08) !important;">
            © <?= date('Y') ?> • App Finanças
        </footer>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function(){
    const wrapper = document.getElementById("wrapper");
    const btn = document.getElementById("menu-toggle");
    const overlay = document.getElementById("sidebarOverlay");

    if (btn) {
        btn.addEventListener("click", function () {
            wrapper.classList.toggle("toggled");
        });
    }
    if (overlay) {
        overlay.addEventListener("click", function () {
            wrapper.classList.remove("toggled");
        });
    }
})();
</script>

<!-- Chart.js (se suas telas usarem) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>
