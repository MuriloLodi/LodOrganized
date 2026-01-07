<?php
require_once __DIR__ . '/../models/Relatorio.php';
require_once __DIR__ . '/../models/Conta.php';
require_once __DIR__ . '/../models/Categoria.php';

class RelatorioController
{
    private static function getFiltrosFromRequest(): array
    {
        // padrões: mês atual
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $mes = (int)($_GET['mes'] ?? date('m'));

        $dataInicio = $_GET['data_inicio'] ?? '';
        $dataFim    = $_GET['data_fim'] ?? '';

        // se usuário não informou data manual, usa mês/ano
        if ($dataInicio === '' && $dataFim === '') {
            $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
            $ultimoDia  = date('t', strtotime($dataInicio));
            $dataFim    = sprintf('%04d-%02d-%02d', $ano, $mes, $ultimoDia);
        }

        return [
            'ano'         => $ano,
            'mes'         => $mes,
            'data_inicio' => $dataInicio,
            'data_fim'    => $dataFim,
            'id_conta'    => $_GET['id_conta'] ?? '',
            'id_categoria'=> $_GET['id_categoria'] ?? '',
            'tipo'        => $_GET['tipo'] ?? '',
            'status'      => $_GET['status'] ?? '',
            'q'           => trim($_GET['q'] ?? ''), // busca no front
        ];
    }

    public static function index(PDO $pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        $f = self::getFiltrosFromRequest();

        $contas = Conta::allByUsuario($pdo, $idUsuario);
        $categorias = Categoria::allByUsuario($pdo, $idUsuario);

        $lancamentos = Relatorio::filtrarLancamentos($pdo, $idUsuario, $f);
        $resumo = Relatorio::resumo($pdo, $idUsuario, $f);
        $porCategoria = Relatorio::porCategoria($pdo, $idUsuario, $f);
        $porConta = Relatorio::porConta($pdo, $idUsuario, $f);

        $titulo = "Relatórios";
        $view = __DIR__ . '/../views/relatorios/index_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function exportCsv(PDO $pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        $f = self::getFiltrosFromRequest();
        $rows = Relatorio::filtrarLancamentos($pdo, $idUsuario, $f);

        $nome = sprintf("relatorio_%04d-%02d.csv", (int)$f['ano'], (int)$f['mes']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$nome.'"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Data', 'Tipo', 'Status', 'Conta', 'Categoria', 'Descrição', 'Valor'], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                date('d/m/Y', strtotime($r['data'])),
                $r['tipo'] === 'R' ? 'Receita' : 'Despesa',
                $r['status'] ?? 'pago',
                $r['conta'] ?? '',
                $r['categoria'] ?? '',
                $r['descricao'] ?? '',
                number_format((float)$r['valor'], 2, ',', '.'),
            ], ';');
        }

        fclose($out);
        exit;
    }

    private static function dompdfAvailable(): bool
    {
        // ajuste aqui se seu dompdf está em outro lugar
        $autoload1 = __DIR__ . '/../libs/dompdf/autoload.inc.php';
        $autoload2 = __DIR__ . '/../libs/dompdf/vendor/autoload.php';

        return file_exists($autoload1) || file_exists($autoload2);
    }

    private static function includeDompdf(): void
    {
        $autoload1 = __DIR__ . '/../libs/dompdf/autoload.inc.php';
        $autoload2 = __DIR__ . '/../libs/dompdf/vendor/autoload.php';

        if (file_exists($autoload1)) {
            require_once $autoload1;
            return;
        }
        if (file_exists($autoload2)) {
            require_once $autoload2;
            return;
        }
    }

    public static function exportPdf(PDO $pdo)
    {
        if (!self::dompdfAvailable()) {
            $_SESSION['erro'] = "Dompdf não encontrado no projeto. Envie a pasta do dompdf junto.";
            header("Location: /financas/public/?url=relatorios");
            exit;
        }

        self::includeDompdf();
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        $f = self::getFiltrosFromRequest();

        $rows = Relatorio::filtrarLancamentos($pdo, $idUsuario, $f);
        $resumo = Relatorio::resumo($pdo, $idUsuario, $f);

        ob_start();
        require __DIR__ . '/../views/relatorios/pdf_simples.php';
        $html = ob_get_clean();

        $dompdf = new Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nome = sprintf("relatorio_simples_%04d-%02d.pdf", (int)$f['ano'], (int)$f['mes']);
        $dompdf->stream($nome, ["Attachment" => true]);
        exit;
    }

    public static function exportPdfExecutivo(PDO $pdo)
    {
        if (!self::dompdfAvailable()) {
            $_SESSION['erro'] = "Dompdf não encontrado no projeto. Envie a pasta do dompdf junto.";
            header("Location: /financas/public/?url=relatorios");
            exit;
        }

        self::includeDompdf();
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        $f = self::getFiltrosFromRequest();

        $rows = Relatorio::filtrarLancamentos($pdo, $idUsuario, $f);
        $resumo = Relatorio::resumo($pdo, $idUsuario, $f);
        $porCategoria = Relatorio::porCategoria($pdo, $idUsuario, $f);
        $porConta = Relatorio::porConta($pdo, $idUsuario, $f);

        ob_start();
        require __DIR__ . '/../views/relatorios/pdf_executivo.php';
        $html = ob_get_clean();

        $dompdf = new Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nome = sprintf("relatorio_executivo_%04d-%02d.pdf", (int)$f['ano'], (int)$f['mes']);
        $dompdf->stream($nome, ["Attachment" => true]);
        exit;
    }
}
