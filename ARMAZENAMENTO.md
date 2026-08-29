# Armazenamento dos documentos

Os arquivos dos documentos devem permanecer fora do diretório público da
aplicação. Um caminho privado absoluto é obrigatório para novos uploads.

O caminho pode ser definido pela variável de ambiente:

```text
EDOC_DOCUMENTOS_DIRETORIO=/var/lib/edoc/documentos
```

Sem essa configuração, os arquivos antigos ainda podem ser consultados pelo
controller, mas o sistema rejeita novos uploads para não gravá-los
acidentalmente em uma pasta pública.

## Preparação do diretório

Em um servidor Linux com PHP executado pelo usuário `www-data`:

```bash
sudo install -d -o www-data -g www-data -m 0770 /var/lib/edoc/documentos
```

Use o usuário e o grupo do serviço PHP configurado no servidor.

## Arquivos existentes

Registros antigos podem continuar usando caminhos como:

```text
uploads/documentos/15/arquivo.pdf
```

O controller reconhece esse formato e procura primeiro o arquivo no
armazenamento privado. Enquanto a migração não for concluída, ele também pode
ler o arquivo no diretório público legado.

Copie os arquivos preservando os diretórios dos documentos:

```bash
rsync -a uploads/documentos/ /var/lib/edoc/documentos/
```

Não remova os arquivos de origem antes de testar o acesso autenticado. Depois
da validação, mantenha um backup e defina a política de retenção antes da
remoção definitiva.

Novos uploads armazenam no banco somente o caminho relativo:

```text
15/arquivo.pdf
```

## Bloqueio do caminho antigo

O arquivo `uploads/documentos/.htaccess` bloqueia o acesso direto no Apache.
O acesso válido continua sendo realizado pelo controller autenticado.

Para Nginx, inclua no servidor virtual:

```nginx
location ^~ /uploads/documentos/ {
    deny all;
    return 404;
}
```

Após configurar o servidor, uma URL direta para um arquivo deve retornar
`403` ou `404`, enquanto a rota autenticada do e-Doc deve continuar funcionando.
