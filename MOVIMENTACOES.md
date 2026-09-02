# Movimentação e rastreabilidade de documentos

O módulo de movimentações registra mudanças de localização e de custódia dos
documentos físicos. Cada operação recebe um protocolo próprio e identifica o
usuário autenticado que realizou o registro.

## Tipos de movimentação

- `CADASTRO`: entrada inicial do documento em uma localização;
- `TRANSFERENCIA`: mudança entre duas localizações internas compatíveis;
- `RETIRADA`: entrega temporária do documento a um responsável;
- `DEVOLUCAO`: retorno de uma retirada para uma localização interna.

## Regras

- a localização não pode ser alterada no formulário comum do documento;
- toda mudança física deve utilizar a ação de transferência;
- somente localizações ativas e compatíveis com o tipo documental são aceitas;
- um documento pode possuir somente uma retirada em aberto;
- documentos retirados não podem ser transferidos ou excluídos;
- a devolução encerra a retirada e registra uma movimentação vinculada;
- transferência, retirada e devolução são executadas em transação;
- as operações são registradas também na auditoria do core.

## Situações

Uma retirada é considerada:

- **em aberto** quando ainda não possui `data_devolucao`;
- **em atraso** quando está aberta e a previsão é anterior à data atual;
- **concluída** quando a devolução foi registrada.

Cadastros, transferências e devoluções são eventos concluídos no momento do
registro.

## Implantação

Em bancos existentes, aplique:

```bash
mariadb -u USUARIO -p NOME_DO_BANCO \
    < database/migrations/20260902_002_adiciona_movimentacoes_documentais.sql
```

A migration preserva o histórico existente, gera protocolos determinísticos
para os registros antigos e adiciona o módulo às permissões dos perfis padrão.

Após validar os fluxos, execute o diagnóstico somente leitura:

```bash
mariadb -u USUARIO -p NOME_DO_BANCO \
    < database/diagnosticos/movimentacoes_documentais.sql
```

Todas as consultas devem retornar zero registros.
