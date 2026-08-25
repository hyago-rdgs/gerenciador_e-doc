# e-Doc — Gerenciador Eletrônico de Documentos

Sistema web para gerenciamento de documentos físicos e digitais, desenvolvido para facilitar o cadastro, a classificação, a localização e a consulta de documentos dentro de uma instituição.

O e-Doc utiliza uma estrutura hierárquica de localizações semelhante à organização de uma biblioteca. Dessa forma, cada documento pode ser associado ao seu local exato de armazenamento, como instituição, setor, sala, armário ou prateleira.

## Estado atual do projeto

O projeto está em desenvolvimento. Nesta etapa inicial, está sendo implementado o módulo de **Localizações**, que servirá como base para o armazenamento e a recuperação dos documentos.

Funcionalidades atualmente em desenvolvimento:

- cadastro de localizações;
- listagem das localizações raiz;
- organização hierárquica de localizações e sublocalizações;
- classificação das localizações;
- definição do tipo de localização;
- controle de situação ativa ou inativa;
- consulta dos detalhes de uma localização;
- contagem de sublocalizações e documentos vinculados.

## Estrutura de localizações

As localizações são organizadas em uma árvore hierárquica. Uma localização sem um local superior é considerada uma localização raiz.

Exemplo:

```text
Instituição
└── Setor
    └── Sala
        └── Armário
            └── Prateleira
                └── Documento
```

Essa estrutura permite representar diferentes formas de organização física ou administrativa sem limitar a quantidade de níveis.

## Visão geral

Entre as funcionalidades previstas para o e-Doc estão:

- cadastro e gerenciamento de documentos;
- armazenamento de documentos físicos e digitais;
- definição de tipos de documento;
- criação de metadados personalizados por tipo de documento;
- vinculação de documentos às localizações cadastradas;
- pesquisa de documentos por informações básicas e metadados;
- navegação pela estrutura hierárquica de localizações;
- controle de acesso e permissões de usuários;
- histórico e rastreabilidade das operações.

## Tecnologias utilizadas

- PHP;
- CodeIgniter 3;
- MySQL/MariaDB;
- HTML5;
- Bootstrap 5;
- jQuery;
- Font Awesome.

## Requisitos

Para executar o projeto localmente, é necessário ter instalado:

- PHP compatível com o CodeIgniter 3;
- servidor web Apache ou Nginx;
- MySQL ou MariaDB;
- Git.

Também é possível utilizar um ambiente local como XAMPP, Laragon ou Docker.

## Instalação

Clone o repositório:

```bash
git clone git@github.com:hyago-rdgs/gerenciador_e-doc.git
```

Acesse o diretório do projeto:

```bash
cd gerenciador_e-doc
```

Depois disso:

1. crie o banco de dados da aplicação;
2. importe a estrutura SQL do projeto, quando disponibilizada;
3. configure a conexão em `application/config/database.php`;
4. configure a URL da aplicação em `application/config/config.php`;
5. disponibilize o projeto por meio do servidor web.

Exemplo de configuração da URL base:

```php
$config['base_url'] = 'http://localhost/gerenciador_e-doc/';
```

> As instruções de instalação poderão ser atualizadas conforme a infraestrutura do projeto for definida.

## Organização planejada dos dados

O domínio principal do sistema contempla as seguintes entidades:

- **Localizações:** representam os locais físicos ou administrativos;
- **Tipos de localização:** classificam as localizações cadastradas;
- **Documentos:** armazenam as informações básicas de cada documento;
- **Tipos de documento:** definem categorias documentais;
- **Metadados:** representam campos adicionais configuráveis;
- **Documento–metadado:** armazena os valores dos metadados de cada documento.

## Convenção de commits

O projeto utiliza mensagens de commit objetivas, seguindo uma estrutura inspirada no Conventional Commits:

```text
feat: adiciona nova funcionalidade
fix: corrige um problema
refactor: reorganiza o código sem alterar o comportamento
docs: atualiza a documentação
style: ajusta formatação ou interface
```

## Roadmap

- [x] Estrutura inicial do módulo de localizações
- [x] Listagem das localizações raiz
- [ ] Detalhes e navegação entre sublocalizações
- [ ] Cadastro e edição de localizações
- [ ] Exclusão segura de localizações
- [ ] Cadastro de tipos de documento
- [ ] Configuração de metadados
- [ ] Cadastro e consulta de documentos
- [ ] Autenticação e controle de permissões
- [ ] Histórico de movimentações e auditoria

## Autor

Desenvolvido por [Hyago Rodrigues](https://github.com/hyago-rdgs).

## Licença

Este projeto ainda não possui uma licença definida.
