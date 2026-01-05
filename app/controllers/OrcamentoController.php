<?php
require_once __DIR__ . '/../models/Orcamento.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Lancamento.php';

class OrcamentoController
{
    public static function index($pdo)
    {
        $idUsuario = usuarioId();

        // SEM BUG: força int
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $mes = (int)($_GET['mes'] ?? date('m'));

        // Categorias do usuário
        $categorias = Categoria::allByUsuario($pdo, $idUsuario);

        // Só despesas entram no orçamento
        $categoriasDespesa = array_values(array_filter($categorias, function ($c) {
            return ($c['tipo'] ?? '') === 'D';
        }));

        // Orçamentos do mês em MAPA: [id_categoria => valor]
        $orcamentosMap = Orcamento::mapByMes($pdo, $idUsuario, $ano, $mes);

        // Gastos reais do mês por categoria (seu método)
        $gastosReais = Lancamento::totalPorCategoriaMes($pdo, $idUsuario, $ano, $mes);

        require '../app/views/orcamentos/index.php';
    }

    public static function store($pdo)
    {
        $idUsuario   = usuarioId();
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        $ano         = (int)($_POST['ano'] ?? date('Y'));
        $mes         = (int)($_POST['mes'] ?? date('m'));
        $valor       = (float) normalizaValor($_POST['valor'] ?? '0');

        if ($idCategoria <= 0 || $ano <= 0 || $mes < 1 || $mes > 12) {
            $_SESSION['erro'] = "Dados inválidos para salvar orçamento.";
            header("Location: /financas/public/?url=orcamentos&ano={$ano}&mes={$mes}");
            exit;
        }

        Orcamento::save($pdo, $idUsuario, $idCategoria, $ano, $mes, $valor);

        header("Location: /financas/public/?url=orcamentos&ano={$ano}&mes={$mes}");
        exit;
    }
       public static function resumoGeralMes(PDO $pdo, int $idUsuario, int $ano, int $mes): array
    {
        // Total orçado (somatório dos orçamentos)
        $st1 = $pdo->prepare("
            SELECT IFNULL(SUM(valor), 0) AS total_orcado
            FROM orcamentos
            WHERE id_usuario = ?
              AND ano = ?
              AND mes = ?
        ");
        $st1->execute([$idUsuario, $ano, $mes]);
        $orcado = (float)$st1->fetchColumn();

        // Total real (somatório das despesas)
        $st2 = $pdo->prepare("
            SELECT IFNULL(SUM(valor), 0) AS total_real
            FROM lancamentos
            WHERE id_usuario = ?
              AND tipo = 'D'
              AND YEAR(data) = ?
              AND MONTH(data) = ?
        ");
        $st2->execute([$idUsuario, $ano, $mes]);
        $real = (float)$st2->fetchColumn();

        $percentual = ($orcado > 0) ? ($real / $orcado) * 100 : 0;

        return [
            'orcado'     => $orcado,
            'real'       => $real,
            'percentual' => $percentual
        ];
    }
}
