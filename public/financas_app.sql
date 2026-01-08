-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql102.infinityfree.com
-- Tempo de geração: 08/01/2026 às 14:50
-- Versão do servidor: 11.4.9-MariaDB
-- Versão do PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `if0_40840040_financas_app`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `tipo` enum('R','D') NOT NULL,
  `criada_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `icone` varchar(50) DEFAULT 'bi-tag'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `id_usuario`, `nome`, `tipo`, `criada_em`, `icone`) VALUES
(1, 2, 'Teste', 'D', '2026-01-02 19:32:28', 'bi-tag'),
(2, 2, 'teste', 'R', '2026-01-02 19:32:36', 'bi-tag'),
(3, 3, 'teste', 'R', '2026-01-05 17:49:36', 'bi-tag'),
(4, 2, 'Alimentação', 'D', '2026-01-05 18:20:22', 'bi-tag'),
(5, 3, 'testee', 'D', '2026-01-05 18:33:28', 'bi-tag'),
(8, 4, 'Freelance', 'R', '2026-01-06 23:10:59', 'bi-tag'),
(9, 4, 'Gasolina', 'D', '2026-01-06 23:11:20', 'bi-car-front'),
(10, 4, 'Plano Celular', 'D', '2026-01-06 23:11:32', 'bi-tag'),
(12, 4, 'Namorada', 'D', '2026-01-06 23:11:47', 'bi-cart'),
(13, 4, 'Salario', 'R', '2026-01-06 23:12:34', 'bi-tag'),
(14, 4, 'Cartao', 'D', '2026-01-06 23:14:37', 'bi-tag'),
(15, 4, 'Emprestimos', 'D', '2026-01-06 23:15:51', 'bi-tag'),
(16, 4, 'Investimentos', 'D', '2026-01-06 23:18:01', 'bi-tag');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias_servico`
--

