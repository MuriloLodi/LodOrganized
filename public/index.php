<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ContaController.php';
require_once __DIR__ . '/../app/controllers/CategoriaController.php';
require_once __DIR__ . '/../app/controllers/LancamentoController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';

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

    default:
        echo "Página não encontrada";
}

