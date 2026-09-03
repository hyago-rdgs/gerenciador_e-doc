-- Dados técnicos iniciais do controle de acesso.
-- Execute uma vez após o schema em instalações novas.

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
    ('Movimentações', 'movimentacoes', 'Transferência, retirada, devolução e rastreabilidade documental.', 25),
    ('Pesquisa', 'pesquisa', 'Pesquisa documental e por localização.', 30),
    ('Dashboard', 'dashboard', 'Indicadores gerenciais e operacionais do acervo.', 35),
    ('Relatórios', 'relatorios', 'Relatórios operacionais e gerenciais com exportação de dados.', 37),
    ('Localizações', 'localizacoes', 'Estrutura hierárquica de armazenamento.', 40),
    ('Tipos de documento', 'tipos_documento', 'Configuração dos tipos documentais.', 50),
    ('Metadados', 'metadados', 'Configuração dos campos de metadados.', 60),
    ('Etiquetas', 'etiquetas', 'Geração de etiquetas de localização.', 70),
    ('Usuários', 'usuarios', 'Administração dos usuários do sistema.', 80),
    ('Perfis', 'perfis', 'Configuração visual das permissões dos perfis.', 90),
    ('Auditoria', 'auditoria', 'Consulta do histórico geral de alterações do sistema.', 100)
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
    UNION ALL SELECT 'movimentacoes', 'Visualizar', 'movimentacoes.visualizar', 'Consultar o histórico e a situação das movimentações.', 10
    UNION ALL SELECT 'movimentacoes', 'Gerenciar', 'movimentacoes.gerenciar', 'Transferir, retirar e devolver documentos.', 20
    UNION ALL SELECT 'pesquisa', 'Acessar', 'pesquisa.acessar', 'Realizar pesquisas documentais.', 10
    UNION ALL SELECT 'dashboard', 'Visualizar', 'dashboard.visualizar', 'Consultar dashboards e indicadores gerenciais.', 10
    UNION ALL SELECT 'relatorios', 'Visualizar', 'relatorios.visualizar', 'Consultar os relatórios disponíveis.', 10
    UNION ALL SELECT 'relatorios', 'Exportar', 'relatorios.exportar', 'Exportar relatórios em PDF e Excel.', 20
    UNION ALL SELECT 'localizacoes', 'Visualizar', 'localizacoes.visualizar', 'Listar e consultar localizações.', 10
    UNION ALL SELECT 'localizacoes', 'Gerenciar', 'localizacoes.gerenciar', 'Cadastrar, editar e excluir localizações.', 20
    UNION ALL SELECT 'tipos_documento', 'Gerenciar', 'tipos_documento.gerenciar', 'Administrar tipos de documento e seus vínculos.', 10
    UNION ALL SELECT 'metadados', 'Gerenciar', 'metadados.gerenciar', 'Administrar metadados.', 10
    UNION ALL SELECT 'etiquetas', 'Gerar', 'etiquetas.gerar', 'Gerar etiquetas de localização.', 10
    UNION ALL SELECT 'usuarios', 'Gerenciar', 'usuarios.gerenciar', 'Administrar usuários.', 10
    UNION ALL SELECT 'perfis', 'Gerenciar', 'perfis.gerenciar', 'Administrar perfis e permissões.', 10
    UNION ALL SELECT 'auditoria', 'Visualizar', 'auditoria.visualizar', 'Consultar o histórico geral de auditoria.', 10
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
        'perfis.gerenciar',
        'auditoria.visualizar'
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
        'movimentacoes.visualizar',
        'movimentacoes.gerenciar',
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
        'movimentacoes.visualizar',
        'pesquisa.acessar',
        'localizacoes.visualizar',
        'etiquetas.gerar'
    );
