-- Adiciona a linhagem necessária para versionar arquivos sem misturar anexos.

ALTER TABLE `documento_arquivos`
    ADD COLUMN IF NOT EXISTS `arquivo_raiz_codigo` bigint(20) UNSIGNED DEFAULT NULL
        COMMENT 'Arquivo da versão 1 que identifica a linhagem'
        AFTER `documento_codigo`;

ALTER TABLE `documento_arquivos`
    ADD UNIQUE INDEX IF NOT EXISTS `uk_documento_arquivos_raiz_versao`
        (`arquivo_raiz_codigo`, `versao`);

SET @fk_documento_arquivos_raiz_existe = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
        AND TABLE_NAME = 'documento_arquivos'
        AND CONSTRAINT_NAME = 'fk_documento_arquivos_raiz'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql_fk_documento_arquivos_raiz = IF(
    @fk_documento_arquivos_raiz_existe > 0,
    'SELECT 1',
    'ALTER TABLE `documento_arquivos`
        ADD CONSTRAINT `fk_documento_arquivos_raiz`
        FOREIGN KEY (`arquivo_raiz_codigo`)
        REFERENCES `documento_arquivos` (`codigo`)
        ON DELETE CASCADE
        ON UPDATE CASCADE'
);

PREPARE stmt_fk_documento_arquivos_raiz
    FROM @sql_fk_documento_arquivos_raiz;
EXECUTE stmt_fk_documento_arquivos_raiz;
DEALLOCATE PREPARE stmt_fk_documento_arquivos_raiz;
