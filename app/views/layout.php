<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?? 'Finanças' ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS do app -->
    <link rel="stylesheet" href="/financas/public/css/app.css">
</head>
<body>

<div class="d-flex" id="wrapper">

    <!-- SIDEBAR -->
    <div class="border-end bg-light" id="sidebar-wrapper">
        <div class="sidebar-heading border-bottom fw-bold p-3">
            💰 Finanças
        </div>

        <div class="list-group list-group-flush">
            <a href="/financas/public/?url=dashboard" class="list-group-item list-group-item-action">
                Dashboard
            </a>
            <a href="/financas/public/?url=contas" class="list-group-item list-group-item-action">
                Contas
            </a>
            <a href="/financas/public/?url=lancamentos" class="list-group-item list-group-item-action">
                Lançamentos
            </a>
            <a href="#" class="list-group-item list-group-item-action">
                Relatórios
            </a>
            <a href="/financas/public/?url=categorias" class="list-group-item list-group-item-action">
                Categorias
            </a>
        </div>
    </div>

    <!-- PAGE CONTENT -->
    <div id="page-content-wrapper" class="w-100">

        <!-- TOPBAR -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4">
            <button class="btn btn-outline-secondary" id="menu-toggle">☰</button>

            <div class="ms-auto dropdown">
                <a class="dropdown-toggle text-decoration-none text-dark" href="#" data-bs-toggle="dropdown">
                    <?= $_SESSION['usuario']['nome'] ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">Perfil</a>
                    </li>
                    <li>
                        <a class="dropdown-item text-danger" href="/financas/public/?url=logout">Sair</a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- CONTEÚDO -->
        <main class="container-fluid p-4">
            <?php require $view; ?>
        </main>

        <!-- FOOTER -->
        <footer class="text-center text-muted py-3 border-top">
            © <?= date('Y') ?> • App Finanças
        </footer>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS do app -->
<script>
document.getElementById("menu-toggle").addEventListener("click", function () {
    document.getElementById("wrapper").classList.toggle("toggled");
});
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>
