<?php
require_once __DIR__ . '/../models/Dashboard.php';
require_once __DIR__ . '/../models/Orcamento.php';

class DashboardController
{
    public static function index($pdo)
    {
        $idUsuario = usuarioId();
        $ano = (int)date('Y');
        $mes = (int)date('m');

        $resumo       = Dashboard::resumoMensal($pdo, $idUsuario, $ano, $mes);
        $saldoGeral   = Dashboard::saldoGeral($pdo, $idUsuario);
        $linhaMensal  = Dashboard::resumoMensalLinha($pdo, $idUsuario, $ano);

        // 🔔 Alertas por categoria (se já implementou)
        $orcamentosEstourados  = Orcamento::estouradosNoMes($pdo, $idUsuario, $ano, $mes);
        $orcamentosPreventivo  = Orcamento::preventivosNoMes($pdo, $idUsuario, $ano, $mes);

        // 📊 Resumo geral do orçamento
        $orcamentoGeral = Orcamento::resumoGeralMes($pdo, $idUsuario, $ano, $mes);
        
        require '../app/views/dashboard.php';
    }
}
