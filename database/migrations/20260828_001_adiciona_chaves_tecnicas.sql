-- Adiciona identificadores técnicos opcionais.
-- As colunas são opcionais para preservar os registros já cadastrados.

ALTER TABLE `tipos_localizacao`
    ADD COLUMN IF NOT EXISTS `chave` varchar(50) DEFAULT NULL AFTER `codigo`;

ALTER TABLE `tipos_localizacao`
    ADD UNIQUE INDEX IF NOT EXISTS `uk_tipos_localizacao_chave` (`chave`);

ALTER TABLE `metadados`
    ADD COLUMN IF NOT EXISTS `chave` varchar(100) DEFAULT NULL AFTER `codigo`;

ALTER TABLE `metadados`
    ADD UNIQUE INDEX IF NOT EXISTS `uk_metadados_chave` (`chave`);
