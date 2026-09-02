-- Amplia o histórico existente para rastrear transferências, retiradas e devoluções.

ALTER TABLE `documento_movimentacoes`
    ADD COLUMN IF NOT EXISTS `protocolo` varchar(40) DEFAULT NULL
        AFTER `codigo`,
    ADD COLUMN IF NOT EXISTS `movimentacao_origem_codigo` bigint(20) UNSIGNED DEFAULT NULL
        AFTER `documento_codigo`,
    ADD COLUMN IF NOT EXISTS `usuario_codigo` bigint(20) UNSIGNED DEFAULT NULL
        AFTER `movimentacao_origem_codigo`,
    ADD COLUMN IF NOT EXISTS `responsavel_nome` varchar(255) DEFAULT NULL
        AFTER `tipo_movimentacao`,
    ADD COLUMN IF NOT EXISTS `responsavel_contato` varchar(255) DEFAULT NULL
        AFTER `responsavel_nome`,
    ADD COLUMN IF NOT EXISTS `data_prevista_devolucao` date DEFAULT NULL
        AFTER `data_movimentacao`;

UPDATE `documento_movimentacoes`
SET `protocolo` = CONCAT('MOV-MIG-', LPAD(`codigo`, 10, '0'))
WHERE `protocolo` IS NULL;

ALTER TABLE `documento_movimentacoes`
    ADD UNIQUE INDEX IF NOT EXISTS `uk_documento_movimentacoes_protocolo` (`protocolo`),
    ADD INDEX IF NOT EXISTS `idx_documento_movimentacoes_origem_movimentacao`
        (`movimentacao_origem_codigo`),
    ADD INDEX IF NOT EXISTS `idx_documento_movimentacoes_usuario` (`usuario_codigo`),
    ADD INDEX IF NOT EXISTS `idx_documento_movimentacoes_aberta`
        (`documento_codigo`, `tipo_movimentacao`, `data_devolucao`, `codigo`),
    ADD INDEX IF NOT EXISTS `idx_documento_movimentacoes_situacao`
        (`tipo_movimentacao`, `data_devolucao`, `data_prevista_devolucao`);

SET @fk_movimentacao_origem_existe = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
        AND TABLE_NAME = 'documento_movimentacoes'
        AND CONSTRAINT_NAME = 'fk_documento_movimentacoes_movimentacao_origem'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql_fk_movimentacao_origem = IF(
    @fk_movimentacao_origem_existe > 0,
    'SELECT 1',
    'ALTER TABLE `documento_movimentacoes`
        ADD CONSTRAINT `fk_documento_movimentacoes_movimentacao_origem`
        FOREIGN KEY (`movimentacao_origem_codigo`)
        REFERENCES `documento_movimentacoes` (`codigo`)
        ON DELETE SET NULL
        ON UPDATE CASCADE'
);

PREPARE stmt_fk_movimentacao_origem FROM @sql_fk_movimentacao_origem;
EXECUTE stmt_fk_movimentacao_origem;
DEALLOCATE PREPARE stmt_fk_movimentacao_origem;

SET @fk_movimentacao_usuario_existe = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
        AND TABLE_NAME = 'documento_movimentacoes'
        AND CONSTRAINT_NAME = 'fk_documento_movimentacoes_usuario'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql_fk_movimentacao_usuario = IF(
    @fk_movimentacao_usuario_existe > 0,
    'SELECT 1',
    'ALTER TABLE `documento_movimentacoes`
        ADD CONSTRAINT `fk_documento_movimentacoes_usuario`
        FOREIGN KEY (`usuario_codigo`)
        REFERENCES `usuarios` (`codigo`)
        ON DELETE SET NULL
        ON UPDATE CASCADE'
);

PREPARE stmt_fk_movimentacao_usuario FROM @sql_fk_movimentacao_usuario;
EXECUTE stmt_fk_movimentacao_usuario;
DEALLOCATE PREPARE stmt_fk_movimentacao_usuario;

INSERT INTO `modulos` (`nome`, `chave`, `descricao`, `ordem`)
VALUES (
    'Movimentações',
    'movimentacoes',
    'Transferência, retirada, devolução e rastreabilidade documental.',
    25
)
ON DUPLICATE KEY UPDATE
    `nome` = VALUES(`nome`),
    `descricao` = VALUES(`descricao`),
    `ordem` = VALUES(`ordem`),
    `atualizacao` = current_timestamp(),
    `exclusao` = NULL;

INSERT INTO `permissoes` (`modulo_codigo`, `nome`, `chave`, `descricao`, `ordem`)
SELECT m.`codigo`, dados.`nome`, dados.`chave`, dados.`descricao`, dados.`ordem`
FROM (
    SELECT 'Visualizar' AS nome,
        'movimentacoes.visualizar' AS chave,
        'Consultar o histórico e a situação das movimentações.' AS descricao,
        10 AS ordem
    UNION ALL
    SELECT 'Gerenciar',
        'movimentacoes.gerenciar',
        'Transferir, retirar e devolver documentos.',
        20
) dados
INNER JOIN `modulos` m ON m.`chave` = 'movimentacoes'
ON DUPLICATE KEY UPDATE
    `modulo_codigo` = VALUES(`modulo_codigo`),
    `nome` = VALUES(`nome`),
    `descricao` = VALUES(`descricao`),
    `ordem` = VALUES(`ordem`),
    `atualizacao` = current_timestamp(),
    `exclusao` = NULL;

INSERT IGNORE INTO `perfil_permissoes` (`perfil_codigo`, `permissao_codigo`)
SELECT p.`codigo`, pe.`codigo`
FROM `perfis` p
CROSS JOIN `permissoes` pe
WHERE p.`chave` IN ('administrador', 'gestor_documental')
    AND pe.`chave` IN (
        'movimentacoes.visualizar',
        'movimentacoes.gerenciar'
    );

INSERT IGNORE INTO `perfil_permissoes` (`perfil_codigo`, `permissao_codigo`)
SELECT p.`codigo`, pe.`codigo`
FROM `perfis` p
CROSS JOIN `permissoes` pe
WHERE p.`chave` = 'operador'
    AND pe.`chave` IN (
        'movimentacoes.visualizar',
        'movimentacoes.gerenciar'
    );

INSERT IGNORE INTO `perfil_permissoes` (`perfil_codigo`, `permissao_codigo`)
SELECT p.`codigo`, pe.`codigo`
FROM `perfis` p
CROSS JOIN `permissoes` pe
WHERE p.`chave` = 'consulta'
    AND pe.`chave` = 'movimentacoes.visualizar';
