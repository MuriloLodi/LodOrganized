<?php
require_once __DIR__ . '/../models/Lancamento.php';
require_once __DIR__ . '/../models/Conta.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Dashboard.php';
require_once __DIR__ . '/../helpers/helpers.php';

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

        $lancamentos = Lancamento::filtrar($pdo, $idUsuario, $filtros);

        $contas     = Conta::allByUsuario($pdo, $idUsuario);
        $categorias = Categoria::allByUsuario($pdo, $idUsuario);

        // recorrências ativas (pra exibir e gerar mês)
        $recorrencias = Lancamento::listarRecorrencias($pdo, $idUsuario);

        require '../app/views/lancamentos/index.php';
    }

    public static function store($pdo)
    {
        $idUsuario = usuarioId();

        $tipo = $_POST['tipo'] ?? '';
        $valor = normalizaValor($_POST['valor'] ?? '0');
        $status = ($_POST['status'] ?? 'pago') === 'pendente' ? 'pendente' : 'pago';

        if (!$tipo || !$valor || empty($_POST['data']) || empty($_POST['id_conta']) || empty($_POST['id_categoria'])) {
            $_SESSION['erro'] = "Preencha todos os campos obrigatórios.";
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        try {
            $pdo->beginTransaction();

            Lancamento::create($pdo, [
                'id_usuario'   => $idUsuario,
                'id_conta'     => (int)$_POST['id_conta'],
                'id_categoria' => (int)$_POST['id_categoria'],
                'tipo'         => $tipo,
                'valor'        => $valor,
                'data'         => $_POST['data'],
                'descricao'    => trim($_POST['descricao'] ?? ''),
                'status'       => $status,
                'grupo_uuid'   => uuidv4()
            ]);

            if ($status === 'pago') {
                Conta::movimentar($pdo, (int)$_POST['id_conta'], $idUsuario, $valor, $tipo);
            }

            $pdo->commit();
            header("Location: /financas/public/?url=lancamentos");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['erro'] = "Erro ao salvar lançamento.";
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }
    }

    /**
     * ✅ EDITAR (carrega lançamento + anexos)
     */
    public static function edit($pdo)
    {
        $idUsuario = usuarioId();
        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        $lancamento = Lancamento::findById($pdo, $idUsuario, $id);
        if (!$lancamento) {
            $_SESSION['erro'] = "Lançamento não encontrado.";
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        $contas     = Conta::allByUsuario($pdo, $idUsuario);
        $categorias = Categoria::allByUsuario($pdo, $idUsuario);
        $anexos     = Lancamento::anexos($pdo, $idUsuario, $id);

        require '../app/views/lancamentos/edit.php';
    }

    /**
     * ✅ UPDATE (o mais importante: reverter/aplicar saldo corretamente)
     */
    public static function update($pdo)
    {
        $idUsuario = usuarioId();

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        $novoTipo = $_POST['tipo'] ?? '';
        $novoValor = normalizaValor($_POST['valor'] ?? '0');
        $novoStatus = ($_POST['status'] ?? 'pago') === 'pendente' ? 'pendente' : 'pago';
        $novaData = $_POST['data'] ?? '';
        $novaDesc = trim($_POST['descricao'] ?? '');
        $novaConta = (int)($_POST['id_conta'] ?? 0);
        $novaCat = (int)($_POST['id_categoria'] ?? 0);

        if (!$novoTipo || !$novoValor || !$novaData || !$novaConta) {
            $_SESSION['erro'] = "Preencha os campos obrigatórios.";
            header("Location: /financas/public/?url=lancamentos-edit&id=".$id);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $antigo = Lancamento::findById($pdo, $idUsuario, $id);
            if (!$antigo) {
                $pdo->rollBack();
                $_SESSION['erro'] = "Lançamento não encontrado.";
                header("Location: /financas/public/?url=lancamentos");
                exit;
            }

            // 1) Regras de saldo
            $antigoStatus = $antigo['status'] ?? 'pago';
            $antigoTipo   = $antigo['tipo'];
            $antigoValor  = (float)$antigo['valor'];
            $antigoConta  = (int)$antigo['id_conta'];

            // Se antes estava PAGO:
            // - se agora vai ficar pendente: reverte antigo
            // - se agora continua pago: reverte antigo (se mudou algo que afeta saldo) e aplica novo
            if ($antigoStatus === 'pago') {
                // se vai para pendente -> sempre reverte o antigo
                if ($novoStatus === 'pendente') {
                    Conta::reverterMovimento($pdo, $antigoConta, $idUsuario, $antigoValor, $antigoTipo);
                } else {
                    // continua pago: se mudou conta/tipo/valor, reverte e aplica novo
                    $mudouSaldo = (
                        $antigoConta !== $novaConta ||
                        $antigoTipo  !== $novoTipo ||
                        abs($antigoValor - (float)$novoValor) > 0.0001
                    );

                    if ($mudouSaldo) {
                        Conta::reverterMovimento($pdo, $antigoConta, $idUsuario, $antigoValor, $antigoTipo);
                        Conta::movimentar($pdo, $novaConta, $idUsuario, (float)$novoValor, $novoTipo);
                    }
                }
            } else {
                // Antes estava pendente:
                // - se agora virou pago -> aplica novo
                if ($novoStatus === 'pago') {
                    Conta::movimentar($pdo, $novaConta, $idUsuario, (float)$novoValor, $novoTipo);
                }
            }

            // 2) Atualiza o registro
            Lancamento::updateById($pdo, $idUsuario, $id, [
                'tipo'         => $novoTipo,
                'id_conta'     => $novaConta,
                'id_categoria' => $novaCat ?: null,
                'valor'        => $novoValor,
                'data'         => $novaData,
                'descricao'    => $novaDesc,
                'status'       => $novoStatus
            ]);

            $pdo->commit();

            header("Location: /financas/public/?url=lancamentos");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['erro'] = "Erro ao salvar alterações.";
            header("Location: /financas/public/?url=lancamentos-edit&id=".$id);
            exit;
        }
    }

    /**
     * ✅ DELETE (se estava pago, reverte saldo; remove anexos também)
     */
    public static function delete($pdo)
    {
        $idUsuario = usuarioId();
        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        try {
            $pdo->beginTransaction();

            $l = Lancamento::findById($pdo, $idUsuario, $id);
            if (!$l) {
                $pdo->rollBack();
                $_SESSION['erro'] = "Lançamento não encontrado.";
                header("Location: /financas/public/?url=lancamentos");
                exit;
            }

            // Se pago, reverte saldo
            if (($l['status'] ?? 'pago') === 'pago') {
                Conta::reverterMovimento(
                    $pdo,
                    (int)$l['id_conta'],
                    $idUsuario,
                    (float)$l['valor'],
                    $l['tipo']
                );
            }

            // Apaga anexos (db + arquivo)
            $anexos = Lancamento::anexos($pdo, $idUsuario, $id);
            foreach ($anexos as $a) {
                $path = __DIR__ . '/../../public/uploads/'.$idUsuario.'/'.$a['arquivo'];
                if (is_file($path)) @unlink($path);
                Lancamento::excluirAnexo($pdo, $idUsuario, (int)$a['id']);
            }

            // Apaga lançamento
            Lancamento::deleteById($pdo, $idUsuario, $id);

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['erro'] = "Erro ao excluir lançamento.";
        }

        header("Location: /financas/public/?url=lancamentos");
        exit;
    }

    public static function storeTransferencia($pdo)
    {
        $idUsuario = usuarioId();

        $origem = (int)($_POST['id_conta_origem'] ?? 0);
        $destino = (int)($_POST['id_conta_destino'] ?? 0);
        $valor = normalizaValor($_POST['valor'] ?? '0');
        $data = $_POST['data'] ?? '';
        $desc = trim($_POST['descricao'] ?? '');

        if (!$origem || !$destino || $origem === $destino || !$valor || !$data) {
            $_SESSION['erro'] = "Informe origem, destino, valor e data (origem e destino devem ser diferentes).";
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        $resp = Lancamento::criarTransferencia($pdo, $idUsuario, $origem, $destino, $valor, $data, $desc);
        if (!$resp['ok']) {
            $_SESSION['erro'] = "Erro na transferência.";
        } else {
            // atualiza saldos imediatamente
            try {
                $pdo->beginTransaction();
                Conta::movimentar($pdo, $origem, $idUsuario, $valor, 'D');
                Conta::movimentar($pdo, $destino, $idUsuario, $valor, 'R');
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['erro'] = "Transferência criada, mas falhou ao atualizar saldo.";
            }
        }

        header("Location: /financas/public/?url=lancamentos");
        exit;
    }

    public static function storeParcelas($pdo)
    {
        $idUsuario = usuarioId();

        $tipo = $_POST['tipo'] ?? 'D';
        $idConta = (int)($_POST['id_conta'] ?? 0);
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        $totalParcelas = (int)($_POST['total_parcelas'] ?? 0);
        $valorTotal = normalizaValor($_POST['valor_total'] ?? '0');
        $dataInicio = $_POST['data_inicio'] ?? '';
        $descricao = trim($_POST['descricao'] ?? '');
        $pagarPrimeira = !empty($_POST['pagar_primeira']);

        if (!$idConta || !$totalParcelas || $totalParcelas < 2 || !$valorTotal || !$dataInicio) {
            $_SESSION['erro'] = "Informe conta, valor total, data e total de parcelas (mínimo 2).";
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        $resp = Lancamento::criarParcelamento($pdo, [
            'id_usuario' => $idUsuario,
            'tipo' => $tipo,
            'id_conta' => $idConta,
            'id_categoria' => $idCategoria ?: null,
            'descricao' => $descricao ?: 'Parcelamento',
            'valor_total' => $valorTotal,
            'total_parcelas' => $totalParcelas,
            'data_inicio' => $dataInicio,
            'pagar_primeira' => $pagarPrimeira
        ]);

        if (!$resp['ok']) {
            $_SESSION['erro'] = "Erro ao criar parcelamento.";
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        // se marcou pagar a 1ª parcela, aplica o saldo da 1ª
        if ($pagarPrimeira) {
            $valorParcela = round($valorTotal / $totalParcelas, 2);
            try {
                $pdo->beginTransaction();
                Conta::movimentar($pdo, $idConta, $idUsuario, $valorParcela, $tipo);
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['erro'] = "Parcelamento criado, mas falhou ao atualizar saldo da 1ª parcela.";
            }
        }

        header("Location: /financas/public/?url=lancamentos");
        exit;
    }

    public static function storeRecorrencia($pdo)
    {
        $idUsuario = usuarioId();

        $tipo = $_POST['tipo'] ?? '';
        $idConta = (int)($_POST['id_conta'] ?? 0);
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        $valor = normalizaValor($_POST['valor'] ?? '0');
        $freq = $_POST['frequencia'] ?? 'mensal';
        $desc = trim($_POST['descricao'] ?? '');

        $diaMes = (int)($_POST['dia_mes'] ?? 1);
        $diaSemana = (int)($_POST['dia_semana'] ?? 1);

        if (!$tipo || !$idConta || !$valor || !$freq) {
            $_SESSION['erro'] = "Informe tipo, conta, valor e frequência.";
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        if ($freq === 'mensal') {
            $diaMes = max(1, min($diaMes, 28));
            $diaSemana = null;
        } else {
            $diaSemana = max(1, min($diaSemana, 7));
            $diaMes = null;
        }

        Lancamento::criarRecorrencia($pdo, [
            'id_usuario' => $idUsuario,
            'tipo' => $tipo,
            'id_conta' => $idConta,
            'id_categoria' => $idCategoria ?: null,
            'valor' => $valor,
            'descricao' => $desc ?: 'Recorrente',
            'frequencia' => $freq,
            'dia_mes' => $diaMes,
            'dia_semana' => $diaSemana
        ]);

        header("Location: /financas/public/?url=lancamentos");
        exit;
    }

    public static function gerarRecorrenciasMes($pdo)
    {
        $idUsuario = usuarioId();
        $ano = (int)($_POST['ano'] ?? date('Y'));
        $mes = (int)($_POST['mes'] ?? date('m'));
        $marcarPago = !empty($_POST['marcar_pago']);

        $resp = Lancamento::gerarRecorrenciasDoMes($pdo, $idUsuario, $ano, $mes, $marcarPago);

        if (!$resp['ok']) {
            $_SESSION['erro'] = "Falha ao gerar recorrentes.";
        } else {
            if ($marcarPago && $resp['gerados'] > 0) {
                try {
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("
                        SELECT id_conta, tipo, SUM(valor) AS total
                        FROM lancamentos
                        WHERE id_usuario = ? AND recorrencia_id IS NOT NULL
                          AND YEAR(data) = ? AND MONTH(data) = ?
                          AND status = 'pago'
                          AND criado_em >= (NOW() - INTERVAL 10 MINUTE)
                        GROUP BY id_conta, tipo
                    ");
                    $stmt->execute([$idUsuario, $ano, $mes]);
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $r) {
                        Conta::movimentar($pdo, (int)$r['id_conta'], $idUsuario, (float)$r['total'], $r['tipo']);
                    }

                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['erro'] = "Recorrentes gerados, mas falhou ao atualizar saldo.";
                }
            }
        }

        header("Location: /financas/public/?url=lancamentos");
        exit;
    }

    public static function toggleStatus($pdo)
    {
        $idUsuario = usuarioId();
        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            header("Location: /financas/public/?url=lancamentos");
            exit;
        }

        try {
            $pdo->beginTransaction();

            $resp = Lancamento::togglePago($pdo, $id, $idUsuario);
            if (!$resp['ok']) {
                $pdo->rollBack();
                header("Location: /financas/public/?url=lancamentos");
                exit;
            }

            $l = $resp['lancamento'];
            $novo = $resp['novo'];

            if ($novo === 'pago') {
                Conta::movimentar($pdo, (int)$l['id_conta'], $idUsuario, (float)$l['valor'], $l['tipo']);
            } else {
                Conta::reverterMovimento($pdo, (int)$l['id_conta'], $idUsuario, (float)$l['valor'], $l['tipo']);
            }

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['erro'] = "Erro ao alterar status.";
        }

        header("Location: /financas/public/?url=lancamentos");
        exit;
    }

    public static function uploadAnexo($pdo)
    {
        $idUsuario = usuarioId();
        $idLanc = (int)($_POST['id_lancamento'] ?? 0);

        if (!$idLanc || empty($_FILES['arquivo']['name'])) {
            $_SESSION['erro'] = "Selecione um arquivo.";
            header("Location: /financas/public/?url=lancamentos-edit&id=".$idLanc);
            exit;
        }

        $file = $_FILES['arquivo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['erro'] = "Erro no upload.";
            header("Location: /financas/public/?url=lancamentos-edit&id=".$idLanc);
            exit;
        }

        $permitidos = ['image/jpeg','image/png','application/pdf'];
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $permitidos)) {
            $_SESSION['erro'] = "Arquivo inválido (aceito: JPG, PNG, PDF).";
            header("Location: /financas/public/?url=lancamentos-edit&id=".$idLanc);
            exit;
        }

        $max = 5 * 1024 * 1024;
        if ($file['size'] > $max) {
            $_SESSION['erro'] = "Arquivo muito grande (máx 5MB).";
            header("Location: /financas/public/?url=lancamentos-edit&id=".$idLanc);
            exit;
        }

        $dir = __DIR__ . '/../../public/uploads/'.$idUsuario;
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nomeFinal = 'lanc_'.$idLanc.'_'.time().'.'.$ext;
        $dest = $dir.'/'.$nomeFinal;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $_SESSION['erro'] = "Não foi possível salvar o arquivo.";
            header("Location: /financas/public/?url=lancamentos-edit&id=".$idLanc);
            exit;
        }

        Lancamento::salvarAnexo($pdo, $idUsuario, $idLanc, $nomeFinal, $mime, (int)$file['size']);

        header("Location: /financas/public/?url=lancamentos-edit&id=".$idLanc);
        exit;
    }

    public static function deleteAnexo($pdo)
    {
        $idUsuario = usuarioId();
        $idAnexo = (int)($_GET['id'] ?? 0);
        $idLanc = (int)($_GET['l'] ?? 0);

        $a = Lancamento::excluirAnexo($pdo, $idUsuario, $idAnexo);
        if ($a) {
            $path = __DIR__ . '/../../public/uploads/'.$idUsuario.'/'.$a['arquivo'];
            if (is_file($path)) @unlink($path);
        }

        header("Location: /financas/public/?url=lancamentos-edit&id=".$idLanc);
        exit;
    }
}
