-- Remove o conceito de ativo/inativo dos documentos.
--
-- A existência operacional de um documento passa a ser determinada
-- exclusivamente por `exclusao IS NULL`. Registros anteriormente marcados
-- como inativos e não excluídos permanecem no acervo.

ALTER TABLE `documentos`
    DROP INDEX IF EXISTS `idx_documentos_ativo`,
    DROP COLUMN IF EXISTS `ativo`;
