-- Estrutura completa do banco do gerenciador e-Doc.
-- Compatível com MariaDB 10.11 e destinada somente a bancos vazios.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE `perfis` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `chave` varchar(50) NOT NULL,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    UNIQUE KEY `uk_perfis_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `modulos` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `chave` varchar(50) NOT NULL,
    `descricao` text DEFAULT NULL,
    `ordem` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    UNIQUE KEY `uk_modulos_chave` (`chave`),
    KEY `idx_modulos_ordem` (`ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissoes` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `modulo_codigo` bigint(20) UNSIGNED NOT NULL,
    `nome` varchar(100) NOT NULL,
    `chave` varchar(100) NOT NULL,
    `descricao` text DEFAULT NULL,
    `ordem` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    UNIQUE KEY `uk_permissoes_chave` (`chave`),
    KEY `idx_permissoes_modulo` (`modulo_codigo`),
    KEY `idx_permissoes_ordem` (`modulo_codigo`, `ordem`),
    CONSTRAINT `fk_permissoes_modulo`
        FOREIGN KEY (`modulo_codigo`)
        REFERENCES `modulos` (`codigo`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `perfil_permissoes` (
    `perfil_codigo` bigint(20) UNSIGNED NOT NULL,
    `permissao_codigo` bigint(20) UNSIGNED NOT NULL,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`perfil_codigo`, `permissao_codigo`),
    KEY `idx_perfil_permissoes_permissao` (`permissao_codigo`),
    CONSTRAINT `fk_perfil_permissoes_perfil`
        FOREIGN KEY (`perfil_codigo`)
        REFERENCES `perfis` (`codigo`) ON UPDATE CASCADE,
    CONSTRAINT `fk_perfil_permissoes_permissao`
        FOREIGN KEY (`permissao_codigo`)
        REFERENCES `permissoes` (`codigo`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `usuarios` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NOT NULL,
    `usuario` varchar(100) NOT NULL,
    `email` varchar(255) NOT NULL,
    `senha` varchar(255) NOT NULL,
    `perfil_codigo` bigint(20) UNSIGNED NOT NULL,
    `ativo` tinyint(1) NOT NULL DEFAULT 1,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    KEY `idx_usuarios_perfil` (`perfil_codigo`),
    CONSTRAINT `fk_usuarios_perfil`
        FOREIGN KEY (`perfil_codigo`)
        REFERENCES `perfis` (`codigo`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `auditorias` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_codigo` bigint(20) UNSIGNED DEFAULT NULL,
    `modulo` varchar(50) NOT NULL,
    `acao` varchar(50) NOT NULL,
    `entidade` varchar(50) NOT NULL,
    `entidade_codigo` bigint(20) UNSIGNED DEFAULT NULL,
    `dados_anteriores` longtext DEFAULT NULL,
    `dados_novos` longtext DEFAULT NULL,
    `endereco_ip` varchar(45) DEFAULT NULL,
    `user_agent` varchar(500) DEFAULT NULL,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`codigo`),
    KEY `idx_auditorias_usuario` (`usuario_codigo`),
    KEY `idx_auditorias_modulo_acao` (`modulo`, `acao`),
    KEY `idx_auditorias_entidade` (`entidade`, `entidade_codigo`),
    KEY `idx_auditorias_cadastro` (`cadastro`),
    CONSTRAINT `fk_auditorias_usuario`
        FOREIGN KEY (`usuario_codigo`)
        REFERENCES `usuarios` (`codigo`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tipos_documento` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome` varchar(150) NOT NULL,
    `descricao` text DEFAULT NULL,
    `ativo` tinyint(1) NOT NULL DEFAULT 1,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `metadados` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `chave` varchar(100) DEFAULT NULL,
    `nome` varchar(150) NOT NULL,
    `descricao` text DEFAULT NULL,
    `tipo_campo` varchar(30) NOT NULL,
    `mascara` varchar(100) DEFAULT NULL,
    `opcoes` text DEFAULT NULL COMMENT 'Opcoes para campos de selecao, preferencialmente em JSON',
    `ativo` tinyint(1) NOT NULL DEFAULT 1,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    UNIQUE KEY `uk_metadados_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tipos_localizacao` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `chave` varchar(50) DEFAULT NULL,
    `nome` varchar(100) NOT NULL,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    UNIQUE KEY `uk_tipos_localizacao_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `localizacoes` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `protocolo` varchar(40) DEFAULT NULL,
    `tipo_localizacao_codigo` bigint(20) UNSIGNED NOT NULL,
    `localizacao_codigo_pai` bigint(20) UNSIGNED DEFAULT NULL,
    `sequencial` bigint(20) UNSIGNED NOT NULL,
    `nome` varchar(255) NOT NULL,
    `classificacao` varchar(500) NOT NULL,
    `descricao` text DEFAULT NULL,
    `ativo` tinyint(1) NOT NULL DEFAULT 1,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    UNIQUE KEY `uk_localizacoes_protocolo` (`protocolo`),
    KEY `idx_localizacoes_pai` (`localizacao_codigo_pai`),
    KEY `idx_localizacoes_ativo` (`ativo`),
    KEY `idx_localizacoes_tipo_localizacao` (`tipo_localizacao_codigo`),
    CONSTRAINT `fk_localizacoes_pai`
        FOREIGN KEY (`localizacao_codigo_pai`)
        REFERENCES `localizacoes` (`codigo`) ON UPDATE CASCADE,
    CONSTRAINT `fk_localizacoes_tipo_localizacao`
        FOREIGN KEY (`tipo_localizacao_codigo`)
        REFERENCES `tipos_localizacao` (`codigo`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `documentos` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `protocolo` varchar(40) DEFAULT NULL,
    `tipo_documento_codigo` bigint(20) UNSIGNED NOT NULL,
    `localizacao_codigo` bigint(20) UNSIGNED NOT NULL,
    `titulo` varchar(255) NOT NULL,
    `descricao` text DEFAULT NULL,
    `numero_identificacao` varchar(100) DEFAULT NULL,
    `data_documento` date DEFAULT NULL,
    `ativo` tinyint(4) NOT NULL DEFAULT 1,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    UNIQUE KEY `uk_documentos_protocolo` (`protocolo`),
    KEY `idx_documentos_tipo` (`tipo_documento_codigo`),
    KEY `idx_documentos_localizacao` (`localizacao_codigo`),
    KEY `idx_documentos_titulo` (`titulo`),
    KEY `idx_documentos_numero_identificacao` (`numero_identificacao`),
    KEY `idx_documentos_data` (`data_documento`),
    KEY `idx_documentos_ativo` (`ativo`),
    CONSTRAINT `fk_documentos_localizacao`
        FOREIGN KEY (`localizacao_codigo`)
        REFERENCES `localizacoes` (`codigo`) ON UPDATE CASCADE,
    CONSTRAINT `fk_documentos_tipo`
        FOREIGN KEY (`tipo_documento_codigo`)
        REFERENCES `tipos_documento` (`codigo`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `documento_arquivos` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `documento_codigo` bigint(20) UNSIGNED NOT NULL,
    `arquivo_raiz_codigo` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Arquivo da versão 1 que identifica a linhagem',
    `nome_original` varchar(255) NOT NULL,
    `nome_armazenado` varchar(255) NOT NULL,
    `extensao` varchar(20) NOT NULL,
    `mime_type` varchar(100) DEFAULT NULL,
    `caminho` varchar(500) NOT NULL,
    `tamanho` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Tamanho do arquivo em bytes',
    `versao` int(10) UNSIGNED NOT NULL DEFAULT 1,
    `principal` tinyint(1) NOT NULL DEFAULT 0,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    KEY `idx_documento_arquivos_documento` (`documento_codigo`),
    KEY `idx_documento_arquivos_versao` (`documento_codigo`, `versao`),
    UNIQUE KEY `uk_documento_arquivos_raiz_versao` (`arquivo_raiz_codigo`, `versao`),
    CONSTRAINT `fk_documento_arquivos_documento`
        FOREIGN KEY (`documento_codigo`)
        REFERENCES `documentos` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_documento_arquivos_raiz`
        FOREIGN KEY (`arquivo_raiz_codigo`)
        REFERENCES `documento_arquivos` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `documento_metadados` (
    `documento_codigo` bigint(20) UNSIGNED NOT NULL,
    `metadado_codigo` bigint(20) UNSIGNED NOT NULL,
    `valor` text DEFAULT NULL,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`documento_codigo`, `metadado_codigo`),
    KEY `idx_documento_metadados_metadado` (`metadado_codigo`),
    CONSTRAINT `fk_documento_metadados_documento`
        FOREIGN KEY (`documento_codigo`)
        REFERENCES `documentos` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_documento_metadados_metadado`
        FOREIGN KEY (`metadado_codigo`)
        REFERENCES `metadados` (`codigo`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `documento_movimentacoes` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `protocolo` varchar(40) DEFAULT NULL,
    `documento_codigo` bigint(20) UNSIGNED NOT NULL,
    `movimentacao_origem_codigo` bigint(20) UNSIGNED DEFAULT NULL,
    `usuario_codigo` bigint(20) UNSIGNED DEFAULT NULL,
    `localizacao_origem_codigo` bigint(20) UNSIGNED DEFAULT NULL,
    `localizacao_destino_codigo` bigint(20) UNSIGNED DEFAULT NULL,
    `tipo_movimentacao` varchar(30) NOT NULL,
    `responsavel_nome` varchar(255) DEFAULT NULL,
    `responsavel_contato` varchar(255) DEFAULT NULL,
    `observacao` text DEFAULT NULL,
    `data_movimentacao` datetime NOT NULL DEFAULT current_timestamp(),
    `data_prevista_devolucao` date DEFAULT NULL,
    `data_devolucao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    UNIQUE KEY `uk_documento_movimentacoes_protocolo` (`protocolo`),
    KEY `idx_documento_movimentacoes_documento` (`documento_codigo`),
    KEY `idx_documento_movimentacoes_origem_movimentacao` (`movimentacao_origem_codigo`),
    KEY `idx_documento_movimentacoes_usuario` (`usuario_codigo`),
    KEY `idx_documento_movimentacoes_origem` (`localizacao_origem_codigo`),
    KEY `idx_documento_movimentacoes_destino` (`localizacao_destino_codigo`),
    KEY `idx_documento_movimentacoes_data` (`data_movimentacao`),
    KEY `idx_documento_movimentacoes_aberta`
        (`documento_codigo`, `tipo_movimentacao`, `data_devolucao`, `codigo`),
    KEY `idx_documento_movimentacoes_situacao`
        (`tipo_movimentacao`, `data_devolucao`, `data_prevista_devolucao`),
    CONSTRAINT `fk_documento_movimentacoes_destino`
        FOREIGN KEY (`localizacao_destino_codigo`)
        REFERENCES `localizacoes` (`codigo`) ON UPDATE CASCADE,
    CONSTRAINT `fk_documento_movimentacoes_documento`
        FOREIGN KEY (`documento_codigo`)
        REFERENCES `documentos` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_documento_movimentacoes_movimentacao_origem`
        FOREIGN KEY (`movimentacao_origem_codigo`)
        REFERENCES `documento_movimentacoes` (`codigo`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_documento_movimentacoes_origem`
        FOREIGN KEY (`localizacao_origem_codigo`)
        REFERENCES `localizacoes` (`codigo`) ON UPDATE CASCADE,
    CONSTRAINT `fk_documento_movimentacoes_usuario`
        FOREIGN KEY (`usuario_codigo`)
        REFERENCES `usuarios` (`codigo`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `localizacao_tipo_documentos` (
    `localizacao_codigo` bigint(20) UNSIGNED NOT NULL,
    `tipo_documento_codigo` bigint(20) UNSIGNED NOT NULL,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`localizacao_codigo`, `tipo_documento_codigo`),
    KEY `idx_localizacao_tipo_documentos_tipo_documento` (`tipo_documento_codigo`),
    CONSTRAINT `fk_localizacao_tipo_documentos_localizacao`
        FOREIGN KEY (`localizacao_codigo`)
        REFERENCES `localizacoes` (`codigo`) ON UPDATE CASCADE,
    CONSTRAINT `fk_localizacao_tipo_documentos_tipo_documento`
        FOREIGN KEY (`tipo_documento_codigo`)
        REFERENCES `tipos_documento` (`codigo`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tipo_documento_metadados` (
    `tipo_documento_codigo` bigint(20) UNSIGNED NOT NULL,
    `metadado_codigo` bigint(20) UNSIGNED NOT NULL,
    `ordem` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
    `obrigatorio` tinyint(1) NOT NULL DEFAULT 0,
    `visivel` tinyint(1) NOT NULL DEFAULT 1,
    `pesquisavel` tinyint(1) NOT NULL DEFAULT 1,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`tipo_documento_codigo`, `metadado_codigo`),
    KEY `idx_tipo_documento_metadados_ordem` (`tipo_documento_codigo`, `ordem`),
    KEY `idx_tipo_documento_metadados_metadado` (`metadado_codigo`),
    CONSTRAINT `fk_tipo_documento_metadados_metadado`
        FOREIGN KEY (`metadado_codigo`)
        REFERENCES `metadados` (`codigo`) ON UPDATE CASCADE,
    CONSTRAINT `fk_tipo_documento_metadados_tipo`
        FOREIGN KEY (`tipo_documento_codigo`)
        REFERENCES `tipos_documento` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
