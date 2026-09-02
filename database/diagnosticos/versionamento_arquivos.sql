-- Diagnóstico somente leitura da integridade do versionamento.
-- Cada consulta deve retornar zero registros em uma base consistente.

-- Versões posteriores sem referência para a raiz da linhagem.
SELECT
    arquivo.codigo,
    arquivo.documento_codigo,
    arquivo.versao
FROM documento_arquivos arquivo
WHERE arquivo.versao > 1
    AND arquivo.arquivo_raiz_codigo IS NULL;

-- Referências que não apontam para uma versão 1 do mesmo documento.
SELECT
    arquivo.codigo,
    arquivo.documento_codigo,
    arquivo.arquivo_raiz_codigo,
    raiz.documento_codigo AS raiz_documento_codigo,
    raiz.versao AS raiz_versao
FROM documento_arquivos arquivo
LEFT JOIN documento_arquivos raiz
    ON raiz.codigo = arquivo.arquivo_raiz_codigo
WHERE arquivo.arquivo_raiz_codigo IS NOT NULL
    AND (
        raiz.codigo IS NULL
        OR raiz.documento_codigo <> arquivo.documento_codigo
        OR raiz.versao <> 1
        OR raiz.arquivo_raiz_codigo IS NOT NULL
    );

-- Números de versão repetidos dentro da mesma linhagem.
SELECT
    arquivo.documento_codigo,
    COALESCE(
        arquivo.arquivo_raiz_codigo,
        arquivo.codigo
    ) AS arquivo_raiz_codigo,
    arquivo.versao,
    COUNT(*) AS total
FROM documento_arquivos arquivo
GROUP BY
    arquivo.documento_codigo,
    COALESCE(
        arquivo.arquivo_raiz_codigo,
        arquivo.codigo
    ),
    arquivo.versao
HAVING COUNT(*) > 1;

-- Documentos com mais de um arquivo principal ativo.
SELECT
    arquivo.documento_codigo,
    COUNT(*) AS total_principais
FROM documento_arquivos arquivo
WHERE arquivo.principal = 1
    AND arquivo.exclusao IS NULL
GROUP BY arquivo.documento_codigo
HAVING COUNT(*) > 1;

-- Arquivo principal que não corresponde à versão atual da linhagem.
SELECT
    arquivo.codigo,
    arquivo.documento_codigo,
    arquivo.versao
FROM documento_arquivos arquivo
WHERE arquivo.principal = 1
    AND arquivo.exclusao IS NULL
    AND EXISTS (
        SELECT 1
        FROM documento_arquivos versao_posterior
        WHERE versao_posterior.documento_codigo =
            arquivo.documento_codigo
            AND versao_posterior.exclusao IS NULL
            AND COALESCE(
                versao_posterior.arquivo_raiz_codigo,
                versao_posterior.codigo
            ) = COALESCE(
                arquivo.arquivo_raiz_codigo,
                arquivo.codigo
            )
            AND versao_posterior.versao > arquivo.versao
    );

-- Versões ativas cuja raiz foi excluída isoladamente.
SELECT
    arquivo.codigo,
    arquivo.documento_codigo,
    arquivo.arquivo_raiz_codigo
FROM documento_arquivos arquivo
INNER JOIN documento_arquivos raiz
    ON raiz.codigo = arquivo.arquivo_raiz_codigo
WHERE arquivo.exclusao IS NULL
    AND raiz.exclusao IS NOT NULL;
