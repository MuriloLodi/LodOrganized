<?php
require_once __DIR__ . '/../models/Proposta.php';

class PropostaController
{
    public static function index($pdo)
    {
        $idUsuario = $_SESSION['usuario']['id'];
        $propostas = Proposta::listar($pdo, $idUsuario);
        require '../app/views/propostas/index.php';
    }

    public static function create($pdo)
    {
        $ano = (int)date('Y');
        $idUsuario = $_SESSION['usuario']['id'];
        $numero = Proposta::proximoNumero($pdo, $idUsuario, $ano);

        // view simples (form)
        $proposta = [
            'id' => null,
            'id_cliente' => '',
            'numero' => $numero,
            'ano' => $ano,
            'status' => 'rascunho',
            'data_emissao' => date('Y-m-d'),
            'validade_dias' => 7,
            'desconto' => 0,
            'observacoes' => ''
        ];
        $itens = [];
        require '../app/views/propostas/form.php';
    }

    public static function store($pdo)
    {
        $idUsuario = $_SESSION['usuario']['id'];
        $ano = (int)date('Y');

        $numero = Proposta::proximoNumero($pdo, $idUsuario, $ano);

        $dados = [
            'id_usuario' => $idUsuario,
            'id_cliente' => $_POST['id_cliente'] ?? '',
            'numero' => $numero,
            'ano' => $ano,
            'data_emissao' => $_POST['data_emissao'] ?? date('Y-m-d'),
            'validade_dias' => (int)($_POST['validade_dias'] ?? 7),
            'desconto' => (float)str_replace(',', '.', ($_POST['desconto'] ?? '0')),
            'observacoes' => trim($_POST['observacoes'] ?? '')
        ];

        try {
            $pdo->beginTransaction();
            $idProposta = Proposta::criar($pdo, $dados);

            // itens via arrays: itens_desc[], itens_qtd[], itens_valor[]
            $desc = $_POST['itens_desc'] ?? [];
            $qtd  = $_POST['itens_qtd'] ?? [];
            $val  = $_POST['itens_valor'] ?? [];

            for ($i=0; $i<count($desc); $i++) {
                $d = trim($desc[$i] ?? '');
                if ($d === '') continue;

                Proposta::adicionarItem($pdo, $idProposta, [
                    'id_servico' => null,
                    'descricao' => $d,
                    'qtd' => (float)str_replace(',', '.', ($qtd[$i] ?? '1')),
                    'valor_unit' => (float)str_replace(',', '.', ($val[$i] ?? '0'))
                ]);
            }

            $pdo->commit();
            header("Location: /financas/public/?url=propostas-edit&id=".$idProposta);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['erro'] = "Erro ao criar proposta.";
            header("Location: /financas/public/?url=propostas-new");
            exit;
        }
    }

    public static function edit($pdo)
    {
        $idUsuario = $_SESSION['usuario']['id'];
        $id = (int)($_GET['id'] ?? 0);

        $proposta = Proposta::buscar($pdo, $idUsuario, $id);
        if (!$proposta) {
            header("Location: /financas/public/?url=propostas");
            exit;
        }

        $itens = Proposta::itens($pdo, $id);
        require '../app/views/propostas/form.php';
    }

