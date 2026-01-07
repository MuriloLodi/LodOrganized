<?php
require_once __DIR__ . '/../models/Dashboard.php';
require_once __DIR__ . '/../models/Conta.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../helpers/helpers.php';

class DashboardController
{
    public static function index($pdo)
    {
        $idUsuario = usuarioId();

        $ano = (int)($_GET['ano'] ?? date('Y'));
        $mes = (int)($_GET['mes'] ?? date('m'));
        $idConta = $_GET['id_conta'] ?? '';
        $idConta = $idConta !== '' ? (int)$idConta : null;

        $contas = Conta::allByUsuario($pdo, $idUsuario);

        $resumo = Dashboard::resumoMes($pdo, $idUsuario, $ano, $mes, $idConta);
        $cardsContas = Dashboard::cardsPorConta($pdo, $idUsuario, $ano, $mes);

        // se filtrou conta, reduz cards para mostrar só ela (dashboard fica menos poluído)
        if (!empty($idConta)) {
            $cardsContas = array_values(array_filter($cardsContas, fn($c) => (int)$c['id'] === (int)$idConta));
        }

        $topCategorias = Dashboard::topCategoriasMes($pdo, $idUsuario, $ano, $mes, $idConta, 6);

        // metas (somente categorias de despesa, mas meta é por categoria mesmo)
        $metas = Dashboard::metasMes($pdo, $idUsuario, $ano, $mes, $idConta);

        // para cadastrar meta (lista de categorias)
        $categoriasDespesa = Categoria::allByUsuario($pdo, $idUsuario);
        $categoriasDespesa = array_values(array_filter($categoriasDespesa, fn($c) => ($c['tipo'] ?? '') === 'D'));

        $view = '../app/views/dashboard_content.php';
        require '../app/views/layout.php';
    }

    public static function salvarMeta($pdo)
    {
        $idUsuario = usuarioId();

        $ano = (int)($_POST['ano'] ?? date('Y'));
        $mes = (int)($_POST['mes'] ?? date('m'));
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        $valor = normalizaValor($_POST['valor_limite'] ?? '0');

        if (!$idCategoria || $valor <= 0) {
            $_SESSION['erro'] = "Informe categoria e valor da meta.";
            header("Location: /financas/public/?url=dashboard&ano={$ano}&mes={$mes}");
            exit;
        }

        Dashboard::salvarMeta($pdo, $idUsuario, $ano, $mes, $idCategoria, $valor);

        header("Location: /financas/public/?url=dashboard&ano={$ano}&mes={$mes}");
        exit;
    }
}
