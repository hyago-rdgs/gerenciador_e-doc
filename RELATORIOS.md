# Relatórios

O módulo de Relatórios complementa o Dashboard com consultas detalhadas e
exportáveis dos dados do e-Doc.

## Permissões

O módulo utiliza:

- `relatorios.visualizar`: acessar a central e consultar relatórios;
- `relatorios.exportar`: gerar arquivos PDF e Excel.

Por padrão, ambas são concedidas aos perfis `administrador` e
`gestor_documental`.

## Bloco 1 — Acervo documental

O primeiro relatório permite filtrar documentos por:

- termo;
- tipo de documento;
- localização;
- período de cadastro;
- situação de digitalização.

Por padrão, o período de cadastro corresponde ao mês atual. Caso o usuário
informe explicitamente as datas em branco e aplique o filtro, o relatório
consulta todo o histórico.

Os indicadores apresentam:

- total de documentos encontrados;
- documentos com arquivo digital;
- documentos sem arquivo digital;
- percentual de cobertura digital.

A listagem considera somente documentos com `exclusao IS NULL`.

## Exportações

### PDF

A geração utiliza mPDF e formato A4 paisagem.

Para proteger o processo PHP, a exportação PDF é limitada a 2.000 registros.
Quando o resultado ultrapassar esse valor, o usuário deve aplicar filtros.

O conteúdo tabular é processado em lotes de 50 registros por chamada a
`WriteHTML()`, evitando enviar ao mPDF um bloco HTML excessivamente grande e
reduzindo o risco de atingir o `pcre.backtrack_limit`.

### Excel

A exportação utiliza PhpSpreadsheet e gera XLSX com:

- título e data de emissão;
- descrição dos filtros aplicados;
- cabeçalho destacado;
- filtro automático;
- congelamento do cabeçalho;
- ajuste automático de colunas.

Textos derivados do banco são gravados explicitamente como texto, evitando
interpretação indevida como fórmulas.

A exportação Excel permanece limitada a 20.000 registros.

## Dependências

As bibliotecas são instaladas via Composer:

- `mpdf/mpdf`;
- `phpoffice/phpspreadsheet`.

Em produção, prefira:

```bash
composer install --no-dev --optimize-autoloader
composer check-platform-reqs
```

O diretório `vendor` não deve ser versionado.
