<?php
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController
{
    public static function index($pdo)
    {
        $idUsuario = $_SESSION['usuario']['id'];

        $ano = date('Y');
        $mes = date('m');

        // Resumo do mês (cards)
        $resumo = Dashboard::resumoMensal($pdo, $idUsuario, $ano, $mes);

        // Saldo total (somatório das contas)
        $saldoGeral = Dashboard::saldoGeral($pdo, $idUsuario);

        // Linha mensal para gráfico executivo
        $linhaMensal = Dashboard::resumoMensalLinha($pdo, $idUsuario, $ano);

        // (Opcional) por categoria para futuras tabelas/pizza no dashboard
        // Se você ainda não usa, pode comentar.
        $porCategoria = Dashboard::resumoPorCategoria($pdo, $idUsuario, []);

        // Mantém o padrão do seu projeto
        require '../app/views/dashboard.php';
    }
}
