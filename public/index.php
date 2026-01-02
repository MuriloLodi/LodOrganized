<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ContaController.php';

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

    case 'register':
        require '../app/views/register.php';
        break;

    case 'register-store':
        AuthController::register($pdo);
        break;

    case 'logout':
        AuthController::logout();
        break;

    case 'dashboard':
        require '../app/views/auth_guard.php';
        require '../app/views/dashboard.php';
        break;


    default:
        echo "Página não encontrada";
}

