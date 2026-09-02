<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Detalhes do documento no sistema e-Doc">
    <title><?= htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?> | e-Doc</title>
    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1"><?= htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="text-body-secondary mb-0">
                    <?= htmlspecialchars($documento['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </section>
            <div>
                <a class="btn btn-light border" id="voltar">Voltar</a>  
            </div>
        </header>

        <nav aria-label="Caminho do documento" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('documento'); ?>">Documentos</a></li>
                <li class="breadcrumb-item active">Detalhes</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <section class="card border shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-semibold mb-4">Dados do documento</h2>
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-body-secondary">Protocolo</dt>
                            <dd class="col-sm-8">
                                <span class="font-monospace fw-semibold">
                                    <?= htmlspecialchars($documento['protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary">Identificação</dt>
                            <dd class="col-sm-8">
                                <?= htmlspecialchars($documento['numero_identificacao'] ?? 'Não informada', ENT_QUOTES, 'UTF-8'); ?>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary">Data do documento</dt>
                            <dd class="col-sm-8">
                                <?= $documento['data_documento'] ? date('d/m/Y', strtotime($documento['data_documento'])) : 'Não informada'; ?>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary">Status</dt>
                            <dd class="col-sm-8">
                                <span class="badge <?= $documento['ativo'] ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                    <?= $documento['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary">Descrição</dt>
                            <dd class="col-sm-8 mb-0">
                                <?= nl2br(htmlspecialchars($documento['descricao'] ?? 'Não informada', ENT_QUOTES, 'UTF-8')); ?>
                            </dd>
                        </dl>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-4">
                <section class="card border shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-semibold mb-4">Localização e cadastro</h2>
                        <p class="small text-body-secondary mb-1">Localização</p>
                        <p>
                            <?php foreach ($caminho_localizacao as $indice => $localizacao): ?>
                                <?= $indice ? ' / ' : ''; ?>
                                <?= htmlspecialchars($localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php endforeach; ?>
                        </p>
                        <p class="small text-body-secondary mb-1">Cadastro</p>
                        <p class="mb-0"><?= date('d/m/Y H:i', strtotime($documento['cadastro'])); ?></p>
                    </div>
                </section>
            </div>

            <div class="col-12">
                <section class="card border shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-semibold mb-4">Metadados</h2>
                        <?php if ($metadados): ?>
                            <dl class="row mb-0">
                                <?php foreach ($metadados as $metadado): ?>
                                    <dt class="col-sm-4 text-body-secondary">
                                        <?= htmlspecialchars($metadado['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </dt>
                                    <dd class="col-sm-8">
                                        <?= htmlspecialchars($metadado['valor'], ENT_QUOTES, 'UTF-8'); ?>
                                    </dd>
                                <?php endforeach; ?>
                            </dl>
                        <?php else: ?>
                            <p class="text-body-secondary mb-0">Nenhum metadado preenchido.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <div class="col-12">
                <section class="card border shadow-sm">
                    <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between gap-3 py-3">
                        <div>
                            <h2 class="h6 fw-semibold mb-1">Arquivos</h2>
                            <p class="small text-secondary mb-0">Arquivos digitais vinculados ao documento.</p>
                        </div>
                        <?php if ($this->controle_acesso->tem_permissao('arquivos.gerenciar')): ?>
                            <form action="<?= base_url('documento/cadastrar_arquivo/' . $documento['codigo']); ?>"
                                id="formulario-arquivo" method="post" enctype="multipart/form-data">
                                <div class="input-group mb-3">
                                    <input accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png"
                                        class="form-control" id="arquivo" name="arquivo" required
                                        type="file" aria-describedby="enviar-arquivo">
                                    <button class="btn btn-primary" id="enviar-arquivo" type="submit">
                                        <i class="fa-solid fa-upload me-2"></i>Enviar
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="card-body p-0">
                        <div id="alerta-arquivo" class="alert alert-danger m-3 d-none"></div>
                        <?php if ($arquivos): ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light text-uppercase">
                                        <tr class="small text-secondary">
                                            <th class="px-3 py-3">Arquivo</th>
                                            <th>Tipo</th>
                                            <th>Tamanho</th>
                                            <th>Versão atual</th>
                                            <th class="px-3 text-end">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($arquivos as $arquivo): ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <?php if ($this->controle_acesso->tem_permissao('arquivos.visualizar')): ?>
                                                        <a class="text-decoration-none fw-semibold" href="<?= base_url(
                                                            'documento/acessar_arquivo/' .
                                                            $documento['codigo'] . '/' .
                                                            $arquivo['codigo']
                                                        ); ?>" target="_blank">
                                                            <?= htmlspecialchars($arquivo['nome_original'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($arquivo['nome_original'], ENT_QUOTES, 'UTF-8'); ?>
                                                    <?php endif; ?>
                                                    <?php if ($arquivo['principal']): ?>
                                                        <span class="badge text-bg-primary ms-2">Principal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= strtoupper(htmlspecialchars($arquivo['extensao'], ENT_QUOTES, 'UTF-8')); ?></td>
                                                <td><?= number_format($arquivo['tamanho'] / 1024, 1, ',', '.'); ?> KB</td>
                                                <td>
                                                    <span class="badge text-bg-secondary">
                                                        v<?= (int) $arquivo['versao']; ?>
                                                    </span>
                                                </td>
                                                <td class="pe-3 text-end">
                                                    <?php if ($this->controle_acesso->tem_permissao('arquivos.visualizar')): ?>
                                                        <button class="btn btn-sm btn-light border historico-versoes"
                                                            data-codigo="<?= $arquivo['codigo']; ?>" type="button"
                                                            title="Visualizar histórico">
                                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($this->controle_acesso->tem_permissao('arquivos.gerenciar')): ?>
                                                        <button class="btn btn-sm btn-light border nova-versao"
                                                            data-codigo="<?= $arquivo['codigo']; ?>"
                                                            data-nome="<?= htmlspecialchars($arquivo['nome_original'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            type="button" title="Enviar nova versão">
                                                            <i class="fa-solid fa-upload"></i>
                                                        </button>
                                                        <?php if (!$arquivo['principal']): ?>
                                                            <button class="btn btn-sm btn-light border arquivo-principal"
                                                                data-codigo="<?= $arquivo['codigo']; ?>" type="button"
                                                                title="Definir como principal">
                                                                <i class="fa-solid fa-star"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button class="btn btn-sm btn-light border text-danger excluir-arquivo"
                                                            data-codigo="<?= $arquivo['codigo']; ?>" type="button"
                                                            title="Excluir arquivo e seu histórico">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-body-secondary text-center py-4 mb-0">Nenhum arquivo enviado.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <div class="col-12">
                <section class="card border shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h2 class="h6 fw-semibold mb-1">Movimentações</h2>
                        <p class="small text-secondary mb-0">Histórico de alterações da localização.</p>
                    </div>
                    <?php if ($movimentacoes): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light text-uppercase">
                                    <tr class="small text-secondary">
                                        <th class="px-3 py-3">Data</th>
                                        <th>Tipo</th>
                                        <th>Origem</th>
                                        <th>Destino</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($movimentacoes as $movimentacao): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <?= date('d/m/Y H:i', strtotime($movimentacao['data_movimentacao'])); ?>
                                            </td>
                                            <td><?= ucfirst(strtolower($movimentacao['tipo_movimentacao'])); ?></td>
                                            <td><?= htmlspecialchars($movimentacao['localizacao_origem'] ?? 'Não se aplica', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($movimentacao['localizacao_destino'] ?? 'Não informada', ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-body-secondary text-center py-4 mb-0">Nenhuma movimentação registrada.</p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>

    <?php if ($this->controle_acesso->tem_permissao('arquivos.gerenciar')): ?>
        <div class="modal fade" id="modal-nova-versao" tabindex="-1"
            aria-labelledby="titulo-modal-nova-versao" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="formulario-nova-versao" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title fs-5" id="titulo-modal-nova-versao">
                                    Enviar nova versão
                                </h2>
                                <p class="small text-body-secondary mb-0" id="nome-arquivo-versao"></p>
                            </div>
                            <button class="btn-close" data-bs-dismiss="modal" type="button"
                                aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger d-none" id="alerta-nova-versao"></div>

                            <label class="form-label" for="arquivo-versao">Arquivo</label>
                            <input accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png"
                                class="form-control" id="arquivo-versao" name="arquivo"
                                required type="file">
                            <p class="form-text mb-0">
                                O arquivo anterior permanecerá disponível no histórico.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light border" data-bs-dismiss="modal" type="button">
                                Cancelar
                            </button>
                            <button class="btn btn-primary" id="enviar-nova-versao" type="submit">
                                <i class="fa-solid fa-upload me-2"></i>Enviar versão
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($this->controle_acesso->tem_permissao('arquivos.visualizar')): ?>
        <div class="modal fade" id="modal-historico-versoes" tabindex="-1"
            aria-labelledby="titulo-modal-historico-versoes" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="titulo-modal-historico-versoes">
                            Histórico de versões
                        </h2>
                        <button class="btn-close" data-bs-dismiss="modal" type="button"
                            aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="alert alert-danger d-none m-3"
                            id="alerta-historico-versoes"></div>
                        <div id="conteudo-historico-versoes"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast-feedback" class="toast border-0 shadow">
            <div class="toast-body d-flex align-items-center gap-3">
                <i id="toast-icone" class="fa-solid fa-circle-check text-success fs-5"></i>
                <span id="toast-mensagem" class="flex-grow-1"></span>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <?php $this->load->view('js'); ?>

    <script>
        const base_url = '<?= base_url(); ?>';
        const documento_codigo = <?= (int) $documento['codigo']; ?>;

        $(document).ready(function () {
            const modal_nova_versao = $('#modal-nova-versao').length
                ? bootstrap.Modal.getOrCreateInstance($('#modal-nova-versao')[0])
                : null;

            const modal_historico_versoes = $('#modal-historico-versoes').length
                ? bootstrap.Modal.getOrCreateInstance($('#modal-historico-versoes')[0])
                : null;

            const feedback = sessionStorage.getItem('feedback');

            if (feedback) {
                sessionStorage.removeItem('feedback');
                mostrar_feedback(feedback, 'success');
            }

            $('#formulario-arquivo').on('submit', function (e) {
                e.preventDefault();

                const dados = new FormData(this);
                const texto_botao = '<i class="fa-solid fa-upload me-2"></i>Enviar';

                $('#enviar-arquivo')
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');

                $('#alerta-arquivo').empty().addClass('d-none');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: dados,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(
                            response.dados?.erros || response.mensagem?.conteudo,
                            'alerta-arquivo'
                        );
                        return;
                    }

                    sessionStorage.setItem('feedback', response.mensagem?.conteudo);
                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-arquivo');
                }).always(function () {
                    $('#enviar-arquivo').prop('disabled', false).html(texto_botao);
                });
            });

            $('.nova-versao').on('click', function () {
                const arquivo_codigo = $(this).data('codigo');

                $('#formulario-nova-versao').attr(
                    'action',
                    base_url + 'documento/cadastrar_versao/' +
                        documento_codigo + '/' + arquivo_codigo
                );

                $('#nome-arquivo-versao').text($(this).data('nome'));
                $('#arquivo-versao').val('');
                $('#alerta-nova-versao').empty().addClass('d-none');

                modal_nova_versao.show();
            });

            $('#formulario-nova-versao').on('submit', function (e) {
                e.preventDefault();

                const formulario = $(this);
                const dados = new FormData(this);
                const texto_botao = '<i class="fa-solid fa-upload me-2"></i>Enviar versão';

                $('#enviar-nova-versao')
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');

                $('#alerta-nova-versao').empty().addClass('d-none');

                $.ajax({
                    url: formulario.attr('action'),
                    method: 'POST',
                    data: dados,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(
                            response.dados?.erros || response.mensagem?.conteudo,
                            'alerta-nova-versao'
                        );
                        return;
                    }

                    sessionStorage.setItem('feedback', response.mensagem?.conteudo);
                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-nova-versao');
                }).always(function () {
                    $('#enviar-nova-versao')
                        .prop('disabled', false)
                        .html(texto_botao);
                });
            });

            $('.historico-versoes').on('click', function () {
                const arquivo_codigo = $(this).data('codigo');

                $('#alerta-historico-versoes').empty().addClass('d-none');
                $('#conteudo-historico-versoes').html(
                    '<div class="text-center py-5">' +
                        '<span class="spinner-border text-primary" aria-hidden="true"></span>' +
                        '<span class="visually-hidden">Carregando...</span>' +
                    '</div>'
                );

                modal_historico_versoes.show();

                $.ajax({
                    url: base_url + 'documento/historico_versoes/' +
                        documento_codigo + '/' + arquivo_codigo,
                    method: 'GET',
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        $('#conteudo-historico-versoes').empty();
                        mostrar_erros(
                            response.dados?.erros || response.mensagem?.conteudo,
                            'alerta-historico-versoes'
                        );
                        return;
                    }

                    $('#conteudo-historico-versoes').html(response.dados?.html || '');
                }).fail(function (xhr) {
                    $('#conteudo-historico-versoes').empty();
                    mostrar_erro_ajax(xhr, 'alerta-historico-versoes');
                });
            });

            $('.arquivo-principal').on('click', function () {
                const botao = $(this);
                const arquivo_codigo = botao.data('codigo');

                botao
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');

                $('#alerta-arquivo').empty().addClass('d-none');

                $.ajax({
                    url: base_url + 'documento/definir_arquivo_principal/' + documento_codigo + '/' + arquivo_codigo,
                    method: 'POST',
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(
                            response.dados?.erros || response.mensagem?.conteudo,
                            'alerta-arquivo'
                        );
                        return;
                    }

                    sessionStorage.setItem('feedback', response.mensagem?.conteudo);
                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-arquivo');
                }).always(function () {
                    botao.prop('disabled', false).html('<i class="fa-solid fa-star"></i>');
                });
            });

            $('.excluir-arquivo').on('click', function () {
                const botao = $(this);
                const arquivo_codigo = botao.data('codigo');

                if (!window.confirm('Deseja excluir este arquivo e todo o histórico de versões?')) {
                    return;
                }

                botao
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');

                $('#alerta-arquivo').empty().addClass('d-none');

                $.ajax({
                    url: base_url + 'documento/excluir_arquivo/' + documento_codigo + '/' + arquivo_codigo,
                    method: 'POST',
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(
                            response.dados?.erros || response.mensagem?.conteudo,
                            'alerta-arquivo'
                        );
                        return;
                    }

                    sessionStorage.setItem('feedback', response.mensagem?.conteudo);
                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-arquivo');
                }).always(function () {
                    botao.prop('disabled', false).html('<i class="fa-solid fa-trash-can"></i>');
                });
            });

            $('#voltar').on('click', function () {
                if (window.history.length > 1) {
                    window.history.back();
                    return;
                }

                window.location = base_url + 'documento';
            });
        });
    </script>
</body>

</html>
