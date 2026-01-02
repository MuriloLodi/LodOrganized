<?php
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController
{
    public static function index($pdo)
    {
        $idUsuario = $_SESSION['usuario']['id'];

        $ano = date('Y');
        $mes = date('m');

        $resumo = Dashboard::resumoMensal(
            $pdo,
            $idUsuario,
            $ano,
            $mes
        );

        require '../app/views/dashboard.php';
    }
}
