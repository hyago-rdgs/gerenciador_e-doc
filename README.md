# e-Doc — Gerenciador Eletrônico de Documentos

Sistema web para gerenciamento de documentos físicos e digitais, desenvolvido para centralizar o cadastro, a organização, a localização e a pesquisa documental dentro de uma instituição.

O e-Doc combina uma estrutura hierárquica de localizações com tipos de documento e metadados configuráveis. Dessa forma, a aplicação pode representar tanto a organização física do acervo quanto as informações específicas de cada categoria documental.

## Estado atual do projeto

O projeto está em desenvolvimento ativo e já possui uma base funcional para os principais fluxos do sistema.

Atualmente estão disponíveis:

- autenticação de usuários;
- tela inicial pós-login com pesquisa global, ações rápidas e acesso aos módulos;
- cadastro, edição, listagem, visualização e exclusão lógica de documentos;
- upload e gerenciamento de arquivos vinculados aos documentos;
- cadastro e gerenciamento de tipos de documento;
- configuração de metadados por tipo de documento;
- cadastro e gerenciamento de metadados;
- cadastro e gerenciamento hierárquico de localizações;
- navegação entre localizações e sublocalizações;
- pesquisa avançada por dados básicos e metadados;
- navegação de documentos pela estrutura de localizações;
- controle de registros ativos e inativos;
- validação de acesso aos módulos autenticados.

## Tela inicial

Após o login, o usuário é direcionado para uma central de trabalho do e-Doc.

A tela inicial prioriza as operações mais frequentes, oferecendo:

- pesquisa rápida por documentos;
- acesso à pesquisa avançada;
- atalhos para novos documentos, localizações, tipos de documento e metadados;
- cards de acesso aos principais módulos do sistema.

Indicadores e análises gerenciais são tratados como um módulo separado, evitando transformar a página inicial em um dashboard excessivamente carregado.

## Principais módulos

### Documentos

Responsável pelo gerenciamento do acervo documental.

Entre as funcionalidades estão:

- cadastro de documentos;
- edição e visualização;
- associação a um tipo de documento;
- associação a uma localização;
- preenchimento de metadados configuráveis;
- armazenamento de arquivos digitais;
- pesquisa e filtragem;
- exclusão lógica.

### Tipos de documento

Define as categorias documentais utilizadas pelo sistema e quais metadados devem ser apresentados durante o cadastro de cada documento.

Exemplos:

- Contrato;
- Nota fiscal;
- Prontuário;
- Memorando;
- Ofício.

### Metadados

Permite criar campos adicionais reutilizáveis para complementar as informações padrão dos documentos.

Os metadados podem utilizar diferentes tipos de campo HTML e posteriormente ser vinculados aos tipos de documento.

Exemplos:

- número do processo;
- CPF;
- data de vencimento;
- setor responsável;
- categoria;
- observações específicas.

### Localizações

Representa a estrutura física ou administrativa utilizada para armazenar documentos.

As localizações são organizadas de forma hierárquica e não possuem uma quantidade fixa de níveis.

Exemplo:

```text
Instituição
└── Setor
    └── Sala
        └── Armário
            └── Prateleira
                └── Caixa
                    └── Documento
```

Uma localização sem `localizacao_codigo_pai` é considerada uma localização raiz.

Cada localização pode possuir:

- nome;
- descrição;
- tipo de localização;
- classificação;
- localização superior;
- sublocalizações;
- documentos vinculados;
- situação ativa ou inativa.

### Pesquisa

O módulo de pesquisa permite localizar documentos utilizando diferentes estratégias.

Atualmente são contempladas:

- pesquisa rápida a partir da tela inicial;
- pesquisa avançada;
- filtros por tipo de documento;
- pesquisa por título e número de identificação;
- filtros por período;
- filtros por situação;
- pesquisa utilizando metadados configuráveis;
- navegação por localizações.

## Modelo conceitual

O domínio principal do sistema utiliza as seguintes entidades:

- **Usuários:** responsáveis pelo acesso autenticado ao sistema;
- **Documentos:** armazenam as informações principais de cada documento;
- **Tipos de documento:** definem categorias documentais;
- **Metadados:** representam campos adicionais configuráveis;
- **Tipo de documento–metadado:** define quais metadados pertencem a cada tipo documental;
- **Documento–metadado:** armazena os valores preenchidos para cada documento;
- **Localizações:** representam a árvore de armazenamento físico ou administrativo;
- **Tipos de localização:** classificam as localizações;
- **Arquivos:** representam os arquivos digitais vinculados aos documentos.

De forma simplificada:

```text
Tipo de documento
       │
       ├── Metadados configurados
       │
       ▼
   Documento
       │
       ├── Valores dos metadados
       ├── Arquivos digitais
       │
       ▼
   Localização
       │
       ▼
Estrutura hierárquica
```

## Tecnologias utilizadas

- PHP;
- CodeIgniter 3;
- MySQL/MariaDB;
- HTML5;
- Bootstrap 5;
- jQuery;
- Font Awesome.

