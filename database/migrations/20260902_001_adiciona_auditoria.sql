-- Adiciona a infraestrutura genérica de auditoria do core.

CREATE TABLE IF NOT EXISTS `auditorias` (
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
