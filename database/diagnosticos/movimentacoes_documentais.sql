-- Todas as consultas devem retornar zero registros.

-- Documentos com mais de uma retirada em aberto.
SELECT
    `documento_codigo`,
    COUNT(*) AS `retiradas_abertas`
FROM `documento_movimentacoes`
WHERE `tipo_movimentacao` = 'RETIRADA'
    AND `data_devolucao` IS NULL
GROUP BY `documento_codigo`
HAVING COUNT(*) > 1;

-- Devoluções sem retirada correspondente.
SELECT dm.*
FROM `documento_movimentacoes` dm
LEFT JOIN `documento_movimentacoes` retirada
    ON retirada.`codigo` = dm.`movimentacao_origem_codigo`
    AND retirada.`tipo_movimentacao` = 'RETIRADA'
WHERE dm.`tipo_movimentacao` = 'DEVOLUCAO'
    AND retirada.`codigo` IS NULL;

-- Retiradas sem responsável.
SELECT *
FROM `documento_movimentacoes`
WHERE `tipo_movimentacao` = 'RETIRADA'
    AND (`responsavel_nome` IS NULL OR TRIM(`responsavel_nome`) = '');

-- Devolução registrada antes da retirada.
SELECT *
FROM `documento_movimentacoes`
WHERE `tipo_movimentacao` = 'RETIRADA'
    AND `data_devolucao` IS NOT NULL
    AND `data_devolucao` < `data_movimentacao`;
