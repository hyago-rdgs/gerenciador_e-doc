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

## Bloco 2 — Movimentações

O relatório de movimentações permite filtrar o histórico por:

- protocolo da movimentação, protocolo ou título do documento e responsável;
- tipo de movimentação;
- situação;
- localização de origem ou destino;
- usuário responsável pelo registro;
- período da movimentação.

Por padrão, o período corresponde ao mês atual. Datas explicitamente vazias
permitem consultar todo o histórico.

Os indicadores apresentam:

- total de movimentações encontradas;
- quantidade de documentos distintos movimentados;
- transferências;
- retiradas.

A rastreabilidade é preservada mesmo quando o documento foi excluído
logicamente. Nessa situação, o relatório mantém os dados históricos, mas não
oferece acesso à tela do documento.

## Bloco 3 — Custódia e retiradas

O relatório de custódia apresenta exclusivamente movimentações do tipo
`RETIRADA` e permite filtrar por:

- protocolo da retirada, documento, responsável ou contato;
- situação da custódia;
- tipo documental;
- localização de origem;
- usuário responsável pelo registro;
- período da retirada.

Por padrão, são exibidas as retiradas em aberto, sem limitar o período. O
usuário pode consultar também itens atrasados, com vencimento no dia, sem
previsão ou já devolvidos.

Os indicadores apresentam:

- retiradas em aberto;
- retiradas atrasadas;
- retiradas que vencem no dia;
- retiradas sem previsão de devolução.

O relatório calcula os dias em custódia e, quando aplicável, os dias de atraso.
O histórico permanece disponível após a exclusão lógica do documento, sem
oferecer acesso ao cadastro excluído.

## Bloco 4 — Digitalização

O relatório de digitalização permite filtrar documentos por:

- termo;
- situação digital;
- tipo documental;
- localização;
- período de cadastro.

Por padrão, todo o acervo ativo é considerado, sem limitar o período. As
situações disponíveis são documentos com arquivo, sem arquivo e com múltiplas
versões.

Os indicadores apresentam:

- total de documentos;
- documentos com e sem arquivo;
- percentual de cobertura digital;
- total de arquivos atuais;
- total de versões armazenadas;
- documentos com múltiplas versões;
- espaço total ocupado.

Uma linhagem de arquivo conta como um arquivo atual. Todas as versões físicas
ativas dessa linhagem contam no total de versões e no espaço armazenado.
Documentos e arquivos excluídos logicamente não entram nos cálculos.

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

Os mesmos limites são aplicados aos quatro relatórios.

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