    public static function update($pdo)
    {
        $idUsuario = $_SESSION['usuario']['id'];
        $id = (int)($_POST['id'] ?? 0);

        $proposta = Proposta::buscar($pdo, $idUsuario, $id);
        if (!$proposta) {
            header("Location: /financas/public/?url=propostas");
            exit;
        }

        $dados = [
            'id_cliente' => $_POST['id_cliente'] ?? '',
            'data_emissao' => $_POST['data_emissao'] ?? date('Y-m-d'),
            'validade_dias' => (int)($_POST['validade_dias'] ?? 7),
            'desconto' => (float)str_replace(',', '.', ($_POST['desconto'] ?? '0')),
            'observacoes' => trim($_POST['observacoes'] ?? '')
        ];

        try {
            $pdo->beginTransaction();
            Proposta::atualizar($pdo, $idUsuario, $id, $dados);

            Proposta::limparItens($pdo, $id);

            $desc = $_POST['itens_desc'] ?? [];
            $qtd  = $_POST['itens_qtd'] ?? [];
            $val  = $_POST['itens_valor'] ?? [];

            for ($i=0; $i<count($desc); $i++) {
                $d = trim($desc[$i] ?? '');
                if ($d === '') continue;

                Proposta::adicionarItem($pdo, $id, [
                    'id_servico' => null,
                    'descricao' => $d,
                    'qtd' => (float)str_replace(',', '.', ($qtd[$i] ?? '1')),
                    'valor_unit' => (float)str_replace(',', '.', ($val[$i] ?? '0'))
                ]);
            }

            $pdo->commit();
            header("Location: /financas/public/?url=propostas-edit&id=".$id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['erro'] = "Erro ao atualizar proposta.";
            header("Location: /financas/public/?url=propostas-edit&id=".$id);
            exit;
        }
    }

    public static function setStatus($pdo)
    {
        $idUsuario = $_SESSION['usuario']['id'];
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'rascunho';

        Proposta::setStatus($pdo, $idUsuario, $id, $status);
        header("Location: /financas/public/?url=propostas-edit&id=".$id);
        exit;
    }

    public static function delete($pdo)
    {
        $idUsuario = $_SESSION['usuario']['id'];
        $id = (int)($_POST['id'] ?? 0);

        Proposta::excluir($pdo, $idUsuario, $id);
        header("Location: /financas/public/?url=propostas");
        exit;
    }
        public static function pdf(PDO $pdo)
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo "ID inválido";
            exit;
        }

        // -----------------------------
        // EXEMPLO DE DADOS (mock)
        // Depois você troca por SELECT no seu banco:
        // proposta + cliente + itens
        // -----------------------------
        $empresa = [
            'nome' => 'FAUSTINO MÓVEIS PLANEJADOS',
            'endereco' => 'Rua Alegre, 123 - Cidade Brasileira',
            'responsavel' => 'Responsável técnico: Tiago Souza',
            'contato' => 'Contato: (12) 3456-7890',
        ];

        $cliente = [
            'nome' => 'Cliente Exemplo',
            'email' => 'cliente@email.com',
            'telefone' => '(11) 99999-9999',
            'endereco' => 'Rua X, 500 - Bairro Y',
            'cidade' => 'São Paulo/SP',
            'pagamento' => 'Pix / Cartão / Boleto',
        ];

        $itens = [
            ['descricao' => 'Cozinha completa', 'qtd' => 1, 'unit' => 20000],
            ['descricao' => 'Guarda-roupa casal', 'qtd' => 1, 'unit' => 5000],
        ];

        $observacoes = [
            'Prazo de entrega: 30 dias após aprovação do orçamento',
            'Validade do orçamento: 15 dias após envio do orçamento',
            '*Orçamento feito conforme projeto',
        ];

        $textoFinal = "Prezado Sr.(a),<br><br>
        Obrigado(a) por escolher a {$empresa['nome']}. Estamos felizes em participar deste projeto.
        Seguem acima todos os itens e condições de fornecimento para sua análise. Caso haja dúvida,
        entre em contato pelo número descrito no cabeçalho deste orçamento.";

        // Total
        $total = 0;
        foreach ($itens as $i) {
            $total += ((float)$i['qtd'] * (float)$i['unit']);
        }

        // Renderiza HTML do PDF via view
        $view = __DIR__ . '/../views/propostas/pdf.php';
        if (!file_exists($view)) {
            http_response_code(500);
            echo "View do PDF não encontrada: $view";
            exit;
        }

        ob_start();
        include $view; // usa $empresa, $cliente, $itens, $total, $observacoes, $textoFinal
        $html = ob_get_clean();

        // Dompdf
        require_once __DIR__ . '/../libs/dompdf/autoload.inc.php';

        $options = new Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf\Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        $nomeArquivo = 'proposta_' . $id . '.pdf';
        $dompdf->stream($nomeArquivo, ['Attachment' => true]);
        exit;
    }
}
