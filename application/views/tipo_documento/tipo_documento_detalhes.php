<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Detalhes do tipo de documento do sistema e-Doc">
    <title><?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?> | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1">Tipos de documento</h1>
                <p class="text-body-secondary mb-0">
                    Consulte os detalhes e gerencie os metadados vinculados ao tipo de documento.
                </p>
            </section>
        </header>

        <nav aria-label="Caminho do tipo de documento" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a class="text-decoration-none" href="<?= base_url('tipo_documento'); ?>">
                        <i class="fa-solid fa-folder-tree me-1" aria-hidden="true"></i>
                        Tipos de documento
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                </li>
            </ol>
        </nav>

        <section class="card border shadow-sm mb-4" aria-labelledby="titulo-tipo-documento">
            <article class="card-body p-3 p-lg-4">
                <header class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
                    <section>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span
                                class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-2"
                                aria-hidden="true">
                                <i class="fa-solid fa-folder-tree"></i>
                            </span>

                            <?php if ($tipo_documento['ativo'] == 1): ?>
                                <span class="badge text-bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inativo</span>
                            <?php endif; ?>
                        </div>

                        <h2 class="h4 mb-1" id="titulo-tipo-documento">
                            <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                        </h2>
                        <p class="text-body-secondary mb-0">
                            <?= !empty($tipo_documento['descricao'])
                                ? nl2br(htmlspecialchars($tipo_documento['descricao'], ENT_QUOTES, 'UTF-8'))
                                : 'Nenhuma descrição informada.'; ?>
                        </p>
                    </section>
                </header>

                <dl class="row border-top pt-3 mb-0">
                    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                        <dt class="small text-body-secondary fw-normal mb-1">Código</dt>
                        <dd class="fw-semibold mb-0"><?= (int) $tipo_documento['codigo']; ?></dd>
                    </div>
                    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                        <dt class="small text-body-secondary fw-normal mb-1">Metadados vinculados</dt>
                        <dd class="fw-semibold mb-0"><?= (int) $tipo_documento['total_metadados']; ?></dd>
                    </div>
                    <div class="col-sm-6 col-lg-3 mb-3 mb-sm-0">
                        <dt class="small text-body-secondary fw-normal mb-1">Documentos cadastrados</dt>
                        <dd class="fw-semibold mb-0"><?= (int) $tipo_documento['total_documentos']; ?></dd>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <dt class="small text-body-secondary fw-normal mb-1">Data de cadastro</dt>
                        <dd class="fw-semibold mb-0">
                            <?= date('d/m/Y H:i', strtotime($tipo_documento['cadastro'])); ?>
                        </dd>
                    </div>
                </dl>
            </article>
        </section>

        <section aria-labelledby="metadados-vinculados-title" class="card border shadow-sm">
            <div class="card-header bg-white d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 py-3">
                <div>
                    <h2 class="h6 fw-semibold mb-1" id="metadados-vinculados-title">Metadados vinculados</h2>
                    <p class="small text-secondary mb-0">
                        Defina a ordem e o comportamento dos campos deste tipo de documento.
                    </p>
                </div>

                <button class="btn btn-primary flex-shrink-0" type="button" id="novo-vinculo"
                    <?= empty($metadados_disponiveis) ? 'disabled' : ''; ?>>
                    <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                    Vincular metadado
                </button>
            </div>

            <?php if (empty($metadados_disponiveis) && !empty($metadados_vinculados)): ?>
                <div class="alert alert-light border-0 border-bottom rounded-0 mb-0 py-2">
                    Todos os metadados ativos já estão vinculados a este tipo de documento.
                </div>
            <?php endif; ?>

            <?php if ($metadados_vinculados): ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3 text-center" scope="col">Ordem</th>
                                <th class="py-3" scope="col">Metadado</th>
                                <th class="py-3" scope="col">Tipo de campo</th>
                                <th class="py-3 text-center" scope="col">Obrigatório</th>
                                <th class="py-3 text-center" scope="col">Visível</th>
                                <th class="py-3 text-center" scope="col">Pesquisável</th>
                                <th class="px-3 py-3 text-end" scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metadados_vinculados as $metadado): ?>
                                <tr>
                                    <td class="text-center fw-semibold"><?= (int) $metadado['ordem']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <span
                                                class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-2"
                                                aria-hidden="true">
                                                <i class="fa-solid fa-tag"></i>
                                            </span>
                                            <span>
                                                <span class="d-block fw-semibold">
                                                    <?= htmlspecialchars($metadado['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                                <?php if ($metadado['ativo'] != 1): ?>
                                                    <small class="d-block text-body-secondary">Metadado inativo</small>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light border fw-normal font-monospace">
                                            <?= htmlspecialchars($metadado['tipo_campo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <i class="fa-solid <?= $metadado['obrigatorio'] == 1 ? 'fa-circle-check text-success' : 'fa-circle-minus text-secondary'; ?>"
                                            aria-label="<?= $metadado['obrigatorio'] == 1 ? 'Sim' : 'Não'; ?>"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="fa-solid <?= $metadado['visivel'] == 1 ? 'fa-circle-check text-success' : 'fa-circle-minus text-secondary'; ?>"
                                            aria-label="<?= $metadado['visivel'] == 1 ? 'Sim' : 'Não'; ?>"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="fa-solid <?= $metadado['pesquisavel'] == 1 ? 'fa-circle-check text-success' : 'fa-circle-minus text-secondary'; ?>"
                                            aria-label="<?= $metadado['pesquisavel'] == 1 ? 'Sim' : 'Não'; ?>"></i>
                                    </td>
                                    <td class="text-end pe-3 pe-lg-4">
                                        <button class="btn btn-sm btn-light border editar-vinculo" type="button"
                                            data-codigo="<?= (int) $metadado['metadado_codigo']; ?>"
                                            data-nome="<?= htmlspecialchars($metadado['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-ordem="<?= (int) $metadado['ordem']; ?>"
                                            data-obrigatorio="<?= (int) $metadado['obrigatorio']; ?>"
                                            data-visivel="<?= (int) $metadado['visivel']; ?>"
                                            data-pesquisavel="<?= (int) $metadado['pesquisavel']; ?>"
                                            aria-label="Editar vínculo com <?= htmlspecialchars($metadado['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                        </button>

                                        <button class="btn btn-sm btn-light border text-danger remover-vinculo" type="button"
                                            data-codigo="<?= (int) $metadado['metadado_codigo']; ?>"
                                            data-nome="<?= htmlspecialchars($metadado['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                            aria-label="Desvincular <?= htmlspecialchars($metadado['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa-solid fa-link-slash" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fa-solid fa-tags fa-2x text-secondary" aria-hidden="true"></i>
                    </div>
                    <h3 class="h5 fw-semibold">Nenhum metadado vinculado</h3>
                    <p class="text-secondary mb-4">
                        Vincule os campos que deverão ser preenchidos nos documentos deste tipo.
                    </p>
                    <?php if ($metadados_disponiveis): ?>
                        <button class="btn btn-primary" type="button" id="primeiro-vinculo">
                            <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                            Vincular metadado
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <div class="modal fade" id="modal-vinculo" tabindex="-1" aria-labelledby="titulo-modal-vinculo"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="form-vinculo" method="post">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="titulo-modal-vinculo">Vincular metadado</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="alerta-vinculo" role="alert"></div>

                        <div class="mb-3" id="campo-metadado-select">
                            <label class="form-label" for="metadado-codigo">Metadado</label>
                            <select class="form-select" id="metadado-codigo" name="metadado_codigo" required>
                                <option value="">Selecione</option>
                                <?php foreach ($metadados_disponiveis as $metadado): ?>
                                    <option value="<?= (int) $metadado['codigo']; ?>">
                                        <?= htmlspecialchars($metadado['nome'], ENT_QUOTES, 'UTF-8'); ?> —
                                        <?= htmlspecialchars($metadado['tipo_campo'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="campo-metadado-texto">
                            <label class="form-label" for="metadado-nome">Metadado</label>
                            <input class="form-control" type="text" id="metadado-nome" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="ordem">Ordem</label>
                            <input class="form-control" type="number" id="ordem" name="ordem" min="1" max="65535"
                                required>
                        </div>

                        <input type="hidden" name="obrigatorio" value="0">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="obrigatorio" name="obrigatorio"
                                value="1">
                            <label class="form-check-label" for="obrigatorio">Obrigatório</label>
                        </div>

                        <input type="hidden" name="visivel" value="0">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="visivel" name="visivel" value="1">
                            <label class="form-check-label" for="visivel">Visível no formulário</label>
                        </div>

                        <input type="hidden" name="pesquisavel" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pesquisavel" name="pesquisavel"
                                value="1">
                            <label class="form-check-label" for="pesquisavel">Disponível para pesquisa</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light border" type="button" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" type="submit" id="salvar-vinculo">Salvar vínculo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-remover" tabindex="-1" aria-labelledby="modal-remover-title"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-link-slash text-danger fa-2x" aria-hidden="true"></i>
                    </div>
                    <h2 class="modal-title fs-5 mb-2" id="modal-remover-title">Desvincular metadado?</h2>
                    <p class="text-body-secondary mb-2">Você está prestes a desvincular:</p>
                    <p class="fw-semibold mb-3" id="nome-remocao"></p>
                    <div class="alert alert-danger text-start d-none" id="alerta-remocao" role="alert"></div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-light border" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger" type="button" id="confirmar-remocao">Desvincular</button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="true">
        <div id="toast-feedback" class="toast border-0 shadow" role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-body d-flex align-items-center gap-3">
                <i id="toast-icone" class="fa-solid fa-circle-check text-success fs-5" aria-hidden="true"></i>
                <span id="toast-mensagem" class="flex-grow-1"> </span>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <?php $this->load->view('js'); ?>

    <script>
        const base_url = '<?= base_url(); ?>';

        $(document).ready(function () {
            const tipo_documento_codigo =
                <?= (int) $tipo_documento['codigo']; ?>;

            let metadado_edicao = null;
            let metadado_remocao = null;

            const modal_vinculo = new bootstrap.Modal(
                document.getElementById('modal-vinculo')
            );

            const modal_remover = new bootstrap.Modal(
                document.getElementById('modal-remover')
            );

            function abrir_novo_vinculo() {
                metadado_edicao = null;

                $('#form-vinculo')[0].reset();
                $('#titulo-modal-vinculo').text(
                    'Vincular metadado'
                );
                $('#campo-metadado-select')
                    .removeClass('d-none');
                $('#campo-metadado-texto')
                    .addClass('d-none');
                $('#metadado-codigo')
                    .prop('disabled', false);
                $('#ordem').val(
                    <?= (int) $proxima_ordem; ?>
                );
                $('#visivel, #pesquisavel')
                    .prop('checked', true);
                $('#alerta-vinculo')
                    .empty()
                    .addClass('d-none');

                modal_vinculo.show();
            }

            $('#novo-vinculo, #primeiro-vinculo')
                .on('click', function () {
                    abrir_novo_vinculo();
                });

            $('.editar-vinculo').on('click', function () {
                const botao = $(this);

                metadado_edicao =
                    botao.data('codigo');

                $('#form-vinculo')[0].reset();
                $('#titulo-modal-vinculo').text(
                    'Editar vínculo'
                );
                $('#campo-metadado-select')
                    .addClass('d-none');
                $('#campo-metadado-texto')
                    .removeClass('d-none');
                $('#metadado-codigo')
                    .prop('disabled', true);
                $('#metadado-nome').val(
                    botao.data('nome')
                );
                $('#ordem').val(
                    botao.data('ordem')
                );
                $('#obrigatorio').prop(
                    'checked',
                    Number(
                        botao.data('obrigatorio')
                    ) === 1
                );
                $('#visivel').prop(
                    'checked',
                    Number(
                        botao.data('visivel')
                    ) === 1
                );
                $('#pesquisavel').prop(
                    'checked',
                    Number(
                        botao.data('pesquisavel')
                    ) === 1
                );
                $('#alerta-vinculo')
                    .empty()
                    .addClass('d-none');

                modal_vinculo.show();
            });

            $('#form-vinculo').on('submit', function (e) {
                e.preventDefault();

                const url = metadado_edicao
                    ? base_url +
                        'tipo_documento/atualizar_metadado/' +
                        tipo_documento_codigo +
                        '/' +
                        metadado_edicao
                    : base_url +
                        'tipo_documento/vincular_metadado/' +
                        tipo_documento_codigo;

                $('#salvar-vinculo')
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
                    );

                $('#alerta-vinculo')
                    .empty()
                    .addClass('d-none');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(
                            response.dados?.erros ||
                            response.mensagem?.conteudo,
                            'alerta-vinculo'
                        );

                        return;
                    }

                    sessionStorage.setItem(
                        'feedback',
                        response.mensagem?.conteudo
                    );

                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(
                        xhr,
                        'alerta-vinculo'
                    );
                }).always(function () {
                    $('#salvar-vinculo')
                        .prop('disabled', false)
                        .html('Salvar vínculo');
                });
            });

            $('.remover-vinculo').on('click', function () {
                metadado_remocao =
                    $(this).data('codigo');

                $('#nome-remocao').text(
                    $(this).data('nome')
                );

                $('#alerta-remocao')
                    .empty()
                    .addClass('d-none');

                modal_remover.show();
            });

            $('#confirmar-remocao').on('click', function () {
                $('#confirmar-remocao')
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
                    );

                $('#alerta-remocao')
                    .empty()
                    .addClass('d-none');

                $.ajax({
                    url:
                        base_url +
                        'tipo_documento/desvincular_metadado/' +
                        tipo_documento_codigo +
                        '/' +
                        metadado_remocao,
                    method: 'POST',
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(
                            response.dados?.erros ||
                            response.mensagem?.conteudo,
                            'alerta-remocao'
                        );

                        return;
                    }

                    sessionStorage.setItem(
                        'feedback',
                        response.mensagem?.conteudo
                    );

                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(
                        xhr,
                        'alerta-remocao'
                    );
                }).always(function () {
                    $('#confirmar-remocao')
                        .prop('disabled', false)
                        .html('Desvincular');
                });
            });

            const feedback =
                sessionStorage.getItem('feedback');

            if (feedback) {
                sessionStorage.removeItem('feedback');
                mostrar_feedback(feedback, 'success');
            }
        });
    </script>
</body>

</html>
