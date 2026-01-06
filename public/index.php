<?php
session_start();

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

    case 'propostas-pdf':
        require '../app/views/auth_guard.php';
        PropostaController::pdf($pdo);
        break;

    case 'register-store':
        AuthController::register($pdo);
        break;

    case 'relatorio-csv':
        RelatorioController::exportCsv($pdo);
        break;
        
    case 'relatorio-pdf-executivo':
        RelatorioController::exportPdfExecutivo($pdo);
        break;

    case 'relatorio-pdf':
        RelatorioController::exportPdf($pdo);
        break;

    case 'lancamentos':
        require '../app/views/auth_guard.php';
        LancamentoController::index($pdo);
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
    
    case 'propostas':
        require '../app/views/auth_guard.php';
        PropostaController::index($pdo);
        break;

    case 'propostas-new':
        require '../app/views/auth_guard.php';
        PropostaController::create($pdo);
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

    case 'propostas-status':
        require '../app/views/auth_guard.php';
        PropostaController::setStatus($pdo);
        break;

    case 'propostas-delete':
        require '../app/views/auth_guard.php';
        PropostaController::delete($pdo);
        break;

    case 'orcamentos-store':
        require '../app/views/auth_guard.php';
        OrcamentoController::store($pdo);
        break;


    default:
        echo "Página não encontrada";
}

