<?php
require_once __DIR__ . '/../models/Servico.php';

class ServicoController
{
    public static function index($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) { header("Location: /financas/public/?url=login"); exit; }

        $q = trim($_GET['q'] ?? '');
        $servicos = Servico::listar($pdo, $idUsuario, $q);

        $titulo = "Serviços • Comercial";
        $view = __DIR__ . '/../views/servicos/index_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function store($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) { header("Location: /financas/public/?url=login"); exit; }

        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco = str_replace(['.', ','], ['', '.'], trim($_POST['preco'] ?? '0'));
        $duracao = trim($_POST['duracao_min'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if ($nome === '') {
            $_SESSION['erro'] = "Informe o nome do serviço.";
            header("Location: /financas/public/?url=servicos");
            exit;
        }

        try {
            Servico::create($pdo, $idUsuario, [
                'nome' => $nome,
                'descricao' => $descricao,
                'preco' => (float)$preco,
                'duracao_min' => $duracao,
                'ativo' => $ativo
            ]);
            $_SESSION['sucesso'] = "Serviço criado!";
        } catch (Throwable $e) {
            $_SESSION['erro'] = "Erro ao criar serviço: " . $e->getMessage();
        }

        header("Location: /financas/public/?url=servicos");
        exit;
    }

    public static function update($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) { header("Location: /financas/public/?url=login"); exit; }

        $id = (int)($_POST['id'] ?? 0);

        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco = str_replace(['.', ','], ['', '.'], trim($_POST['preco'] ?? '0'));
        $duracao = trim($_POST['duracao_min'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if (!$id || $nome === '') {
            $_SESSION['erro'] = "Dados inválidos para editar serviço.";
            header("Location: /financas/public/?url=servicos");
            exit;
        }

        try {
            Servico::update($pdo, $idUsuario, $id, [
                'nome' => $nome,
                'descricao' => $descricao,
                'preco' => (float)$preco,
                'duracao_min' => $duracao,
                'ativo' => $ativo
            ]);
            $_SESSION['sucesso'] = "Serviço atualizado!";
        } catch (Throwable $e) {
            $_SESSION['erro'] = "Erro ao atualizar serviço: " . $e->getMessage();
        }

        header("Location: /financas/public/?url=servicos");
        exit;
    }

    public static function delete($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) { header("Location: /financas/public/?url=login"); exit; }

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header("Location: /financas/public/?url=servicos"); exit; }

        try {
            Servico::delete($pdo, $idUsuario, $id);
            $_SESSION['sucesso'] = "Serviço removido!";
        } catch (Throwable $e) {
            $_SESSION['erro'] = "Erro ao remover serviço: " . $e->getMessage();
        }

        header("Location: /financas/public/?url=servicos");
        exit;
    }

    public static function toggle($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) { header("Location: /financas/public/?url=login"); exit; }

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header("Location: /financas/public/?url=servicos"); exit; }

        try {
            Servico::toggleAtivo($pdo, $idUsuario, $id);
        } catch (Throwable $e) {
            $_SESSION['erro'] = "Erro ao alterar status: " . $e->getMessage();
        }

        header("Location: /financas/public/?url=servicos");
        exit;
    }
}
