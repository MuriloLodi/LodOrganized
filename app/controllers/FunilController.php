<?php
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/Oportunidade.php';
require_once __DIR__ . '/../models/Cliente.php';

class FunilController
{
    private static function parseMoneyBr($v): string
    {
        $v = trim((string)$v);
        if ($v === '') return '';
        // "1.234,56" -> "1234.56"
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
        return $v;
    }

    public static function index(PDO $pdo)
    {
        $idUsuario = usuarioId();
        $etapas = Oportunidade::etapas();

        $clientes = Cliente::allByUsuario($pdo, $idUsuario); // usa o seu método existente
        $lista = Oportunidade::allByUsuario($pdo, $idUsuario);

        // agrupa por etapa
        $cardsPorEtapa = [];
        foreach ($etapas as $k => $label) $cardsPorEtapa[$k] = [];
        foreach ($lista as $o) {
            $cardsPorEtapa[$o['etapa']][] = $o;
        }

        $titulo = "Funil de Vendas";
        $view = __DIR__ . '/../views/funil/index_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function store(PDO $pdo)
    {
        $idUsuario = usuarioId();

        $titulo = trim($_POST['titulo'] ?? '');
        $etapa  = $_POST['etapa'] ?? 'lead';

        if ($titulo === '') {
            $_SESSION['erro'] = "Informe o título da oportunidade.";
            header("Location: /financas/public/?url=funil");
            exit;
        }

        $data = [
            'id_cliente'    => (int)($_POST['id_cliente'] ?? 0),
            'titulo'        => $titulo,
            'descricao'     => trim($_POST['descricao'] ?? ''),
            'valor'         => self::parseMoneyBr($_POST['valor'] ?? ''),
            'etapa'         => $etapa,
            'data_prevista' => $_POST['data_prevista'] ?? null,
        ];

        Oportunidade::create($pdo, $idUsuario, $data);
        $_SESSION['sucesso'] = "Oportunidade criada!";
        header("Location: /financas/public/?url=funil");
        exit;
    }

    public static function update(PDO $pdo)
    {
        $idUsuario = usuarioId();
        $id = (int)($_POST['id'] ?? 0);

        $titulo = trim($_POST['titulo'] ?? '');
        if (!$id || $titulo === '') {
            $_SESSION['erro'] = "Preencha o título.";
            header("Location: /financas/public/?url=funil");
            exit;
        }

        $data = [
            'id_cliente'    => (int)($_POST['id_cliente'] ?? 0),
            'titulo'        => $titulo,
            'descricao'     => trim($_POST['descricao'] ?? ''),
            'valor'         => self::parseMoneyBr($_POST['valor'] ?? ''),
            'data_prevista' => $_POST['data_prevista'] ?? null,
        ];

        Oportunidade::update($pdo, $idUsuario, $id, $data);
        $_SESSION['sucesso'] = "Oportunidade atualizada!";
        header("Location: /financas/public/?url=funil");
        exit;
    }

    public static function delete(PDO $pdo)
    {
        $idUsuario = usuarioId();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) Oportunidade::delete($pdo, $idUsuario, $id);

        $_SESSION['sucesso'] = "Oportunidade removida!";
        header("Location: /financas/public/?url=funil");
        exit;
    }

    // AJAX: reordenação/arrastar
public static function move(PDO $pdo)
{
    $idUsuario = usuarioId();

    $payload = file_get_contents('php://input');
    $json = json_decode($payload, true);

    if (!$json) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'Payload inválido']);
        exit;
    }

    $etapasValidas = array_keys(Oportunidade::etapas());

    // NOVO: updates = [ {etapa:'lead', ids:[1,2]}, {etapa:'proposta', ids:[3]} ]
    if (!empty($json['updates']) && is_array($json['updates'])) {

        foreach ($json['updates'] as $up) {
            $etapa = $up['etapa'] ?? '';
            $ids   = $up['ids'] ?? [];

            if ($etapa === '' || !in_array($etapa, $etapasValidas, true) || !is_array($ids)) {
                continue;
            }

            Oportunidade::reorder($pdo, $idUsuario, $etapa, $ids);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true]);
        exit;
    }

    // COMPAT: payload antigo { etapa:'x', ids:[...] }
    $etapa = $json['etapa'] ?? '';
    $ids   = $json['ids'] ?? [];

    if ($etapa === '' || !in_array($etapa, $etapasValidas, true) || !is_array($ids)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'Dados inválidos']);
        exit;
    }

    Oportunidade::reorder($pdo, $idUsuario, $etapa, $ids);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true]);
    exit;
}

}
