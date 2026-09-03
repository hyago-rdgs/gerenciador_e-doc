-- Adiciona a consulta da auditoria ao controle de acesso.

INSERT INTO `modulos` (
    `nome`,
    `chave`,
    `descricao`,
    `ordem`
)
VALUES (
    'Auditoria',
    'auditoria',
    'Consulta do histórico geral de alterações do sistema.',
    100
)
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
    'Visualizar',
    'auditoria.visualizar',
    'Consultar o histórico geral de auditoria.',
    10
FROM `modulos` m
WHERE m.`chave` = 'auditoria'
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
SELECT
    p.`codigo`,
    pe.`codigo`
FROM `perfis` p
INNER JOIN `permissoes` pe
    ON pe.`chave` = 'auditoria.visualizar'
WHERE p.`chave` = 'administrador';
