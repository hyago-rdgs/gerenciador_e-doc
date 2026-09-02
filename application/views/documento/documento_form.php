<?php $modo_edicao = !empty($documento) ? TRUE : FALSE; ?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Formulário de documento do sistema e-Doc">
    <title><?= $modo_edicao ? 'Editar documento' : 'Novo documento'; ?> | e-Doc</title>
    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="mb-4">
            <h1 class="h3 mb-1"><?= $modo_edicao ? 'Editar documento' : 'Novo documento'; ?></h1>
            <p class="text-body-secondary mb-0">
                <?= $modo_edicao ? 'Atualize as informações deste documento.' : 'Cadastre um documento e seus metadados.'; ?>
            </p>
        </header>

        <nav aria-label="Caminho do documento" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a class="text-decoration-none" href="<?= base_url('documento'); ?>">
                        <i class="fa-regular fa-file-lines me-1" aria-hidden="true"></i>
                        Documentos
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= $modo_edicao ? 'Editar' : 'Cadastrar'; ?>
                </li>
            </ol>
        </nav>

        <form id="formulario" method="post" novalidate>
            <section class="card border shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-semibold mb-1">Dados do documento</h2>
                    <p class="small text-body-secondary mb-3">Informe os dados utilizados para identificar e localizar o
                        documento.</p>
                    <div id="alerta-formulario" class="alert alert-danger d-none" role="alert"></div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label" for="titulo">Título</label>
                            <input class="form-control" id="titulo" maxlength="255" name="titulo" required type="text"
                                value="<?= htmlspecialchars($documento['titulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label" for="numero_identificacao">Número de identificação</label>
                            <input class="form-control" id="numero_identificacao" maxlength="100"
                                name="numero_identificacao" type="text"
                                value="<?= htmlspecialchars($documento['numero_identificacao'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <?php if (!$modo_edicao && !empty($localizacao_selecionada)): ?>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label">Tipo de documento</label>
                                <div class="form-control bg-body-tertiary">
                                    <?= htmlspecialchars($tipo_documento_selecionado['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <input id="tipo_documento_codigo" name="tipo_documento_codigo" type="hidden"
                                    value="<?= $tipo_documento_selecionado['tipo_documento_codigo']; ?>">
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label">Localização</label>
                                <div class="form-control bg-body-tertiary">
                                    <?= htmlspecialchars(
                                        $localizacao_selecionada['classificacao']
                                        . ' - '
                                        . $localizacao_selecionada['nome']
                                        . (
                                            !empty($localizacao_selecionada['tipo_documento'])
                                            ? ' (' . $localizacao_selecionada['tipo_documento'] . ')'
                                            : ''
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </div>
                                <input id="localizacao_codigo" name="localizacao_codigo" type="hidden"
                                    value="<?= $localizacao_selecionada['codigo']; ?>">
                            </div>
                        <?php elseif ($modo_edicao): ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="tipo_documento_codigo">Tipo de documento</label>
                                <select class="form-select" id="tipo_documento_codigo" name="tipo_documento_codigo"
                                    required>
                                    <option value="">Selecione</option>
                                    <?php foreach ($tipos_documento as $tipo_documento): ?>
                                        <option value="<?= $tipo_documento['codigo']; ?>"
                                            <?= ($documento['tipo_documento_codigo'] ?? '') == $tipo_documento['codigo'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label">Localização atual</label>
                                <div class="form-control bg-body-tertiary">
                                    <?= htmlspecialchars(
                                        $documento['localizacao_classificacao'] . ' - ' . $documento['localizacao'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </div>
                                <input id="localizacao_codigo" name="localizacao_codigo" type="hidden"
                                    value="<?= $documento['localizacao_codigo']; ?>">
                                <div class="form-text">
                                    Para alterar a localização, utilize a ação Transferir nos detalhes do documento.
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="tipo_documento_codigo">Tipo de documento</label>
                                <select class="form-select" id="tipo_documento_codigo" name="tipo_documento_codigo"
                                    required>
                                    <option value="">Selecione</option>
                                    <?php foreach ($tipos_documento as $tipo_documento): ?>
                                        <option value="<?= $tipo_documento['codigo']; ?>"
                                            <?= ($documento['tipo_documento_codigo'] ?? '') == $tipo_documento['codigo'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="localizacao_codigo">Localização</label>
                                <select class="form-select" id="localizacao_codigo" name="localizacao_codigo" required>
                                    <option value="">Selecione</option>
                                    <?php foreach ($localizacoes as $localizacao): ?>
                                        <option value="<?= $localizacao['codigo']; ?>" <?= ($documento['localizacao_codigo'] ?? '') == $localizacao['codigo'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars(
                                                $localizacao['classificacao']
                                                . ' - '
                                                . $localizacao['nome']
                                                . (
                                                    !empty($localizacao['tipo_documento'])
                                                    ? ' (' . $localizacao['tipo_documento'] . ')'
                                                    : ''
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    A localização precisa estar configurada para o tipo de documento selecionado.
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label" for="data_documento">Data do documento</label>
                            <input class="form-control" id="data_documento" name="data_documento" type="date"
                                value="<?= htmlspecialchars($documento['data_documento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="descricao">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao"
                                rows="3"><?= htmlspecialchars($documento['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card border shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h5 fw-semibold mb-1">Metadados</h2>
                    <p class="small text-body-secondary mb-3">Os campos são carregados de acordo com o tipo selecionado.
                    </p>

                    <div id="campos-metadados" class="row g-3">
                        <div class="col-12 text-body-secondary">Selecione um tipo de documento.</div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                        <button class="btn btn-light border" id="voltar" type="button">Voltar</button>
                        <button class="btn btn-primary" id="salvar" type="submit">
                            <?= $modo_edicao ? 'Salvar alterações' : 'Revisar cadastro'; ?>
                        </button>
                    </div>
                </div>
            </section>
        </form>
    </main>

    <?php if (!$modo_edicao): ?>
        <div class="modal fade" id="modalConfirmarDocumento" tabindex="-1" aria-labelledby="modalConfirmarDocumentoLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title fs-5" id="modalConfirmarDocumentoLabel">Confirmar cadastro</h2>
                            <p class="small text-body-secondary mb-0">Revise as informações antes de cadastrar.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body" id="conteudo-revisao"></div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                            Voltar e corrigir
                        </button>
                        <button type="button" class="btn btn-primary" id="confirmar-cadastro">
                            Confirmar cadastro
                        </button>
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
        const documento_codigo = <?= $modo_edicao ? (int) $documento['codigo'] : 'null'; ?>;
        const modo_edicao = <?= $modo_edicao ? 'true' : 'false'; ?>;
        let formulario_pendente = null;

        $(document).ready(function () {
            function carregar_metadados() {
                const tipo_documento_codigo = $('#tipo_documento_codigo').val();

                if (!tipo_documento_codigo) {
                    $('#campos-metadados').html(
                        '<div class="col-12 text-body-secondary">Selecione um tipo de documento.</div>'
                    );
                    return;
                }

                $('#campos-metadados').html(
                    '<div class="col-12"><span class="spinner-border spinner-border-sm me-2"></span>Carregando campos...</div>'
                );

                let url = base_url + 'documento/campos_metadados/' + tipo_documento_codigo;

                if (documento_codigo) {
                    url += '/' + documento_codigo;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(
                            response.dados?.erros || response.mensagem?.conteudo,
                            'alerta-formulario'
                        );
                        return;
                    }

                    $('#campos-metadados').html(response.dados.html);
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-formulario');
                });
            }

            function enviar_formulario(confirmar = false) {
                const texto_botao = modo_edicao ? 'Salvar alterações' : 'Revisar cadastro';
                let dados = formulario_pendente || $('#formulario').serialize();

                if (confirmar) {
                    dados += '&confirmar=1';
                }

                $('#salvar')
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');

                $('#alerta-formulario').empty().addClass('d-none');

                if (confirmar) {
                    $('#confirmar-cadastro')
                        .prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
                }

                $.ajax({
                    url: base_url + '<?= uri_string(); ?>',
                    method: 'POST',
                    data: dados,
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(
                            response.dados?.erros || response.mensagem?.conteudo,
                            'alerta-formulario'
                        );
                        return;
                    }

                    if (!modo_edicao && response.dados?.confirmacao) {
                        $('#conteudo-revisao').html(response.dados.html);

                        bootstrap.Modal
                            .getOrCreateInstance(document.getElementById('modalConfirmarDocumento'))
                            .show();
                        return;
                    }

                    sessionStorage.setItem(
                        'feedback',
                        response.mensagem?.conteudo
                    );

                    window.location =
                        base_url + 'documento/detalhes/' + response.dados.codigo;
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-formulario');
                }).always(function () {
                    $('#salvar').prop('disabled', false).html(texto_botao);

                    if (!modo_edicao) {
                        $('#confirmar-cadastro')
                            .prop('disabled', false)
                            .html('Confirmar cadastro');
                    }
                });
            }

            carregar_metadados();

            $('#tipo_documento_codigo').on('change', carregar_metadados);

            $('#voltar').on('click', function () {
                if (window.history.length > 1) {
                    window.history.back();
                    return;
                }

                window.location = base_url + 'documento';
            });

            $('#formulario').on('submit', function (e) {
                e.preventDefault();
                formulario_pendente = $(this).serialize();
                enviar_formulario(false);
            });

            $('#confirmar-cadastro').on('click', function () {
                enviar_formulario(true);
            });
        });
    </script>
</body>

</html>
