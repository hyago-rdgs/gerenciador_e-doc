<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Listagem de documentos do sistema e-Doc">
    <title>Documentos | e-Doc</title>
    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1">Documentos</h1>
                <p class="text-body-secondary mb-0">Consulte e gerencie os documentos cadastrados.</p>
            </section>
            <?php if ($this->controle_acesso->tem_permissao('documentos.gerenciar')): ?>
                <a class="btn btn-primary flex-shrink-0" href="<?= base_url('documento/cadastrar'); ?>">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Novo documento
                </a>
            <?php endif; ?>
        </header>

        <nav aria-label="Caminho do documento" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fa-regular fa-file-lines me-1"></i>
                    Documentos
                </li>
            </ol>
        </nav>

        <section class="card border shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Filtros</h2>
                <form method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label class="form-label" for="termo">Buscar documento</label>
                            <input class="form-control" id="termo" name="termo"
                                placeholder="Título, identificação ou protocolo" type="search"
                                value="<?= htmlspecialchars($filtro_termo, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-md-4 col-lg-2">
                            <label class="form-label" for="tipo_documento_codigo">Tipo</label>
                            <select class="form-select" id="tipo_documento_codigo" name="tipo_documento_codigo">
                                <option value="">Todos</option>
                                <?php foreach ($tipos_documento as $tipo_documento): ?>
                                    <option value="<?= $tipo_documento['codigo']; ?>"
                                        <?= $filtro_tipo_documento == $tipo_documento['codigo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4 col-lg-2">
                            <label class="form-label" for="localizacao_codigo">Localização</label>
                            <select class="form-select" id="localizacao_codigo" name="localizacao_codigo">
                                <option value="">Todas</option>
                                <?php foreach ($localizacoes as $localizacao): ?>
                                    <option value="<?= $localizacao['codigo']; ?>"
                                        <?= $filtro_localizacao == $localizacao['codigo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($localizacao['classificacao'] . ' - ' . $localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4 col-lg-2">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="ativo" <?= $filtro_status == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="inativo" <?= $filtro_status == 'inativo' ? 'selected' : ''; ?>>Inativo
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-lg-2">
                            <div class="d-flex gap-2">
                                <a class="btn btn-primary flex-fill" role="button" id="filtrar">
                                    <i class="fa-solid fa-filter me-2"></i>Filtrar
                                </a>
                                <a class="btn btn-light border flex-fill" id="limpar_filtro" role="button">Limpar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <?php if ($documentos): ?>
            <section class="card border shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-semibold mb-1">Documentos cadastrados</h2>
                    <p class="small text-secondary mb-0"><?= $total_documentos; ?> documentos encontrados</p>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3">Documento</th>
                                <th>Tipo</th>
                                <th>Localização</th>
                                <th class="text-center">Status</th>
                                <th class="px-3 text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $documento): ?>
                                <tr>
                                    <td class="ps-3 ps-lg-4">
                                        <a class="text-decoration-none fw-semibold"
                                            href="<?= base_url('documento/detalhes/' . $documento['codigo']); ?>">
                                            <?= htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                            <small class="d-block text-body-secondary fw-normal font-monospace">
                                                <?= htmlspecialchars($documento['protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                            <small class="d-block text-body-secondary fw-normal">
                                                <?= htmlspecialchars($documento['numero_identificacao'] ?? 'Sem identificação', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($documento['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($documento['localizacao'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-center">
                                        <span
                                            class="badge <?= $documento['ativo'] ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                            <?= $documento['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a class="btn btn-sm btn-primary"
                                            href="<?= base_url('documento/detalhes/' . $documento['codigo']); ?>"
                                            title="Acessar documento">
                                            Acessar
                                        </a>
                                        <?php if ($this->controle_acesso->tem_permissao('documentos.gerenciar')): ?>
                                            <a class="btn btn-sm btn-light border"
                                                href="<?= base_url('documento/atualizar/' . $documento['codigo']); ?>"
                                                aria-label="Editar documento">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($this->controle_acesso->tem_permissao('documentos.excluir')): ?>
                                            <button class="btn btn-sm btn-light border text-danger excluir-documento"
                                                data-codigo="<?= $documento['codigo']; ?>"
                                                data-titulo="<?= htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalExcluir" type="button">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <p class="small text-secondary mb-0">
                            Exibindo <?= $offset; ?> –
                            <?= min($offset + $limite - 1, $total_documentos); ?> de
                            <?= $total_documentos; ?> documentos
                        </p>
                        <?php parse_str($_SERVER['QUERY_STRING'] ?? '', $params);
                        unset($params['pagina']); ?>
                        <nav aria-label="Paginação de documentos">
                            <ul class="pagination pagination-sm mb-0">
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <?php $params['pagina'] = $i; ?>
                                    <li class="page-item <?= $i == $pagina_atual ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?= http_build_query($params); ?>"><?= $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="card border shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fa-regular fa-file-lines fa-2x text-secondary mb-3"></i>
                    <h2 class="h5">Nenhum documento encontrado</h2>
                    <p class="text-secondary">Cadastre um documento ou ajuste os filtros.</p>
                    <?php if ($this->controle_acesso->tem_permissao('documentos.gerenciar')): ?>
                        <a class="btn btn-primary" href="<?= base_url('documento/cadastrar'); ?>">Cadastrar documento</a>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <div class="modal fade" id="modalExcluir" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <form id="formulario-exclusao">
                    <div class="modal-body text-center p-4">
                        <i class="fa-solid fa-trash-can text-danger fa-2x mb-3"></i>
                        <h2 class="fs-5">Excluir documento?</h2>
                        <p id="titulo-exclusao" class="fw-semibold"></p>
                        <div id="alerta-exclusao" class="alert alert-danger text-start d-none"></div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button class="btn btn-light border" data-bs-dismiss="modal" type="button">Cancelar</button>
                        <button class="btn btn-danger" id="confirmar-exclusao" type="submit">Excluir documento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

        $(document).ready(function () {
            $('#limpar_filtro').on('click', function () {
                window.location = base_url + 'documento';
                return;
            });

            $('#filtrar').click(function (e) {
                e.preventDefault();
                $(this).prop('disabled', true).html("<span class='spinner-border spinner-border-sm' aria-hidden='true'></span>");

                const params = new URLSearchParams();
                const termo = $('#termo').val().trim();
                const status = $('#status').val();
                const tipo_documento = $('#tipo_documento_codigo').val();
                const localizacao = $('#localizacao_codigo').val();

                if (termo) params.append('termo', termo);
                if (status) params.append('status', status);
                if (tipo_documento) params.append('tipo_documento_codigo', tipo_documento);
                if (localizacao) params.append('localizacao_codigo', localizacao);

                window.location = base_url + 'documento?' + params.toString();
            });

            $('.excluir-documento').on('click', function () {
                $('#titulo-exclusao').text(
                    $(this).data('titulo')
                );

                $('#formulario-exclusao').attr(
                    'action',
                    base_url +
                    'documento/excluir/' +
                    $(this).data('codigo')
                );

                $('#alerta-exclusao')
                    .empty()
                    .addClass('d-none');
            });

            $('#formulario-exclusao').on('submit', function (e) {
                e.preventDefault();

                const url = $(this).attr('action');
                const texto_botao = 'Excluir documento';

                $('#confirmar-exclusao')
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
                    );

                $('#alerta-exclusao')
                    .empty()
                    .addClass('d-none');

                $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(
                            response.dados?.erros ||
                            response.mensagem?.conteudo,
                            'alerta-exclusao'
                        );

                        return;
                    }

                    mostrar_feedback(
                        response.mensagem?.conteudo,
                        'success'
                    );

                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                }).fail(function (xhr) {
                    mostrar_erro_ajax(
                        xhr,
                        'alerta-exclusao'
                    );
                }).always(function () {
                    $('#confirmar-exclusao')
                        .prop('disabled', false)
                        .html(texto_botao);
                });
            });
        });
    </script>
</body>

</html>
