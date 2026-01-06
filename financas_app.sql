-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 06/01/2026 às 19:47
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `financas_app`
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
(6, 3, 'te', 'R', '2026-01-06 17:37:50', 'bi-cup-hot');

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
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 2, '1212121', 100.00, 100.00, '2026-01-02 19:39:27'),
(2, 3, 'Murilo', 100.00, 1020.00, '2026-01-05 17:49:25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lancamentos`
--

CREATE TABLE `lancamentos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_conta` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `tipo` enum('R','D') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data` date NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `lancamentos`
--

INSERT INTO `lancamentos` (`id`, `id_usuario`, `id_conta`, `id_categoria`, `tipo`, `valor`, `data`, `descricao`, `criado_em`) VALUES
(1, 2, 1, 1, 'R', 120.00, '2026-01-03', 'teste', '2026-01-02 19:39:44'),
(2, 3, 2, 3, 'R', 1000.00, '2026-01-06', '', '2026-01-05 17:49:46'),
(3, 3, 2, 5, 'D', 10.00, '2026-01-06', '', '2026-01-05 18:51:55'),
(4, 3, 2, 5, 'D', 70.00, '2026-01-05', '', '2026-01-05 18:55:30');

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
(1, 3, 5, 2026, 1, 0.00, '2026-01-05 18:51:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `propostas`
--

CREATE TABLE `propostas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `numero` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `status` enum('rascunho','enviado','aprovado','recusado') NOT NULL DEFAULT 'rascunho',
  `data_emissao` date NOT NULL,
  `validade_dias` int(11) NOT NULL DEFAULT 7,
  `desconto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `observacoes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `propostas`
--

INSERT INTO `propostas` (`id`, `id_usuario`, `id_cliente`, `numero`, `ano`, `status`, `data_emissao`, `validade_dias`, `desconto`, `observacoes`, `criado_em`) VALUES
(1, 3, NULL, 1, 2026, 'aprovado', '2026-01-06', 7, 0.00, '', '2026-01-06 18:38:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `proposta_itens`
--

CREATE TABLE `proposta_itens` (
  `id` int(11) NOT NULL,
  `id_proposta` int(11) NOT NULL,
  `id_servico` int(11) DEFAULT NULL,
  `descricao` varchar(200) NOT NULL,
  `qtd` decimal(10,2) NOT NULL DEFAULT 1.00,
  `valor_unit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `proposta_itens`
--

INSERT INTO `proposta_itens` (`id`, `id_proposta`, `id_servico`, `descricao`, `qtd`, `valor_unit`, `total`) VALUES
(1, 1, NULL, 'pereirao', 1.00, 10.00, 10.00),
(2, 1, NULL, 'pereirao', 1.00, 110.00, 110.00),
(3, 1, NULL, 'teste', 1.00, 11110.00, 11110.00);

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
  `valor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('pessoal','familiar','empresa') DEFAULT 'pessoal',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `criado_em`) VALUES
(1, 'Admin', 'admin@teste.com', '<?= password_hash(\"123456\", PASSWORD_DEFAULT) ?>', 'pessoal', '2026-01-02 19:10:29'),
(2, 'Murilo', 'm@gmail.com', '$2y$10$1jhCVFkYIF2R8h.4rPMmxONr6XsSsUIoFav8Y79muVvTlO3KlEHIO', 'pessoal', '2026-01-02 19:15:31'),
(3, 'Murilo', 'murlxff@gmail.com', '$2y$10$N.0ehlGYDWkgPOTcgzKr9eWKdDBEJxO0jFuE8o.MnoHVUY97isaO.', 'pessoal', '2026-01-05 17:44:36');

--
-- Índices para tabelas despejadas
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
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Índices de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_orcamento` (`id_usuario`,`id_categoria`,`ano`,`mes`),
  ADD KEY `fk_orc_categoria` (`id_categoria`);

--
-- Índices de tabela `propostas`
--
ALTER TABLE `propostas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_proposta_numero` (`id_usuario`,`ano`,`numero`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Índices de tabela `proposta_itens`
--
ALTER TABLE `proposta_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_proposta` (`id_proposta`),
  ADD KEY `fk_itens_servico` (`id_servico`);

--
-- Índices de tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_categoria_servico` (`id_categoria_servico`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `categorias_servico`
--
ALTER TABLE `categorias_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `contas`
--
ALTER TABLE `contas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `lancamentos`
--
ALTER TABLE `lancamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `propostas`
--
ALTER TABLE `propostas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `proposta_itens`
--
ALTER TABLE `proposta_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
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
  ADD CONSTRAINT `fk_itens_proposta` FOREIGN KEY (`id_proposta`) REFERENCES `propostas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_itens_servico` FOREIGN KEY (`id_servico`) REFERENCES `servicos` (`id`);

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
