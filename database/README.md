# Banco de dados do gerenciador e-Doc

Os arquivos deste diretório representam a estrutura de banco exigida pelo
branch `main` do core.

## Instalação nova

Execute o schema e, em seguida, os seeds técnicos:

```bash
mariadb -u USUARIO -p NOME_DO_BANCO < database/schema.sql

mariadb -u USUARIO -p NOME_DO_BANCO \
    < database/seeds/20260829_001_controle_acesso.sql
```

## Atualização de uma instalação existente

Execute, em ordem alfabética, somente as migrations ainda não aplicadas.

```bash
mariadb -u USUARIO -p NOME_DO_BANCO \
    < database/migrations/20260828_001_adiciona_chaves_tecnicas.sql

mariadb -u USUARIO -p NOME_DO_BANCO \
    < database/migrations/20260829_001_adiciona_controle_acesso.sql
mariadb -u USUARIO -p NOME_DO_BANCO \
    < database/migrations/20260901_001_adiciona_versionamento_arquivos.sql

```

O `schema.sql` já contém o resultado das migrations e não deve ser executado
sobre uma instalação que possua dados.

## Convenção

- `schema.sql`: estado completo e atual do banco do core;
- `migrations/`: alterações incrementais para bancos existentes;
- `seeds/`: dados técnicos reutilizáveis, quando existirem;
- nomes de migration: `AAAAMMDD_NNN_descricao.sql`.

Antes de criar uma migration, confirme que a mudança pertence ao core. Dados
e estruturas exclusivos de uma implementação devem permanecer no repositório
correspondente.

