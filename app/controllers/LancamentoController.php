<?php
require_once __DIR__ . '/../models/Lancamento.php';
require_once __DIR__ . '/../models/Conta.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Dashboard.php';


class LancamentoController
{
    public static function index($pdo)
    {
        $idUsuario = usuarioId();

        $filtros = [
            'data_inicio'  => $_GET['data_inicio'] ?? '',
            'data_fim'     => $_GET['data_fim'] ?? '',
            'id_conta'     => $_GET['id_conta'] ?? '',
            'id_categoria' => $_GET['id_categoria'] ?? ''
        ];

        $lancamentos     = Lancamento::filtrar($pdo, $idUsuario, $filtros);
        $resumoTipo      = Dashboard::resumoPorTipo($pdo, $idUsuario, $filtros);
        $resumoCategoria = Dashboard::resumoPorCategoria($pdo, $idUsuario, $filtros);
        $resumoLinha     = Dashboard::resumoMensalLinha($pdo, $idUsuario, date('Y'));

        $contas     = Conta::allByUsuario($pdo, $idUsuario);
        $categorias = Categoria::allByUsuario($pdo, $idUsuario);

        require '../app/views/lancamentos/index.php';
    }

    public static function store($pdo)
    {
        $idUsuario = usuarioId();

        try {
            $pdo->beginTransaction();

            $valor = normalizaValor($_POST['valor']);

            Lancamento::create($pdo, [
                'id_usuario'   => $idUsuario,
                'id_conta'     => $_POST['id_conta'],
                'id_categoria' => $_POST['id_categoria'],
                'tipo'         => $_POST['tipo'],
                'valor'        => $valor,
                'data'         => $_POST['data'],
                'descricao'    => $_POST['descricao']
            ]);

            Conta::atualizaSaldo(
                $pdo,
                $_POST['id_conta'],
                $valor,
                $_POST['tipo'],
                $idUsuario
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
        $idUsuario = usuarioId();
        $id = $_POST['id'];

        $novo = [
            'id_conta'     => $_POST['id_conta'],
            'id_categoria' => $_POST['id_categoria'],
            'tipo'         => $_POST['tipo'],
            'valor'        => normalizaValor($_POST['valor']),
            'data'         => $_POST['data'],
            'descricao'    => $_POST['descricao']
        ];

        $antigo = Lancamento::find($pdo, $id, $idUsuario);

        try {
            $pdo->beginTransaction();

            Conta::reverterSaldo(
                $pdo,
                $antigo['id_conta'],
                $antigo['valor'],
                $antigo['tipo'],
                $idUsuario
            );

            Lancamento::update($pdo, $id, $idUsuario, $novo);

            Conta::atualizaSaldo(
                $pdo,
                $novo['id_conta'],
                $novo['valor'],
                $novo['tipo'],
                $idUsuario
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
        $idUsuario = usuarioId();
        $id = $_GET['id'];

        $lancamento = Lancamento::find($pdo, $id, $idUsuario);
        if (!$lancamento) {
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        try {
            $pdo->beginTransaction();

            Conta::reverterSaldo(
                $pdo,
                $lancamento['id_conta'],
                $lancamento['valor'],
                $lancamento['tipo'],
                $idUsuario
            );

            Lancamento::delete($pdo, $id, $idUsuario);

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['erro'] = "Erro ao excluir lançamento.";
        }

        header("Location: /financas/public/?url=lancamentos");
        exit;
    }


}
