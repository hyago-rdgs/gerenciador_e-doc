# Dashboard e indicadores gerenciais

O dashboard do e-Doc concentra indicadores operacionais e gerenciais do
acervo sem substituir a tela inicial do sistema.

## Objetivo

A página apresenta uma visão rápida sobre volume documental, digitalização,
distribuição do acervo e movimentações.

Os dados são calculados no momento da consulta e não são persistidos em uma
tabela própria de indicadores.

## Indicadores principais

- total de documentos não excluídos;
- documentos cadastrados no mês;
- percentual de documentos com arquivo digital;
- total de localizações não excluídas;
- movimentações registradas no mês.

## Gráficos

O módulo utiliza Apache ECharts para apresentar:

- evolução dos cadastros nos últimos 12 meses;
- documentos por tipo documental;
- documentos por localização;
- proporção de documentos com e sem arquivo digital.

## Atenções

A área de atenção apresenta:

- documentos sem arquivo digital;
- retiradas em aberto;
- retiradas com previsão de devolução em atraso.

## Movimentações recentes

A tabela final apresenta as últimas movimentações registradas, incluindo
documento, tipo de movimentação, origem, destino ou responsável e usuário.

## Regra sobre status de documento

O dashboard não utiliza o campo `documentos.ativo` para classificar o acervo
como ativo ou inativo.

Para os indicadores documentais, um documento pertence ao acervo enquanto
`exclusao IS NULL`. Situações físicas ou de custódia devem ser representadas
pelas movimentações e não por um status ativo/inativo.

## Permissão

O acesso ao módulo exige:

`dashboard.visualizar`

A permissão é concedida inicialmente aos perfis `administrador` e
`gestor_documental`.

## Dependência visual

Os gráficos utilizam Apache ECharts 5.6.0 carregado pela página do dashboard.
Bootstrap, jQuery e Font Awesome continuam sendo carregados pelas views
compartilhadas do projeto.