CREATE TABLE `categorias_servico` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `icone` varchar(60) DEFAULT 'bi-tag',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `documento` varchar(30) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `endereco` varchar(255) DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `id_usuario`, `nome`, `email`, `telefone`, `documento`, `criado_em`, `endereco`, `observacoes`) VALUES
(1, 3, 'Teste-', 'teste@gmail.com', 'ww', '11111111111', '2026-01-08 19:20:48', 'www', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `contas`
--

CREATE TABLE `contas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `saldo_inicial` decimal(10,2) DEFAULT 0.00,
  `saldo_atual` decimal(10,2) DEFAULT 0.00,
  `criada_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `contas`
--

INSERT INTO `contas` (`id`, `id_usuario`, `nome`, `saldo_inicial`, `saldo_atual`, `criada_em`) VALUES
(1, 2, '1212121', '100.00', '100.00', '2026-01-02 19:39:27'),
(2, 3, 'Murilo', '100.00', '100.00', '2026-01-05 17:49:25'),
(5, 4, 'Nubank', '2000.00', '2657.96', '2026-01-06 23:09:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lancamentos`
--

CREATE TABLE `lancamentos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_conta` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `tipo` enum('R','D') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data` date NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'pago',
  `grupo_uuid` varchar(36) DEFAULT NULL,
  `recorrencia_id` int(11) DEFAULT NULL,
  `parcelamento_id` int(11) DEFAULT NULL,
  `parcela_num` int(11) DEFAULT NULL,
  `parcela_total` int(11) DEFAULT NULL,
  `transferencia_id` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `lancamentos`
--

INSERT INTO `lancamentos` (`id`, `id_usuario`, `id_conta`, `id_categoria`, `tipo`, `valor`, `data`, `descricao`, `status`, `grupo_uuid`, `recorrencia_id`, `parcelamento_id`, `parcela_num`, `parcela_total`, `transferencia_id`, `criado_em`) VALUES
(1, 2, 1, 1, 'R', '120.00', '2026-01-03', 'teste', 'pago', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 19:39:44'),
(5, 4, 5, 13, 'R', '2000.00', '2026-01-05', 'Salario Myse', 'pago', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 23:12:51'),
(6, 4, 5, 8, 'R', '25.00', '2026-01-05', 'Hospedagem Pereirao', 'pago', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 23:13:17'),
(7, 4, 5, 10, 'D', '44.88', '2026-01-05', 'Plano Claro', 'pago', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 23:14:15'),
(8, 4, 5, 14, 'D', '85.78', '2026-01-05', 'Cartao empresas', 'pago', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 23:15:03'),
(9, 4, 5, 14, 'D', '161.86', '2026-01-05', 'Cartao Nubank', 'pago', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 23:15:33'),
(10, 4, 5, 15, 'D', '53.51', '2026-01-05', 'Parcela Mouse', 'pago', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 23:16:11'),
(11, 4, 5, 15, 'D', '424.41', '2026-01-05', 'Parcela Celular', 'pago', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 23:16:36'),
(12, 4, 5, 16, 'D', '300.00', '2026-01-05', 'CDI', 'pago', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 23:18:18'),
(13, 4, 5, 16, 'D', '92.60', '2026-01-05', 'Curso (paguei leo)', 'pago', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 23:19:30'),
(14, 4, 5, 9, 'D', '50.00', '2026-01-07', '', 'pago', '50dc927a-c0bb-4921-9629-a045efcb2eba', NULL, NULL, NULL, NULL, NULL, '2026-01-08 04:35:48'),
(15, 4, 5, 12, 'D', '154.00', '2026-01-07', 'Anticoncepcional', 'pago', '85ddb07b-e90c-4208-ae64-5eb294bd20a6', NULL, NULL, NULL, NULL, NULL, '2026-01-08 04:36:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lancamento_anexos`
--

CREATE TABLE `lancamento_anexos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_lancamento` int(11) NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `mime` varchar(80) DEFAULT NULL,
  `tamanho` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `metas`
--

CREATE TABLE `metas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `valor_limite` decimal(12,2) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamentos`
--

CREATE TABLE `orcamentos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `orcamentos`
--

INSERT INTO `orcamentos` (`id`, `id_usuario`, `id_categoria`, `ano`, `mes`, `valor`, `criado_em`) VALUES
(1, 3, 5, 2026, 1, '1000.00', '2026-01-05 18:51:38'),
(16, 4, 9, 2026, 1, '400.00', '2026-01-06 23:20:45'),
(19, 4, 14, 2026, 1, '247.64', '2026-01-06 23:23:32'),
(20, 4, 15, 2026, 1, '477.92', '2026-01-06 23:23:40'),
(22, 4, 16, 2026, 1, '392.60', '2026-01-06 23:23:49'),
(28, 4, 10, 2026, 1, '44.88', '2026-01-06 23:24:57'),
(29, 4, 12, 2026, 1, '500.00', '2026-01-06 23:25:03');

-- --------------------------------------------------------

--
-- Estrutura para tabela `parcelamentos`
--

CREATE TABLE `parcelamentos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` char(1) NOT NULL,
  `id_conta` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `total_parcelas` int(11) NOT NULL,
  `data_inicio` date NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `propostas`
--

CREATE TABLE `propostas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `numero` varchar(20) NOT NULL,
  `ano` int(11) NOT NULL,
  `status` enum('rascunho','enviado','aprovado','recusado') NOT NULL DEFAULT 'rascunho',
  `data_emissao` date NOT NULL,
  `validade_dias` int(11) NOT NULL DEFAULT 15,
  `cliente_nome` varchar(120) DEFAULT NULL,
  `cliente_email` varchar(120) DEFAULT NULL,
  `cliente_telefone` varchar(40) DEFAULT NULL,
  `cliente_endereco` varchar(255) DEFAULT NULL,
  `forma_pagamento` varchar(120) DEFAULT NULL,
  `desconto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `observacoes` text DEFAULT NULL,
  `consideracoes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `propostas`
--

INSERT INTO `propostas` (`id`, `id_usuario`, `id_cliente`, `numero`, `ano`, `status`, `data_emissao`, `validade_dias`, `cliente_nome`, `cliente_email`, `cliente_telefone`, `cliente_endereco`, `forma_pagamento`, `desconto`, `total`, `observacoes`, `consideracoes`, `criado_em`, `atualizado_em`) VALUES
(2, 3, NULL, '2026-0001', 0, 'rascunho', '2026-01-08', 15, 'Teste', 'murlxff@gmail.com', '', '', '', '0.00', '10.00', 'Prazo de entrega: 30 dias após aprovação\r\nValidade do orçamento: 15 dias', 'Prezado(a),\r\nObrigado por escolher nossos serviços.', '2026-01-08 13:54:08', '2026-01-08 13:54:08'),
(3, 4, NULL, '2026-0001', 0, 'enviado', '2026-01-10', 20, 'Murilo Henrique Lodi', 'murlxff@gmail.com', '45999997579', 'Rua marechal Floriano 195', 'PIx', '0.00', '375.00', 'Prazo de entrega: 30 dias após aprovação\r\nValidade do orçamento: 15 dias', 'Prezado(a),\r\nObrigado por escolher nossos serviços.', '2026-01-08 13:56:55', '2026-01-08 13:56:55'),
(4, 3, NULL, '2026-0002', 0, 'rascunho', '2026-01-08', 15, 'Teste-', 'teste@gmail.com', 'ww', 'www', '', '0.00', '0.00', 'Prazo de entrega: 30 dias após aprovação\r\nValidade do orçamento: 15 dias', 'Prezado(a),\r\nObrigado por escolher nossos serviços.', '2026-01-08 19:21:01', '2026-01-08 19:21:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `proposta_itens`
--

CREATE TABLE `proposta_itens` (
  `id` int(11) NOT NULL,
  `id_proposta` int(11) NOT NULL,
  `ordem` int(11) NOT NULL DEFAULT 1,
  `descricao` varchar(255) NOT NULL,
  `quantidade` decimal(10,2) NOT NULL DEFAULT 1.00,
  `valor_unit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `proposta_itens`
--

INSERT INTO `proposta_itens` (`id`, `id_proposta`, `ordem`, `descricao`, `quantidade`, `valor_unit`, `valor_total`, `criado_em`) VALUES
(1, 2, 1, 'Teste', '1.00', '10.00', '10.00', '2026-01-08 13:54:08'),
(2, 3, 1, 'Site', '1.00', '300.00', '300.00', '2026-01-08 13:56:55'),
(3, 3, 2, 'Hospedagem', '1.00', '25.00', '25.00', '2026-01-08 13:56:55'),
(4, 3, 3, 'Domínio', '1.00', '50.00', '50.00', '2026-01-08 13:56:55'),
(5, 4, 1, 'ted', '1.00', '0.00', '0.00', '2026-01-08 19:21:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `recorrencias`
--

CREATE TABLE `recorrencias` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` char(1) NOT NULL,
  `id_conta` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `frequencia` varchar(10) NOT NULL,
  `dia_mes` tinyint(4) DEFAULT NULL,
  `dia_semana` tinyint(4) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recorrencia_execucoes`
--

CREATE TABLE `recorrencia_execucoes` (
  `id` int(11) NOT NULL,
  `recorrencia_id` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

CREATE TABLE `servicos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_categoria_servico` int(11) DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duracao_min` int(11) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `transferencias`
--

CREATE TABLE `transferencias` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_conta_origem` int(11) NOT NULL,
  `id_conta_destino` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data` date NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `nome_empresa` varchar(120) DEFAULT NULL,
  `endereco_empresa` varchar(255) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `empresa_nome` varchar(120) DEFAULT NULL,
  `empresa_logo` varchar(255) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('pessoal','familiar','empresa') DEFAULT 'pessoal',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `nome_empresa`, `endereco_empresa`, `telefone`, `empresa_nome`, `empresa_logo`, `email`, `avatar`, `senha`, `tipo`, `criado_em`, `is_admin`, `is_blocked`, `last_login_at`, `last_login_ip`, `created_at`, `updated_at`) VALUES
(1, 'Admin', NULL, NULL, NULL, NULL, NULL, 'admin@teste.com', NULL, '<?= password_hash(\"123456\", PASSWORD_DEFAULT) ?>', 'pessoal', '2026-01-02 19:10:29', 0, 0, NULL, NULL, '2026-01-08 19:49:28', '2026-01-08 19:49:28'),
(2, 'Murilo', NULL, NULL, NULL, NULL, NULL, 'm@gmail.com', NULL, '$2y$10$1jhCVFkYIF2R8h.4rPMmxONr6XsSsUIoFav8Y79muVvTlO3KlEHIO', 'pessoal', '2026-01-02 19:15:31', 0, 0, NULL, NULL, '2026-01-08 19:49:28', '2026-01-08 19:49:28'),
(3, 'Murilo', NULL, NULL, NULL, NULL, NULL, 'murlxff@gmail.com', 'avatar_1767899993.jpg', '$2y$10$N.0ehlGYDWkgPOTcgzKr9eWKdDBEJxO0jFuE8o.MnoHVUY97isaO.', 'pessoal', '2026-01-05 17:44:36', 0, 0, NULL, NULL, '2026-01-08 19:49:28', '2026-01-08 19:49:28'),
(4, 'Murilo Lodi', NULL, NULL, NULL, NULL, NULL, 'murilo@gmail.com', 'avatar_1767842928.jpg', '$2y$10$hStPDRcmUsJn4jW4wSbmd.1af0uGIxc8OkbeQmNlx3KWPMEyV1u8O', 'pessoal', '2026-01-06 23:09:15', 1, 0, NULL, NULL, '2026-01-08 19:49:28', '2026-01-08 19:49:28');

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `categorias_servico`
--
ALTER TABLE `categorias_servico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `contas`
--
ALTER TABLE `contas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `lancamentos`
--
ALTER TABLE `lancamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_conta` (`id_conta`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `idx_lanc_usuario_data` (`id_usuario`,`data`),
  ADD KEY `idx_lanc_status` (`status`),
  ADD KEY `idx_lanc_grupo` (`grupo_uuid`);

--
-- Índices de tabela `lancamento_anexos`
--
ALTER TABLE `lancamento_anexos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_anexo_lanc` (`id_lancamento`);

--
-- Índices de tabela `metas`
--
ALTER TABLE `metas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_meta` (`id_usuario`,`id_categoria`,`ano`,`mes`);

--
-- Índices de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_orcamento` (`id_usuario`,`id_categoria`,`ano`,`mes`),
  ADD KEY `fk_orc_categoria` (`id_categoria`);

--
-- Índices de tabela `parcelamentos`
--
ALTER TABLE `parcelamentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `propostas`
--
ALTER TABLE `propostas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_proposta_numero` (`id_usuario`,`ano`,`numero`),
  ADD UNIQUE KEY `uq_usuario_numero` (`id_usuario`,`numero`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Índices de tabela `proposta_itens`
--
ALTER TABLE `proposta_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proposta_itens` (`id_proposta`);

--
-- Índices de tabela `recorrencias`
--
ALTER TABLE `recorrencias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `recorrencia_execucoes`
--
ALTER TABLE `recorrencia_execucoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_recorrencia_mes` (`recorrencia_id`,`ano`,`mes`);

--
-- Índices de tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_categoria_servico` (`id_categoria_servico`);

--
-- Índices de tabela `transferencias`
--
ALTER TABLE `transferencias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `categorias_servico`
--
ALTER TABLE `categorias_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `contas`
--
ALTER TABLE `contas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `lancamentos`
--
ALTER TABLE `lancamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `lancamento_anexos`
--
ALTER TABLE `lancamento_anexos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `metas`
--
ALTER TABLE `metas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de tabela `parcelamentos`
--
ALTER TABLE `parcelamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `propostas`
--
ALTER TABLE `propostas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `proposta_itens`
--
ALTER TABLE `proposta_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `recorrencias`
--
ALTER TABLE `recorrencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recorrencia_execucoes`
--
ALTER TABLE `recorrencia_execucoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `transferencias`
--
ALTER TABLE `transferencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para dumps de tabelas
--

--
-- Restrições para tabelas `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `categorias_servico`
--
ALTER TABLE `categorias_servico`
  ADD CONSTRAINT `fk_catserv_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_clientes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `contas`
--
ALTER TABLE `contas`
  ADD CONSTRAINT `contas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `lancamentos`
--
ALTER TABLE `lancamentos`
  ADD CONSTRAINT `lancamentos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `lancamentos_ibfk_2` FOREIGN KEY (`id_conta`) REFERENCES `contas` (`id`),
  ADD CONSTRAINT `lancamentos_ibfk_3` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`);

--
-- Restrições para tabelas `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD CONSTRAINT `fk_orc_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_orc_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `propostas`
--
ALTER TABLE `propostas`
  ADD CONSTRAINT `fk_propostas_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `fk_propostas_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `proposta_itens`
--
ALTER TABLE `proposta_itens`
  ADD CONSTRAINT `fk_proposta_itens` FOREIGN KEY (`id_proposta`) REFERENCES `propostas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `servicos`
--
ALTER TABLE `servicos`
  ADD CONSTRAINT `fk_servicos_catserv` FOREIGN KEY (`id_categoria_servico`) REFERENCES `categorias_servico` (`id`),
  ADD CONSTRAINT `fk_servicos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
