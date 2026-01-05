<?php
require_once __DIR__ . '/../models/Lancamento.php';
require_once __DIR__ . '/../libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

class RelatorioController
{
    public static function exportCsv($pdo)
    {
        $idUsuario = usuarioId();
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $mes = (int)($_GET['mes'] ?? date('m'));

        $dados = Lancamento::allByMes($pdo, $idUsuario, $ano, $mes);

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=relatorio_{$mes}_{$ano}.csv");

        $out = fopen('php://output', 'w');

        // Cabeçalho
        fputcsv($out, [
            'Data',
            'Descrição',
            'Tipo',
            'Conta',
            'Categoria',
            'Valor'
        ], ';');

        $totalReceita = 0;
        $totalDespesa = 0;

        foreach ($dados as $row) {
            if ($row['tipo'] === 'R') {
                $totalReceita += $row['valor'];
            } else {
                $totalDespesa += $row['valor'];
            }

            fputcsv($out, [
                date('d/m/Y', strtotime($row['data'])),
                $row['descricao'],
                $row['tipo'] === 'R' ? 'Receita' : 'Despesa',
                $row['conta'],
                $row['categoria'],
                number_format($row['valor'], 2, ',', '.')
            ], ';');
        }

        // Linha em branco
        fputcsv($out, [], ';');

        // Totais
        fputcsv($out, ['TOTAL RECEITAS', '', '', '', '', number_format($totalReceita, 2, ',', '.')], ';');
        fputcsv($out, ['TOTAL DESPESAS', '', '', '', '', number_format($totalDespesa, 2, ',', '.')], ';');
        fputcsv($out, ['SALDO', '', '', '', '', number_format($totalReceita - $totalDespesa, 2, ',', '.')], ';');

        fclose($out);
        exit;
    }
      public static function exportPdf($pdo)
    {
        $idUsuario = usuarioId();
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $mes = (int)($_GET['mes'] ?? date('m'));

        $dados = Lancamento::allByMes($pdo, $idUsuario, $ano, $mes);

        // HTML do PDF
        ob_start();
        require __DIR__ . '/../views/relatorios/pdf.php';
        $html = ob_get_clean();

        $dompdf = new Dompdf([
            'isRemoteEnabled' => true
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream(
            "relatorio_{$mes}_{$ano}.pdf",
            ['Attachment' => true]
        );
        exit;
    }
    public static function exportPdfExecutivo($pdo)
{
    $idUsuario = usuarioId();
    $ano = (int)($_GET['ano'] ?? date('Y'));
    $mes = (int)($_GET['mes'] ?? date('m'));

    // Resumo financeiro
    $resumo = Dashboard::resumoMensal($pdo, $idUsuario, $ano, $mes);

    // Orçamento geral
    $orcamentoGeral = Orcamento::resumoGeralMes($pdo, $idUsuario, $ano, $mes);

    // Estourados
    $estourados = Orcamento::estouradosNoMes($pdo, $idUsuario, $ano, $mes);

    // Gastos por categoria
    $gastosCategoria = Lancamento::totalPorCategoriaMes(
        $pdo,
        $idUsuario,
        $ano,
        $mes
    );

    ob_start();
    require __DIR__ . '/../views/relatorios/pdf_executivo.php';
    $html = ob_get_clean();

    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream(
        "relatorio_executivo_{$mes}_{$ano}.pdf",
        ['Attachment' => true]
    );
    exit;
}

}
