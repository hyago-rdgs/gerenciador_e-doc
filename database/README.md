# Banco de dados do gerenciador e-Doc

Os arquivos deste diretório representam a estrutura de banco exigida pelo
branch `main` do core.

## Instalação nova

Execute apenas o arquivo `schema.sql` em um banco vazio.

```bash
mariadb -u USUARIO -p NOME_DO_BANCO < database/schema.sql
```

## Atualização de uma instalação existente

Execute, em ordem alfabética, somente as migrations ainda não aplicadas.

```bash
mariadb -u USUARIO -p NOME_DO_BANCO \
    < database/migrations/20260828_001_adiciona_chaves_tecnicas.sql
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
