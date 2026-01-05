<?php
require_once __DIR__ . '/../models/Lancamento.php';
require_once __DIR__ . '/../models/Conta.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Dashboard.php';


class LancamentoController
{
    public static function index($pdo)
{
    // ✅ PRIMEIRA COISA: definir usuário
    $idUsuario = $_SESSION['usuario']['id'];

    // 🔎 Filtros
    $filtros = [
        'data_inicio'  => $_GET['data_inicio'] ?? '',
        'data_fim'     => $_GET['data_fim'] ?? '',
        'id_conta'     => $_GET['id_conta'] ?? '',
        'id_categoria' => $_GET['id_categoria'] ?? ''
    ];

    // 📄 Lançamentos (tabela)
    $lancamentos = Lancamento::filtrar($pdo, $idUsuario, $filtros);

    // 📊 Gráficos
    $resumoTipo = Dashboard::resumoPorTipo($pdo, $idUsuario, $filtros);
    $resumoCategoria = Dashboard::resumoPorCategoria($pdo, $idUsuario, $filtros);

    // 📈 Gráfico mensal (linha)
    $anoAtual = date('Y');
    $resumoLinha = Dashboard::resumoMensalLinha(
        $pdo,
        $idUsuario,
        $anoAtual
    );

    // 🔽 Combos
    $contas     = Conta::allByUsuario($pdo, $idUsuario);
    $categorias = Categoria::allByUsuario($pdo, $idUsuario);

    require '../app/views/lancamentos/index.php';
}




    public static function store($pdo)
{
    $dados = [
        'id_usuario'   => $_SESSION['usuario']['id'],
        'id_conta'     => $_POST['id_conta'] ?? '',
        'id_categoria' => $_POST['id_categoria'] ?? '',
        'tipo'         => $_POST['tipo'] ?? '',
        'valor'        => str_replace(',', '.', $_POST['valor'] ?? '0'),
        'data'         => $_POST['data'] ?? '',
        'descricao'    => trim($_POST['descricao'] ?? '')
    ];

    if (
        !$dados['id_conta'] ||
        !$dados['id_categoria'] ||
        !$dados['tipo'] ||
        !$dados['valor'] ||
        !$dados['data']
    ) {
        $_SESSION['erro'] = "Preencha todos os campos obrigatórios.";
        header("Location: /financas/public/?url=lancamentos");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1️⃣ Cria lançamento
        Lancamento::create($pdo, $dados);

        // 2️⃣ Atualiza saldo da conta
        Conta::atualizaSaldo(
            $pdo,
            $dados['id_conta'],
            $dados['valor'],
            $dados['tipo']
        );

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['erro'] = "Erro ao salvar lançamento.";
    }

    header("Location: /financas/public/?url=lancamentos");
    exit;
}
public static function edit($pdo)
{
    $id = $_GET['id'] ?? null;
    $idUsuario = $_SESSION['usuario']['id'];

    $lancamento = Lancamento::find($pdo, $id, $idUsuario);

    if (!$lancamento) {
        header("Location: /financas/public/?url=lancamentos");
        exit;
    }

    $contas     = Conta::allByUsuario($pdo, $idUsuario);
    $categorias = Categoria::allByUsuario($pdo, $idUsuario);

    require '../app/views/lancamentos/edit.php';
}
public static function update($pdo)
{
    $id = $_POST['id'];
    $idUsuario = $_SESSION['usuario']['id'];

    $novo = [
        'id_conta'     => $_POST['id_conta'],
        'id_categoria' => $_POST['id_categoria'],
        'tipo'         => $_POST['tipo'],
        'valor'        => str_replace(',', '.', $_POST['valor']),
        'data'         => $_POST['data'],
        'descricao'    => $_POST['descricao']
    ];

    $antigo = Lancamento::find($pdo, $id, $idUsuario);

    try {
        $pdo->beginTransaction();

        // 1️⃣ Reverte saldo antigo
        Conta::reverterSaldo(
            $pdo,
            $antigo['id_conta'],
            $antigo['valor'],
            $antigo['tipo']
        );

        // 2️⃣ Atualiza lançamento
        Lancamento::update($pdo, $id, $novo);

        // 3️⃣ Aplica novo saldo
        Conta::atualizaSaldo(
            $pdo,
            $novo['id_conta'],
            $novo['valor'],
            $novo['tipo']
        );

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['erro'] = "Erro ao atualizar lançamento.";
    }

    header("Location: /financas/public/?url=lancamentos");
    exit;
}
public static function delete($pdo)
{
    $id = $_GET['id'];
    $idUsuario = $_SESSION['usuario']['id'];

    $lancamento = Lancamento::find($pdo, $id, $idUsuario);

    if (!$lancamento) {
        header("Location: /financas/public/?url=lancamentos");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1️⃣ Reverte saldo
        Conta::reverterSaldo(
            $pdo,
            $lancamento['id_conta'],
            $lancamento['valor'],
            $lancamento['tipo']
        );

        // 2️⃣ Exclui lançamento
        Lancamento::delete($pdo, $id);

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['erro'] = "Erro ao excluir lançamento.";
    }

    header("Location: /financas/public/?url=lancamentos");
    exit;
}


}
