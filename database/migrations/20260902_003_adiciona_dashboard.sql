-- Adiciona o módulo de dashboard e a permissão de visualização.

INSERT INTO `modulos` (`nome`, `chave`, `descricao`, `ordem`)
VALUES (
    'Dashboard',
    'dashboard',
    'Indicadores gerenciais e operacionais do acervo.',
    35
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
    'dashboard.visualizar',
    'Consultar dashboards e indicadores gerenciais.',
    10
FROM `modulos` m
WHERE m.`chave` = 'dashboard'
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
WHERE p.`chave` IN ('administrador', 'gestor_documental')
    AND pe.`chave` = 'dashboard.visualizar';
