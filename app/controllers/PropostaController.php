<?php
require_once __DIR__ . '/../models/Proposta.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/Oportunidade.php';


class PropostaController
{
    public static function index($pdo)
    {
        $idUsuario = usuarioId();
        $propostas = Proposta::allByUsuario($pdo, $idUsuario);

        $titulo = "Orçamentos / Propostas";
        $view = __DIR__ . '/../views/propostas/index_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function createForm($pdo)
    {
        $idUsuario = usuarioId();
        $opId = (int) ($_GET['op'] ?? 0);
        $oportunidade_id = 0;


        // ✅ lista clientes do usuário (para dropdown)
        $clientes = Cliente::allByUsuario($pdo, $idUsuario);

        $proposta = [
            'id' => null,
            'id_cliente' => null,
            'numero' => Proposta::gerarNumero($pdo, $idUsuario),
            'status' => 'rascunho',
            'data_emissao' => date('Y-m-d'),
            'validade_dias' => 15,
            'cliente_nome' => '',
            'cliente_email' => '',
            'cliente_telefone' => '',
            'cliente_endereco' => '',
            'forma_pagamento' => '',
            'observacoes' => "Prazo de entrega: 30 dias após aprovação\nValidade do orçamento: 15 dias",
            'consideracoes' => "Prezado(a),\nObrigado por escolher nossos serviços."
        ];

        $itens = [
            ['descricao' => '', 'quantidade' => '1,00', 'valor_unit' => '0,00']
        ];

        if ($opId > 0) {
            $op = Oportunidade::findById($pdo, $idUsuario, $opId);
            if ($op) {
                $oportunidade_id = (int) $op['id'];

                // Se tiver cliente vinculado, puxa dados do cliente
                if (!empty($op['id_cliente'])) {
                    $cl = Cliente::findById($pdo, (int) $op['id_cliente'], $idUsuario);
                    if ($cl) {
                        $proposta['cliente_nome'] = $cl['nome'] ?? '';
                        $proposta['cliente_email'] = $cl['email'] ?? '';
                        $proposta['cliente_telefone'] = $cl['telefone'] ?? '';
                        $proposta['cliente_endereco'] = $cl['endereco'] ?? '';
                    }
                }

                // Item padrão vira o título da oportunidade
                $valorUnit = (float) ($op['valor'] ?? 0);
                $itens = [
                    [
                        'descricao' => $op['titulo'] ?? '',
                        'quantidade' => '1,00',
                        'valor_unit' => number_format($valorUnit, 2, ',', '.')
                    ]
                ];

                // Se tiver descrição, joga nas observações
                if (!empty($op['descricao'])) {
                    $proposta['observacoes'] = trim($op['descricao']) . "\n\n" . ($proposta['observacoes'] ?? '');
                }
            }
        }

        $titulo = "Nova Proposta";
        $view = __DIR__ . '/../views/propostas/form_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function store($pdo)
    {
        $idUsuario = usuarioId();

        $idCliente = (int) ($_POST['id_cliente'] ?? 0);

        $data = [
            'id_usuario' => $idUsuario,
            'id_cliente' => $idCliente ?: null,

            'numero' => trim($_POST['numero'] ?? ''),
            'status' => $_POST['status'] ?? 'rascunho',
            'data_emissao' => $_POST['data_emissao'] ?? date('Y-m-d'),
            'validade_dias' => (int) ($_POST['validade_dias'] ?? 15),

            // snapshot (manual ou vindo do cliente)
            'cliente_nome' => trim($_POST['cliente_nome'] ?? ''),
            'cliente_email' => trim($_POST['cliente_email'] ?? ''),
            'cliente_telefone' => trim($_POST['cliente_telefone'] ?? ''),
            'cliente_endereco' => trim($_POST['cliente_endereco'] ?? ''),

            'forma_pagamento' => trim($_POST['forma_pagamento'] ?? ''),
            'observacoes' => trim($_POST['observacoes'] ?? ''),
            'consideracoes' => trim($_POST['consideracoes'] ?? '')
        ];

        // ✅ se selecionou cliente existente, puxa do banco e sobrescreve o snapshot
        if ($idCliente > 0) {
            $cli = Cliente::findById($pdo, $idCliente, $idUsuario);
            if ($cli) {
                $data['cliente_nome'] = trim((string) ($cli['nome'] ?? $data['cliente_nome']));
                $data['cliente_email'] = trim((string) ($cli['email'] ?? $data['cliente_email']));
                $data['cliente_telefone'] = trim((string) ($cli['telefone'] ?? $data['cliente_telefone']));
                $data['cliente_endereco'] = trim((string) ($cli['endereco'] ?? $data['cliente_endereco']));
            } else {
                // segurança: id de outro usuário
                $data['id_cliente'] = null;
            }
        }

        $itens = $_POST['itens'] ?? [];

        if ($data['numero'] === '')
            $data['numero'] = Proposta::gerarNumero($pdo, $idUsuario);

        if ($data['cliente_nome'] === '') {
            $_SESSION['erro'] = "Informe o nome do cliente (ou selecione um cliente existente).";
            header("Location: /financas/public/?url=propostas-new");
            exit;
        }
        $oportunidade_id = (int) ($_POST['oportunidade_id'] ?? 0);

        try {
            $id = Proposta::create($pdo, $data, $itens);
            if ($oportunidade_id > 0) {
    Oportunidade::setEtapa($pdo, $idUsuario, $oportunidade_id, 'proposta');
}

            header("Location: /financas/public/?url=propostas-edit&id=" . $id);
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            $_SESSION['erro'] = "Erro ao criar proposta: " . $e->getMessage();
            header("Location: /financas/public/?url=propostas-new");
            exit;
        }
    }

    public static function edit($pdo)
    {
        $idUsuario = usuarioId();
        $id = (int) ($_GET['id'] ?? 0);

        // ✅ lista clientes do usuário (para dropdown)
        $clientes = Cliente::allByUsuario($pdo, $idUsuario);

        $proposta = Proposta::findById($pdo, $idUsuario, $id);
        if (!$proposta) {
            $_SESSION['erro'] = "Proposta não encontrada.";
            header("Location: /financas/public/?url=propostas");
            exit;
        }

        $itens = Proposta::itens($pdo, $id);

        $titulo = "Editar Proposta";
        $view = __DIR__ . '/../views/propostas/form_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function update($pdo)
    {
        $idUsuario = usuarioId();
        $id = (int) ($_POST['id'] ?? 0);

        $idCliente = (int) ($_POST['id_cliente'] ?? 0);

        $data = [
            'id_cliente' => $idCliente ?: null,

            'status' => $_POST['status'] ?? 'rascunho',
            'data_emissao' => $_POST['data_emissao'] ?? date('Y-m-d'),
            'validade_dias' => (int) ($_POST['validade_dias'] ?? 15),

            'cliente_nome' => trim($_POST['cliente_nome'] ?? ''),
            'cliente_email' => trim($_POST['cliente_email'] ?? ''),
            'cliente_telefone' => trim($_POST['cliente_telefone'] ?? ''),
            'cliente_endereco' => trim($_POST['cliente_endereco'] ?? ''),

            'forma_pagamento' => trim($_POST['forma_pagamento'] ?? ''),
            'observacoes' => trim($_POST['observacoes'] ?? ''),
            'consideracoes' => trim($_POST['consideracoes'] ?? '')
        ];

        // ✅ se selecionou cliente existente, sobrescreve snapshot
        if ($idCliente > 0) {
            $cli = Cliente::findById($pdo, $idCliente, $idUsuario);
            if ($cli) {
                $data['cliente_nome'] = trim((string) ($cli['nome'] ?? $data['cliente_nome']));
                $data['cliente_email'] = trim((string) ($cli['email'] ?? $data['cliente_email']));
                $data['cliente_telefone'] = trim((string) ($cli['telefone'] ?? $data['cliente_telefone']));
                $data['cliente_endereco'] = trim((string) ($cli['endereco'] ?? $data['cliente_endereco']));
            } else {
                $data['id_cliente'] = null;
            }
        }

        $itens = $_POST['itens'] ?? [];

        if (!$id || $data['cliente_nome'] === '') {
            $_SESSION['erro'] = "Preencha o cliente (ou selecione um cliente existente).";
            header("Location: /financas/public/?url=propostas");
            exit;
        }

        try {
            Proposta::update($pdo, $idUsuario, $id, $data, $itens);
            header("Location: /financas/public/?url=propostas-edit&id=" . $id);
            exit;
        } catch (Exception $e) {
            $_SESSION['erro'] = "Erro ao salvar proposta.";
            header("Location: /financas/public/?url=propostas-edit&id=" . $id);
            exit;
        }
    }

    public static function delete($pdo)
    {
        $idUsuario = usuarioId();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id)
            Proposta::delete($pdo, $idUsuario, $id);

        header("Location: /financas/public/?url=propostas");
        exit;
    }

    public static function pdf($pdo)
    {
        $idUsuario = usuarioId();
        $id = (int) ($_GET['id'] ?? 0);

        $proposta = Proposta::findById($pdo, $idUsuario, $id);
        if (!$proposta) {
            $_SESSION['erro'] = "Proposta não encontrada.";
            header("Location: /financas/public/?url=propostas");
            exit;
        }

        $itens = Proposta::itens($pdo, $id);

        $autoload1 = __DIR__ . '/../libs/dompdf/vendor/autoload.php';
        $autoload2 = __DIR__ . '/../../vendor/autoload.php';

        if (is_file($autoload2))
            require_once $autoload2;
        elseif (is_file($autoload1))
            require_once $autoload1;
        else {
            $_SESSION['erro'] = "Dompdf não encontrado. Suba a pasta vendor do dompdf (composer).";
            header("Location: /financas/public/?url=propostas-edit&id=" . $id);
            exit;
        }

        $u = $_SESSION['usuario'] ?? [];

        ob_start();
        require __DIR__ . '/../views/propostas/pdf_template.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nome = 'proposta_' . ($proposta['numero'] ?? $id) . '.pdf';
        $dompdf->stream($nome, ["Attachment" => true]);
        exit;
    }
}