A interface segue uma abordagem simples e consistente, priorizando componentes nativos do Bootstrap e evitando CSS e JavaScript adicionais quando não são necessários.

## Estrutura do projeto

A aplicação segue a estrutura MVC do CodeIgniter 3.

```text
application/
├── config/
├── controllers/
│   ├── Autenticacao.php
│   ├── Documento.php
│   ├── Localizacao.php
│   ├── Metadado.php
│   ├── Pesquisa.php
│   ├── Principal.php
│   └── Tipo_documento.php
├── libraries/
├── models/
└── views/
    ├── autenticacao/
    ├── documento/
    ├── localizacao/
    ├── metadado/
    ├── pesquisa/
    ├── tipo_documento/
    ├── principal.php
    ├── nav.php
    ├── css.php
    └── js.php
```

As views compartilhadas `nav.php`, `css.php` e `js.php` concentram os elementos comuns da interface.

## Padrões adotados

Alguns princípios utilizados no desenvolvimento do projeto:

- controllers responsáveis pela coordenação das requisições;
- models responsáveis pelo acesso e persistência dos dados;
- reaproveitamento de views sempre que cadastro e edição possuem a mesma estrutura;
- validação no backend antes de operações de persistência;
- respostas JSON padronizadas nas operações AJAX;
- exclusão lógica por meio do campo `exclusao`;
- consultas considerando apenas registros válidos e não excluídos;
- uso de `base_url()` para construção das rotas;
- escape de dados exibidos nas views;
- autenticação centralizada pela biblioteca de controle de acesso;
- interface construída principalmente com Bootstrap e Font Awesome.

## Requisitos

Para executar o projeto localmente, é necessário possuir:

- PHP compatível com CodeIgniter 3;
- Apache ou Nginx;
- MySQL ou MariaDB;
- Git.

Também é possível utilizar ambientes locais como XAMPP, Laragon ou containers Docker configurados para PHP e MySQL/MariaDB.

## Instalação

Clone o repositório:

```bash
git clone git@github.com:hyago-rdgs/gerenciador_e-doc.git
```

Acesse o diretório:

```bash
cd gerenciador_e-doc
```

Configure o banco de dados em:

```text
application/config/database.php
```

Configure a URL base da aplicação em:

```text
application/config/config.php
```

Exemplo:

```php
$config['base_url'] = 'http://localhost/gerenciador_e-doc/';
```

Em seguida, configure o servidor web para servir o diretório do projeto e disponibilize o banco de dados utilizado pela aplicação.

> A configuração exata do ambiente pode variar de acordo com o servidor utilizado no desenvolvimento ou em produção.

## Fluxo de autenticação

O acesso aos módulos internos exige autenticação.

O fluxo básico é:

```text
Login
  │
  ▼
Autenticação
  │
  ▼
Tela inicial
  │
  ├── Documentos
  ├── Pesquisa
  ├── Tipos de documento
  ├── Metadados
  └── Localizações
```

Controllers protegidos utilizam a biblioteca de controle de acesso para validar a sessão antes de permitir o acesso ao conteúdo.

## Convenção de commits

O projeto utiliza mensagens objetivas inspiradas em Conventional Commits.

```text
feat: adiciona nova funcionalidade
fix: corrige um problema
refactor: reorganiza o código sem alterar o comportamento
docs: atualiza a documentação
style: ajusta formatação ou interface
```

## Roadmap

### Concluído ou disponível

- [x] Estrutura inicial do projeto em CodeIgniter 3
- [x] Autenticação de usuários
- [x] Controle de acesso aos módulos autenticados
- [x] Tela inicial pós-login
- [x] Pesquisa rápida na tela inicial
- [x] Cadastro, edição e listagem de localizações
- [x] Navegação hierárquica entre localizações
- [x] Detalhes de localização
- [x] Exclusão lógica de localizações
- [x] Cadastro e gerenciamento de metadados
- [x] Cadastro e gerenciamento de tipos de documento
- [x] Vinculação de metadados aos tipos de documento
- [x] Cadastro e gerenciamento de documentos
- [x] Upload de arquivos de documentos
- [x] Pesquisa avançada de documentos
- [x] Pesquisa por metadados
- [x] Navegação de documentos por localização

### Próximas etapas

- [ ] Dashboard e indicadores gerenciais
- [ ] Perfis e níveis de permissão
- [ ] Histórico detalhado de movimentações
- [ ] Auditoria das operações dos usuários
- [ ] Melhorias na pesquisa global
- [ ] Versionamento de documentos
- [ ] Recursos adicionais de gestão documental

## Status

O e-Doc ainda está em desenvolvimento e pode sofrer mudanças na estrutura do banco, nas regras de negócio e na organização dos módulos.

## Autor

Desenvolvido por [Hyago Rodrigues](https://github.com/hyago-rdgs).

## Licença

Consulte o arquivo `license.txt` disponível na raiz do repositório.
