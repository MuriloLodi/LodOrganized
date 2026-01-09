<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ContaController.php';
require_once __DIR__ . '/../app/controllers/CategoriaController.php';
require_once __DIR__ . '/../app/controllers/LancamentoController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/OrcamentoController.php';
require_once __DIR__ . '/../app/controllers/RelatorioController.php';
require_once __DIR__ . '/../app/helpers/helpers.php';
require_once __DIR__ . '/../app/controllers/PropostaController.php';
require_once __DIR__ . '/../app/controllers/PerfilController.php';
require_once __DIR__ . '/../app/controllers/ClienteController.php';
require_once __DIR__ . '/../app/controllers/ServicoController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/AgendaController.php';
require_once __DIR__ . '/../app/controllers/FunilController.php';

$rota = $_GET['url'] ?? 'login';

switch ($rota) {

    case 'login':
        require '../app/views/login.php';
        break;

    case 'login-auth':
        AuthController::login($pdo);
        break;

    case 'contas':
        require '../app/views/auth_guard.php';
        ContaController::index($pdo);
        break;

    case 'contas-store':
        require '../app/views/auth_guard.php';
        ContaController::store($pdo);
        break;

    case 'categorias':
        require '../app/views/auth_guard.php';
        CategoriaController::index($pdo);
        break;

    case 'categorias-store':
        require '../app/views/auth_guard.php';
        CategoriaController::store($pdo);
        break;

    case 'register':
        require '../app/views/register.php';
        break;

    case 'register-store':
        AuthController::register($pdo);
        break;

    case 'relatorio-csv':
        RelatorioController::exportCsv($pdo);
        break;
    case 'lancamentos-transferencia-store':
        require '../app/views/auth_guard.php';
        LancamentoController::storeTransferencia($pdo);
        break;

    case 'lancamentos-parcelas-store':
        require '../app/views/auth_guard.php';
        LancamentoController::storeParcelas($pdo);
        break;

    case 'recorrencias-store':
        require '../app/views/auth_guard.php';
        LancamentoController::storeRecorrencia($pdo);
        break;

    case 'recorrencias-gerar-mes':
        require '../app/views/auth_guard.php';
        LancamentoController::gerarRecorrenciasMes($pdo);
        break;

    case 'lancamentos-toggle-status':
        require '../app/views/auth_guard.php';
        LancamentoController::toggleStatus($pdo);
        break;

    case 'anexos-upload':
        require '../app/views/auth_guard.php';
        LancamentoController::uploadAnexo($pdo);
        break;

    case 'anexos-delete':
        require '../app/views/auth_guard.php';
        LancamentoController::deleteAnexo($pdo);
        break;
    case 'dashboard-meta-store':
        require '../app/views/auth_guard.php';
        DashboardController::salvarMeta($pdo);
        break;


    case 'relatorios':
        require '../app/views/auth_guard.php';
        RelatorioController::index($pdo);
        break;

    case 'relatorios-csv':
        require '../app/views/auth_guard.php';
        RelatorioController::exportCsv($pdo);
        break;

    case 'relatorios-pdf':
        require '../app/views/auth_guard.php';
        RelatorioController::exportPdf($pdo);
        break;

    case 'relatorios-pdf-executivo':
        require '../app/views/auth_guard.php';
        RelatorioController::exportPdfExecutivo($pdo);
        break;

    case 'relatorio-pdf-executivo':
        RelatorioController::exportPdfExecutivo($pdo);
        break;

    case 'relatorio-pdf':
        RelatorioController::exportPdf($pdo);
        break;

    case 'propostas':
        require '../app/views/auth_guard.php';
        PropostaController::index($pdo);
        break;

    case 'propostas-new':
        require '../app/views/auth_guard.php';
        PropostaController::createForm($pdo);
        break;

    case 'propostas-store':
        require '../app/views/auth_guard.php';
        PropostaController::store($pdo);
        break;

    case 'propostas-edit':
        require '../app/views/auth_guard.php';
        PropostaController::edit($pdo);
        break;

    case 'propostas-update':
        require '../app/views/auth_guard.php';
        PropostaController::update($pdo);
        break;

    case 'propostas-delete':
        require '../app/views/auth_guard.php';
        PropostaController::delete($pdo);
        break;

    case 'propostas-pdf':
        require '../app/views/auth_guard.php';
        PropostaController::pdf($pdo);
        break;

    case 'lancamentos':
        require '../app/views/auth_guard.php';
        LancamentoController::index($pdo);
        break;

    case 'perfil':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/PerfilController.php';
        PerfilController::index($pdo);
        break;

    case 'perfil-update':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/PerfilController.php';
        PerfilController::update($pdo);
        break;

    case 'perfil-avatar':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/PerfilController.php';
        PerfilController::updateAvatar($pdo);
        break;

    case 'perfil-avatar-delete':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/PerfilController.php';
        PerfilController::deleteAvatar($pdo);
        break;


    case 'lancamentos-store':
        require '../app/views/auth_guard.php';
        LancamentoController::store($pdo);
        break;

    case 'logout':
        AuthController::logout();
        break;

    case 'dashboard':
        require '../app/views/auth_guard.php';
        DashboardController::index($pdo);
        break;

    case 'lancamentos-edit':
        require '../app/views/auth_guard.php';
        LancamentoController::edit($pdo);
        break;

    case 'lancamentos-update':
        require '../app/views/auth_guard.php';
        LancamentoController::update($pdo);
        break;

    case 'lancamentos-delete':
        require '../app/views/auth_guard.php';
        LancamentoController::delete($pdo);
        break;

    case 'orcamentos':
        require '../app/views/auth_guard.php';
        OrcamentoController::index($pdo);
        break;

    case 'categorias-delete':
        require '../app/views/auth_guard.php';
        CategoriaController::delete($pdo);
        break;

    case 'categorias-update':
        require '../app/views/auth_guard.php';
        CategoriaController::update($pdo);
        break;

    case 'contas-delete':
        require '../app/views/auth_guard.php';
        ContaController::delete($pdo);
        break;


    case 'orcamentos-store':
        require '../app/views/auth_guard.php';
        OrcamentoController::store($pdo);
        break;

    case 'clientes':
        require '../app/views/auth_guard.php';
        ClienteController::index($pdo);
        break;

    case 'clientes-store':
        require '../app/views/auth_guard.php';
        ClienteController::store($pdo);
        break;

    case 'clientes-update':
        require '../app/views/auth_guard.php';
        ClienteController::update($pdo);
        break;

    case 'clientes-delete':
        require '../app/views/auth_guard.php';
        ClienteController::delete($pdo);
        break;


    case 'servicos':
        require '../app/views/auth_guard.php';
        ServicoController::index($pdo);
        break;

    case 'servicos-store':
        require '../app/views/auth_guard.php';
        ServicoController::store($pdo);
        break;

    case 'servicos-update':
        require '../app/views/auth_guard.php';
        ServicoController::update($pdo);
        break;

    case 'servicos-delete':
        require '../app/views/auth_guard.php';
        ServicoController::delete($pdo);
        break;

    case 'servicos-toggle':
        require '../app/views/auth_guard.php';
        ServicoController::toggle($pdo);
        break;

    case 'admin':
        require '../app/views/admin_guard.php';
        AdminController::index($pdo);
        break;

    case 'admin-usuarios':
        require '../app/views/admin_guard.php';
        AdminController::usuarios($pdo);
        break;

    case 'admin-usuario-toggle-block':
        require '../app/views/admin_guard.php';
        AdminController::toggleBlock($pdo);
        break;

    case 'admin-usuario-reset-senha':
        require '../app/views/admin_guard.php';
        AdminController::resetSenha($pdo);
        break;

    case 'admin-metricas':
        require '../app/views/admin_guard.php';
        AdminController::metricas($pdo);
        break;

    case 'funil':
        require '../app/views/auth_guard.php';
        FunilController::index($pdo);
        break;

    case 'funil-store':
        require '../app/views/auth_guard.php';
        FunilController::store($pdo);
        break;

    case 'funil-update':
        require '../app/views/auth_guard.php';
        FunilController::update($pdo);
        break;

    case 'funil-delete':
        require '../app/views/auth_guard.php';
        FunilController::delete($pdo);
        break;

    case 'funil-move':
        require '../app/views/auth_guard.php';
        FunilController::move($pdo);
        break;
    case 'agenda':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::index($pdo);
        break;

    case 'agenda-new':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::createForm($pdo);
        break;

    case 'agenda-store':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::store($pdo);
        break;

    case 'agenda-edit':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::edit($pdo);
        break;

    case 'agenda-update':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::update($pdo);
        break;

    case 'agenda-delete':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::delete($pdo);
        break;

    case 'agenda-status':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::status($pdo);
        break;

    case 'agenda-bloqueio-store':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::bloqueioStore($pdo);
        break;

    case 'agenda-bloqueio-delete':
        require '../app/views/auth_guard.php';
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::bloqueioDelete($pdo);
        break;

    /* PÚBLICO */
    case 'agendar':
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::publicoForm($pdo);
        break;

    case 'agendar-slots':
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::publicoSlots($pdo);
        break;

    case 'agendar-store':
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::publicoStore($pdo);
        break;

    /* CRON NOTIFICAÇÕES */
    case 'agenda-notificacoes':
        require_once __DIR__ . '/../app/controllers/AgendaController.php';
        AgendaController::notificacoes($pdo);
        break;


    default:
        echo "Página não encontrada";
}

