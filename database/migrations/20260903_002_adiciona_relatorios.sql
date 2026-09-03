-- Adiciona o módulo de relatórios e suas permissões.

INSERT INTO `modulos` (
    `nome`,
    `chave`,
    `descricao`,
    `ordem`
)
VALUES (
    'Relatórios',
    'relatorios',
    'Relatórios operacionais e gerenciais com exportação de dados.',
    37
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
    dados.`nome`,
    dados.`chave`,
    dados.`descricao`,
    dados.`ordem`
FROM (
    SELECT
        'Visualizar' AS nome,
        'relatorios.visualizar' AS chave,
        'Consultar os relatórios disponíveis.' AS descricao,
        10 AS ordem
    UNION ALL
    SELECT
        'Exportar',
        'relatorios.exportar',
        'Exportar relatórios em PDF e Excel.',
        20
) dados
INNER JOIN `modulos` m
    ON m.`chave` = 'relatorios'
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
    ON pe.`chave` IN (
        'relatorios.visualizar',
        'relatorios.exportar'
    )
WHERE p.`chave` IN (
    'administrador',
    'gestor_documental'
);
