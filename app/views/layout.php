<?php
require_once __DIR__ . '/../models/Orcamento.php';

$anoAtual = (int) date('Y');
$mesAtual = (int) date('m');

$usuarioLogado = isset($_SESSION['usuario']) && !empty($_SESSION['usuario']['id']);

$estourados = [];
$preventivos = [];
$qtdAlertasOrcamento = 0;

if ($usuarioLogado) {
    $estourados = Orcamento::estouradosNoMes($pdo, (int) $_SESSION['usuario']['id'], $anoAtual, $mesAtual);
    $preventivos = Orcamento::preventivosNoMes($pdo, (int) $_SESSION['usuario']['id'], $anoAtual, $mesAtual);
    $qtdAlertasOrcamento = count($estourados) + count($preventivos);
}

$rotaAtual = $_GET['url'] ?? 'dashboard';
function menuActive($rotaAtual, $rota)
{
    return $rotaAtual === $rota ? 'active fw-semibold' : '';
}
function menuOpenRelatorios($rotaAtual)
{
    return in_array($rotaAtual, ['relatorio-csv', 'relatorio-pdf', 'relatorio-pdf-executivo'], true);
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
        body {
            background: #f6f7fb;
        }

        #wrapper {
            min-height: 100vh;
        }

        /* Sidebar */
        #sidebar-wrapper {
            width: 270px;
            min-width: 270px;
            background: #ffffff !important;
            border-right: 1px solid rgba(0, 0, 0, .08) !important;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px;
            border-bottom: 1px solid rgba(0, 0, 0, .06);
        }

        .sidebar-logo {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(13, 110, 253, .10);
            color: #0d6efd;
            font-size: 18px;
        }

        .sidebar-title {
            line-height: 1.1;
        }

        .sidebar-title strong {
            font-size: 14px;
        }

        .sidebar-title small {
            font-size: 12px;
            color: #6c757d;
        }

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
            background: rgba(13, 110, 253, .08);
            color: #0d6efd;
        }

        .sidebar-link.active {
            background: rgba(13, 110, 253, .12);
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
            border-left: 2px solid rgba(0, 0, 0, .06);
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
            background: rgba(0, 0, 0, .04);
        }

        .submenu a.active {
            background: rgba(13, 110, 253, .10);
            color: #0d6efd;
            font-weight: 600;
        }

        /* Topbar */
        .topbar {
            background: #fff !important;
            border-bottom: 1px solid rgba(0, 0, 0, .08) !important;
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(25, 135, 84, .12);
            color: #198754;
            font-weight: 700;
        }

        /* Content */
        main.container-fluid {
            max-width: 1400px;
        }

        /* Mobile */
        @media (max-width: 991px) {
            #sidebar-wrapper {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
                z-index: 1045;
                box-shadow: 0 10px 40px rgba(0, 0, 0, .15);
                transition: .2s;
            }

            #wrapper.toggled #sidebar-wrapper {
                transform: translateX(0);
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, .35);
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

                <?php $rotasFin = ['dashboard', 'contas', 'lancamentos', 'categorias', 'orcamentos', 'relatorios']; ?>
                <?php $rotasCom = ['propostas', 'clientes', 'servicos', 'agenda']; ?>

                <!-- FINANÇAS (GRUPO) -->
                <a class="sidebar-link is-group d-flex justify-content-between align-items-center <?= in_array($rotaAtual, $rotasFin, true) ? 'active fw-semibold' : '' ?>"
                    data-bs-toggle="collapse" href="#menuFinancas" role="button"
                    aria-expanded="<?= in_array($rotaAtual, $rotasFin, true) ? 'true' : 'false' ?>"
                    aria-controls="menuFinancas">

                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-cash-coin"></i>
                        <span>Finanças</span>
                    </span>

                    <i class="bi bi-chevron-down chev"></i>
                </a>

                <div class="collapse <?= in_array($rotaAtual, $rotasFin, true) ? 'show' : '' ?> sidebar-sub"
                    id="menuFinancas">

                    <a class="sidebar-link is-sub <?= menuActive($rotaAtual, 'dashboard') ?>"
                        href="/financas/public/?url=dashboard">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>

                    <a class="sidebar-link is-sub <?= menuActive($rotaAtual, 'contas') ?>"
                        href="/financas/public/?url=contas">
                        <i class="bi bi-wallet2"></i>
                        <span>Contas</span>
                    </a>

                    <a class="sidebar-link is-sub <?= menuActive($rotaAtual, 'lancamentos') ?>"
                        href="/financas/public/?url=lancamentos">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Lançamentos</span>
                    </a>

                    <a class="sidebar-link is-sub <?= menuActive($rotaAtual, 'categorias') ?>"
                        href="/financas/public/?url=categorias">
                        <i class="bi bi-tags"></i>
                        <span>Categorias</span>
                    </a>

                    <a class="sidebar-link is-sub <?= menuActive($rotaAtual, 'orcamentos') ?>"
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

                    <a class="sidebar-link is-sub <?= menuActive($rotaAtual, 'relatorios') ?>"
                        href="/financas/public/?url=relatorios">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Relatórios</span>
                    </a>
                </div>


                <!-- COMERCIAL (GRUPO) -->
                <a class="sidebar-link is-group d-flex justify-content-between align-items-center <?= in_array($rotaAtual, $rotasCom, true) ? 'active fw-semibold' : '' ?>"
                    data-bs-toggle="collapse" href="#menuComercial" role="button"
                    aria-expanded="<?= in_array($rotaAtual, $rotasCom, true) ? 'true' : 'false' ?>"
                    aria-controls="menuComercial">

                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-briefcase"></i>
                        <span>Comercial</span>
                    </span>

                    <i class="bi bi-chevron-down chev"></i>
                </a>

                <div class="collapse <?= in_array($rotaAtual, $rotasCom, true) ? 'show' : '' ?> sidebar-sub"
                    id="menuComercial">

                    <a class="sidebar-link is-sub <?= menuActive($rotaAtual, 'propostas') ?>"
                        href="/financas/public/?url=propostas">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Orçamentos / Propostas</span>
                    </a>

                    <a class="sidebar-link is-sub <?= menuActive($rotaAtual, 'clientes') ?>"
                        href="/financas/public/?url=clientes">
                        <i class="bi bi-people"></i>
                        <span>Clientes</span>
                    </a>

                    <a class="sidebar-link is-sub <?= menuActive($rotaAtual, 'servicos') ?>"
                        href="/financas/public/?url=servicos">
                        <i class="bi bi-box-seam"></i>
                        <span>Serviços</span>
                    </a>

                    <a class="sidebar-link is-sub <?= menuActive($rotaAtual, 'agenda') ?>"
                        href="/financas/public/?url=agenda">
                        <i class="bi bi-calendar2-week"></i>
                        <span>Agendamentos</span>
                    </a>
                </div>


                <!-- SAIR -->
                <div class="mt-2 pt-2 border-top" style="border-color: rgba(0,0,0,.06) !important;">
                    <a class="sidebar-link text-danger" href="/financas/public/?url=logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sair</span>
                    </a>
                </div>

            </div>


        </aside>

        <!-- PAGE CONTENT -->
        <div id="page-content-wrapper" class="w-100">

            <?php
            $u = $_SESSION['usuario'] ?? [];
            $foto = avatarUrl($u);
            ?>

            <nav class="navbar navbar-expand-lg bg-white border-bottom px-3 px-md-4">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary" id="menu-toggle" type="button">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="d-none d-md-block">
                        <div class="fw-semibold">Finanças</div>
                        <div class="text-muted small">Seu painel</div>
                    </div>
                </div>

                <div class="ms-auto dropdown">
                    <a class="d-flex align-items-center gap-2 text-decoration-none text-dark" href="#"
                        data-bs-toggle="dropdown" aria-expanded="false">

                        <?php if ($foto): ?>
                            <img src="<?= $foto ?>" class="avatar-sm" alt="Avatar">
                        <?php else: ?>
                            <div class="avatar-sm avatar-fallback">
                                <?= iniciais($u['nome'] ?? 'Usuário') ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex flex-column align-items-start lh-1">
                            <span class="fw-semibold"><?= htmlspecialchars($u['nome'] ?? 'Usuário') ?></span>
                            <span
                                class="text-muted small d-none d-sm-inline"><?= htmlspecialchars($u['email'] ?? '') ?></span>
                        </div>

                        <i class="bi bi-chevron-down ms-1"></i>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="/financas/public/?url=perfil">
                                <i class="bi bi-person"></i> Perfil
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                href="/financas/public/?url=logout">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    </ul>
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
        (function () {
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
    <script src="/financas/public/js/mask.js"></script>

</body>

</html>