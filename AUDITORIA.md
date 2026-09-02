# Auditoria

O core possui uma estrutura genérica de auditoria para registrar operações
críticas sem acoplar a tabela a um único módulo.

## Estrutura

A tabela `auditorias` registra:

- usuário responsável pela operação;
- módulo, ação, entidade e código da entidade;
- estado anterior e estado posterior em JSON;
- endereço IP, user agent e data do evento.

Os registros são criados pela biblioteca `Auditoria` e persistidos pelo
`Auditoria_model`. A auditoria participa da mesma transação da alteração de
negócio. Se o registro da auditoria falhar, a operação também é revertida.

## Operações de arquivos

O módulo de documentos registra inicialmente as ações:

- `ARQUIVO_CADASTRADO`;
- `VERSAO_CADASTRADA`;
- `ARQUIVO_PRINCIPAL_ALTERADO`;
- `LINHAGEM_EXCLUIDA`.

A consulta de arquivos e do histórico continua protegida pelas permissões
`arquivos.visualizar`. Alterações e novos uploads exigem
`arquivos.gerenciar`.

## Atualização de uma instalação

Depois de atualizar o código, aplique a migration:

```bash
mariadb -u USUARIO -p NOME_DO_BANCO \
    < database/migrations/20260902_001_adiciona_auditoria.sql
```

Não execute `database/schema.sql` sobre uma base existente.

## Sincronização com uma implementação

Ao sincronizar o core com uma implementação, como o repositório HA:

1. faça backup do banco e dos arquivos documentais;
2. incorpore o commit do core pelo fluxo de upstream;
3. aplique primeiro a migration de versionamento e depois a de auditoria;
4. valide cadastro de arquivo, envio de nova versão, alteração do principal e
   exclusão da linhagem;
5. execute `database/diagnosticos/versionamento_arquivos.sql` e confirme que
   todas as consultas retornam zero registros;
6. confirme os registros correspondentes em `auditorias`;
7. atualize o controle de versão do core mantido pela implementação.

Dados, usuários e regras exclusivos da implementação não devem ser incluídos
nas migrations do core.
