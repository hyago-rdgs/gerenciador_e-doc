-- Cria o controle de acesso dinâmico e preserva o acesso dos usuários atuais.

CREATE TABLE IF NOT EXISTS `perfis` (
    `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `chave` varchar(50) NOT NULL,
    `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
    `atualizacao` datetime DEFAULT NULL,
    `exclusao` datetime DEFAULT NULL,
    PRIMARY KEY (`codigo`),
    UNIQUE KEY `uk_perfis_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `modulos` (
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

CREATE TABLE IF NOT EXISTS `permissoes` (
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

CREATE TABLE IF NOT EXISTS `perfil_permissoes` (
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

INSERT INTO `perfis` (`nome`, `chave`)
VALUES
    ('Administrador', 'administrador'),
    ('Gestor documental', 'gestor_documental'),
    ('Operador', 'operador'),
    ('Consulta', 'consulta')
ON DUPLICATE KEY UPDATE
    `nome` = VALUES(`nome`),
    `atualizacao` = current_timestamp(),
    `exclusao` = NULL;

INSERT INTO `modulos` (`nome`, `chave`, `descricao`, `ordem`)
VALUES
    ('Documentos', 'documentos', 'Cadastro e gerenciamento de documentos.', 10),
    ('Arquivos', 'arquivos', 'Arquivos digitais vinculados aos documentos.', 20),
    ('Pesquisa', 'pesquisa', 'Pesquisa documental e por localização.', 30),
    ('Localizações', 'localizacoes', 'Estrutura hierárquica de armazenamento.', 40),
    ('Tipos de documento', 'tipos_documento', 'Configuração dos tipos documentais.', 50),
    ('Metadados', 'metadados', 'Configuração dos campos de metadados.', 60),
    ('Etiquetas', 'etiquetas', 'Geração de etiquetas de localização.', 70),
    ('Usuários', 'usuarios', 'Administração dos usuários do sistema.', 80),
    ('Perfis', 'perfis', 'Configuração visual das permissões dos perfis.', 90)
ON DUPLICATE KEY UPDATE
    `nome` = VALUES(`nome`),
    `descricao` = VALUES(`descricao`),
    `ordem` = VALUES(`ordem`),
    `atualizacao` = current_timestamp(),
    `exclusao` = NULL;

INSERT INTO `permissoes` (
    `modulo_codigo`,
    `nome`,
    `chave`,
    `descricao`,
    `ordem`
)
SELECT
    m.`codigo`,
    dados.`nome`,
    dados.`chave`,
    dados.`descricao`,
    dados.`ordem`
FROM (
    SELECT 'documentos' AS modulo, 'Visualizar' AS nome, 'documentos.visualizar' AS chave, 'Listar e consultar documentos.' AS descricao, 10 AS ordem
    UNION ALL SELECT 'documentos', 'Cadastrar e editar', 'documentos.gerenciar', 'Cadastrar, editar e movimentar documentos.', 20
    UNION ALL SELECT 'documentos', 'Excluir', 'documentos.excluir', 'Excluir documentos logicamente.', 30
    UNION ALL SELECT 'arquivos', 'Visualizar', 'arquivos.visualizar', 'Abrir e baixar arquivos.', 10
    UNION ALL SELECT 'arquivos', 'Gerenciar', 'arquivos.gerenciar', 'Enviar, definir principal e excluir arquivos.', 20
    UNION ALL SELECT 'pesquisa', 'Acessar', 'pesquisa.acessar', 'Realizar pesquisas documentais.', 10
    UNION ALL SELECT 'localizacoes', 'Visualizar', 'localizacoes.visualizar', 'Listar e consultar localizações.', 10
    UNION ALL SELECT 'localizacoes', 'Gerenciar', 'localizacoes.gerenciar', 'Cadastrar, editar e excluir localizações.', 20
    UNION ALL SELECT 'tipos_documento', 'Gerenciar', 'tipos_documento.gerenciar', 'Administrar tipos de documento e seus vínculos.', 10
    UNION ALL SELECT 'metadados', 'Gerenciar', 'metadados.gerenciar', 'Administrar metadados.', 10
    UNION ALL SELECT 'etiquetas', 'Gerar', 'etiquetas.gerar', 'Gerar etiquetas de localização.', 10
    UNION ALL SELECT 'usuarios', 'Gerenciar', 'usuarios.gerenciar', 'Administrar usuários.', 10
    UNION ALL SELECT 'perfis', 'Gerenciar', 'perfis.gerenciar', 'Administrar perfis e permissões.', 10
) dados
INNER JOIN `modulos` m
    ON m.`chave` = dados.`modulo`
ON DUPLICATE KEY UPDATE
    `modulo_codigo` = VALUES(`modulo_codigo`),
    `nome` = VALUES(`nome`),
    `descricao` = VALUES(`descricao`),
    `ordem` = VALUES(`ordem`),
    `atualizacao` = current_timestamp(),
    `exclusao` = NULL;

INSERT IGNORE INTO `perfil_permissoes` (
    `perfil_codigo`,
    `permissao_codigo`
)
SELECT p.`codigo`, pe.`codigo`
FROM `perfis` p
CROSS JOIN `permissoes` pe
WHERE p.`chave` = 'administrador';

INSERT IGNORE INTO `perfil_permissoes` (
    `perfil_codigo`,
    `permissao_codigo`
)
SELECT p.`codigo`, pe.`codigo`
FROM `perfis` p
CROSS JOIN `permissoes` pe
WHERE p.`chave` = 'gestor_documental'
    AND pe.`chave` NOT IN (
        'usuarios.gerenciar',
        'perfis.gerenciar'
    );

INSERT IGNORE INTO `perfil_permissoes` (
    `perfil_codigo`,
    `permissao_codigo`
)
SELECT p.`codigo`, pe.`codigo`
FROM `perfis` p
CROSS JOIN `permissoes` pe
WHERE p.`chave` = 'operador'
    AND pe.`chave` IN (
        'documentos.visualizar',
        'documentos.gerenciar',
        'arquivos.visualizar',
        'arquivos.gerenciar',
        'pesquisa.acessar',
        'localizacoes.visualizar',
        'etiquetas.gerar'
    );

INSERT IGNORE INTO `perfil_permissoes` (
    `perfil_codigo`,
    `permissao_codigo`
)
SELECT p.`codigo`, pe.`codigo`
FROM `perfis` p
CROSS JOIN `permissoes` pe
WHERE p.`chave` = 'consulta'
    AND pe.`chave` IN (
        'documentos.visualizar',
        'arquivos.visualizar',
        'pesquisa.acessar',
        'localizacoes.visualizar',
        'etiquetas.gerar'
    );

ALTER TABLE `usuarios`
    ADD COLUMN IF NOT EXISTS `perfil_codigo` bigint(20) UNSIGNED DEFAULT NULL AFTER `senha`;

UPDATE `usuarios` u
INNER JOIN `perfis` p
    ON p.`chave` = 'administrador'
SET u.`perfil_codigo` = p.`codigo`
WHERE u.`perfil_codigo` IS NULL;

ALTER TABLE `usuarios`
    MODIFY COLUMN `perfil_codigo` bigint(20) UNSIGNED NOT NULL;

ALTER TABLE `usuarios`
    ADD INDEX IF NOT EXISTS `idx_usuarios_perfil` (`perfil_codigo`);

SET @fk_usuarios_perfil_existe = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
        AND TABLE_NAME = 'usuarios'
        AND CONSTRAINT_NAME = 'fk_usuarios_perfil'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql_fk_usuarios_perfil = IF(
    @fk_usuarios_perfil_existe > 0,
    'SELECT 1',
    'ALTER TABLE `usuarios`
        ADD CONSTRAINT `fk_usuarios_perfil`
        FOREIGN KEY (`perfil_codigo`)
        REFERENCES `perfis` (`codigo`)
        ON UPDATE CASCADE'
);

PREPARE stmt_fk_usuarios_perfil FROM @sql_fk_usuarios_perfil;
EXECUTE stmt_fk_usuarios_perfil;
DEALLOCATE PREPARE stmt_fk_usuarios_perfil;
