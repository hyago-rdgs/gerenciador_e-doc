<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tela inicial do módulo de tipos de documento do sistema e-Doc">
    <title>Tipos de documento | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1">Tipos de documento</h1>
                <p class="text-body-secondary mb-0">
                    Cadastre os tipos e defina os metadados utilizados na organização dos documentos.
                </p>
            </section>
            <a class="btn btn-primary flex-shrink-0" href="<?= base_url('tipo_documento/cadastrar'); ?>">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Novo tipo de documento
            </a>
        </header>

        <nav aria-label="Caminho do tipo de documento" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fa-solid fa-folder-tree me-1" aria-hidden="true"></i>
                    Tipos de documento
                </li>
            </ol>
        </nav>

        <section aria-labelledby="filtros-title" class="card border shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3" id="filtros-title">Filtros</h2>
                <form action="#" method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-lg-7">
                            <label class="form-label" for="termo">Buscar tipo de documento</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass text-secondary" aria-hidden="true"></i>
                                </span>
                                <input class="form-control" id="termo" name="termo"
                                    placeholder="Nome ou descrição" type="search"
                                    value="<?= htmlspecialchars($filtro_termo ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="ativo"
                                    <?= isset($filtro_status) && $filtro_status == 'ativo' ? 'selected' : ''; ?>>
                                    Ativo
                                </option>
                                <option value="inativo"
                                    <?= isset($filtro_status) && $filtro_status == 'inativo' ? 'selected' : ''; ?>>
                                    Inativo
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <a class="btn btn-primary flex-fill" id="filtrar" role="button">
                                    <i class="fa-solid fa-filter me-2" aria-hidden="true"></i>
                                    Filtrar
                                </a>
                                <a class="btn btn-light border flex-fill" id="limpar_filtro" role="button">Limpar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <?php if ($tipos_documento): ?>
            <section aria-labelledby="lista-tipos-documento-title" class="card border shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <div>
                        <h2 class="h6 fw-semibold mb-1" id="lista-tipos-documento-title">Tipos cadastrados</h2>
                        <p class="small text-secondary mb-0">
                            <?= $total_tipos_documento; ?> tipos de documento encontrados
                        </p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3" scope="col">Nome</th>
                                <th class="py-3 text-center" scope="col">Metadados</th>
                                <th class="py-3 text-center" scope="col">Documentos</th>
                                <th class="py-3 text-center" scope="col">Status</th>
                                <th class="px-3 py-3 text-end" scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tipos_documento as $tipo_documento): ?>
                                <tr>
                                    <td class="ps-3 ps-lg-4">
                                        <a class="d-flex align-items-center gap-3 text-decoration-none fw-semibold"
                                            href="<?= base_url('tipo_documento/detalhes/' . $tipo_documento['codigo']); ?>">
                                            <span
                                                class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-2"
                                                aria-hidden="true">
                                                <i class="fa-solid fa-folder-tree"></i>
                                            </span>
                                            <span>
                                                <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                                <small class="d-block text-body-secondary fw-normal">
                                                    <?= htmlspecialchars($tipo_documento['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </small>
                                            </span>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <?= (int) $tipo_documento['total_metadados']; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= (int) $tipo_documento['total_documentos']; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($tipo_documento['ativo'] == 1): ?>
                                            <span class="badge text-bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3 pe-lg-4">
                                        <a class="btn btn-sm btn-primary"
                                            href="<?= base_url('tipo_documento/detalhes/' . $tipo_documento['codigo']); ?>">
                                            Acessar
                                        </a>

                                        <a class="btn btn-sm btn-light border"
                                            href="<?= base_url('tipo_documento/atualizar/' . $tipo_documento['codigo']); ?>"
                                            aria-label="Editar <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                        </a>

                                        <button type="button"
                                            class="btn btn-sm btn-light border text-danger excluir-tipo-documento"
                                            data-codigo="<?= $tipo_documento['codigo']; ?>"
                                            data-nome="<?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalExcluirTipoDocumento"
                                            aria-label="Excluir <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white py-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <p class="small text-secondary mb-0">
                            Exibindo <?= $offset; ?> –
                            <?= min($offset + $limite - 1, $total_tipos_documento); ?> de
                            <?= $total_tipos_documento; ?> tipos de documento
                        </p>

                        <?php
                        parse_str($_SERVER['QUERY_STRING'] ?? '', $params);
                        unset($params['pagina']);

                        $gerar_url = function ($pagina) use ($params) {
                            $params['pagina'] = $pagina;
                            return '?' . http_build_query($params);
                        };
                        ?>

                        <?php if ($total_paginas > 1): ?>
                            <nav aria-label="Paginação de tipos de documento">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item">
                                        <?php if ($pagina_atual > 1): ?>
                                            <a aria-label="Anterior" class="page-link"
                                                href="<?= $gerar_url($pagina_atual - 1); ?>">Anterior</a>
                                        <?php else: ?>
                                            <a aria-label="Anterior" class="page-link disabled">Anterior</a>
                                        <?php endif; ?>
                                    </li>

                                    <?php $adjacentes = 2; ?>
                                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                        <?php if ($i == 1 || $i == $total_paginas || ($i >= $pagina_atual - $adjacentes && $i <= $pagina_atual + $adjacentes)): ?>
                                            <?php if ($i == $pagina_atual): ?>
                                                <li aria-current="page" class="page-item active">
                                                    <span class="page-link bg-primary"><?= $i; ?></span>
                                                </li>
                                            <?php else: ?>
                                                <li class="page-item">
                                                    <a class="page-link text-primary" href="<?= $gerar_url($i); ?>">
                                                        <?= $i; ?>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php $mostrar_reticencias_esquerda = $i == 2 && $pagina_atual > $adjacentes + 2; ?>
                                        <?php $mostrar_reticencias_direita = $i == $total_paginas - 1 && $pagina_atual < $total_paginas - $adjacentes - 1; ?>

                                        <?php if ($mostrar_reticencias_esquerda || $mostrar_reticencias_direita): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link border-0 bg-transparent text-muted">
                                                    &hellip;
                                                </span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <li class="page-item">
                                        <?php if ($pagina_atual < $total_paginas): ?>
                                            <a aria-label="Próximo" class="page-link"
                                                href="<?= $gerar_url($pagina_atual + 1); ?>">Próximo</a>
                                        <?php else: ?>
                                            <a aria-label="Próximo" class="page-link disabled">Próximo</a>
                                        <?php endif; ?>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section aria-labelledby="estado-vazio" class="card border shadow-sm mt-4">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fa-solid fa-folder-tree fa-2x text-secondary" aria-hidden="true"></i>
                    </div>
                    <h2 class="h5 fw-semibold" id="estado-vazio">Nenhum tipo de documento cadastrado</h2>
                    <p class="text-secondary mb-4">
                        Cadastre o primeiro tipo para começar a estruturar seus documentos.
                    </p>
                    <a class="btn btn-primary" href="<?= base_url('tipo_documento/cadastrar'); ?>">
                        <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                        Cadastrar tipo de documento
                    </a>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <div class="modal fade" id="modalExcluirTipoDocumento" tabindex="-1"
        aria-labelledby="modalExcluirTipoDocumentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <form id="formulario_exclusao_tipo_documento" method="post">
                    <div class="modal-body text-center p-4">
                        <div class="mb-3">
                            <i class="fa-solid fa-trash-can text-danger fa-2x" aria-hidden="true"></i>
                        </div>

                        <h2 class="modal-title fs-5 mb-2" id="modalExcluirTipoDocumentoLabel">
                            Excluir tipo de documento?
                        </h2>

                        <p class="text-body-secondary mb-2">Você está prestes a excluir:</p>
                        <p class="fw-semibold mb-3" id="nome-tipo-documento-exclusao"></p>

                        <div id="alerta-exclusao" class="alert alert-danger text-start d-none" role="alert"></div>

                        <p class="small text-body-secondary mb-0">
                            O tipo e seus vínculos com metadados deixarão de aparecer nas listagens.
                        </p>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-danger" type="submit" id="confirmar_exclusao">
                            Excluir tipo
                        </button>
                    </div>
                </form>
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
            $('#limpar_filtro').click(function () {
                window.location = base_url + 'tipo_documento';
                return;
            });

            $('#filtrar').click(function (e) {
                e.preventDefault();

                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');

                const params = new URLSearchParams();
                const termo = $('#termo').val().trim();
                const status = $('#status').val();

                if (termo) params.append('termo', termo);
                if (status) params.append('status', status);

                window.location = base_url + 'tipo_documento?' + params.toString();
                return;
            });

            $('.excluir-tipo-documento').click(function () {
                const codigo = $(this).data('codigo');
                const nome = $(this).data('nome');

                $('#nome-tipo-documento-exclusao').text(nome);
                $('#alerta-exclusao').empty().addClass('d-none');
                $('#formulario_exclusao_tipo_documento').attr(
                    'action',
                    base_url + 'tipo_documento/excluir/' + codigo
                );
            });

            $('#formulario_exclusao_tipo_documento').on('submit', function (e) {
                e.preventDefault();

                const url = $(this).attr('action');

                $('#confirmar_exclusao').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
                $('#alerta-exclusao').empty().addClass('d-none');

                $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(response.dados?.erros, 'alerta-exclusao');
                        return;
                    }

                    mostrar_feedback(response.mensagem?.conteudo, 'success');

                    const modal = bootstrap.Modal.getInstance(
                        document.getElementById('modalExcluirTipoDocumento')
                    );

                    modal?.hide();

                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }).fail(function (xhr) {
                    const response = xhr.responseJSON;

                    if (response?.dados?.erros) {
                        mostrar_erros(response.dados.erros, 'alerta-exclusao');
                        return;
                    }

                    mostrar_erros(
                        response?.mensagem?.conteudo ||
                        'Não foi possível comunicar com o servidor. Tente novamente.',
                        'alerta-exclusao'
                    );
                }).always(function () {
                    $('#confirmar_exclusao').prop('disabled', false).html('Excluir tipo');
                });
            });
        });
    </script>
</body>

</html>
