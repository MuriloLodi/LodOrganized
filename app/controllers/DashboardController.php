<?php
require_once __DIR__ . '/../models/Dashboard.php';
require_once __DIR__ . '/../models/Orcamento.php';
require_once __DIR__ . '/../models/Lancamento.php';

class DashboardController
{
    public static function index($pdo)
    {
        $idUsuario = usuarioId();

        $ano = (int)date('Y');
        $mes = (int)date('m');

        // Mês anterior
        $mesAnt = $mes - 1;
        $anoAnt = $ano;
        if ($mesAnt <= 0) {
            $mesAnt = 12;
            $anoAnt = $ano - 1;
        }

        // Resumo do mês (cards)
        $resumo = Dashboard::resumoMensal($pdo, $idUsuario, $ano, $mes);
        $resumoAnt = Dashboard::resumoMensal($pdo, $idUsuario, $anoAnt, $mesAnt);

        // Saldo total (somatório contas)
        $saldoGeral = Dashboard::saldoGeral($pdo, $idUsuario);

        // Linha mensal (gráfico)
        $linhaMensal = Dashboard::resumoMensalLinha($pdo, $idUsuario, $ano);

        // Orçamento geral + alertas orçamento
        $orcamentoGeral = Orcamento::resumoGeralMes($pdo, $idUsuario, $ano, $mes);
        $orcamentosEstourados = Orcamento::estouradosNoMes($pdo, $idUsuario, $ano, $mes);
        $orcamentosPreventivo = Orcamento::preventivosNoMes($pdo, $idUsuario, $ano, $mes);

        // Top despesas (onde está o gasto)
        $topDespesas = Lancamento::topDespesasMes($pdo, $idUsuario, $ano, $mes, 5);

        // Monta alertas do topo (prioridade)
        $alertas = [];

        if (($orcamentoGeral['orcado'] ?? 0) > 0) {
            $p = (float)($orcamentoGeral['percentual'] ?? 0);

            if ($p >= 100) {
                $alertas[] = ['tipo' => 'danger', 'msg' => '🔴 Orçamento estourado neste mês.'];
            } elseif ($p >= 80) {
                $alertas[] = ['tipo' => 'warning', 'msg' => '🟡 Atenção: você já consumiu 80%+ do orçamento do mês.'];
            }
        }

        if ((float)$saldoGeral < 0) {
            $alertas[] = ['tipo' => 'danger', 'msg' => '❌ Seu saldo geral está negativo.'];
        }

        // Categorias em risco (unifica estourado + preventivo)
        $categoriasRisco = [];
        foreach ($orcamentosEstourados as $e) {
            $categoriasRisco[] = [
                'status' => 'danger',
                'nome' => $e['nome'],
                'orcado' => (float)$e['orcado'],
                'total_real' => (float)$e['total_real'],
                'percentual' => (float)$e['percentual'],
            ];
        }
        foreach ($orcamentosPreventivo as $p) {
            $categoriasRisco[] = [
                'status' => 'warning',
                'nome' => $p['nome'],
                'orcado' => (float)$p['orcado'],
                'total_real' => (float)$p['total_real'],
                'percentual' => (float)$p['percentual'],
            ];
        }

        // Ordena por percentual desc
        usort($categoriasRisco, fn($a,$b) => $b['percentual'] <=> $a['percentual']);

        require '../app/views/dashboard.php';
    }
}
