<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Pesquisa avançada de documentos do sistema e-Doc">
    <title>Pesquisa avançada | e-Doc</title>
    <?php $this->load->view('css'); ?>
</head>
<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1">Pesquisa avançada</h1>
                <p class="text-body-secondary mb-0">
                    Pesquise documentos utilizando seus dados básicos e metadados.
                </p>
            </section>
            <a class="btn btn-light border flex-shrink-0" href="<?= base_url('pesquisa/localizacao'); ?>">
                <i class="fa-solid fa-folder-tree me-2"></i>
                Navegar por localizações
            </a>
        </header>

        <nav aria-label="Caminho da pesquisa" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>
                    Pesquisa avançada
                </li>
            </ol>
        </nav>

        <section class="card border shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 fw-semibold mb-1">Filtros</h2>
                <p class="small text-body-secondary mb-4">
                    Selecione primeiro o tipo de documento para carregar seus campos pesquisáveis.
                </p>

                <div id="alerta-pesquisa" class="alert alert-danger d-none" role="alert"></div>

                <form action="<?= base_url('pesquisa/avancada'); ?>" id="formulario-pesquisa" method="get">
                    <input name="pesquisar" type="hidden" value="1">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="tipo_documento_codigo">Tipo de documento</label>
                            <select class="form-select" id="tipo_documento_codigo"
                                name="tipo_documento_codigo" required>
                                <option value="">Selecione</option>
                                <?php foreach ($tipos_documento as $tipo_documento): ?>
                                    <option value="<?= $tipo_documento['codigo']; ?>"
                                        <?= $filtro_tipo_documento == $tipo_documento['codigo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="titulo">Título</label>
                            <input class="form-control" id="titulo" name="titulo"
                                placeholder="Parte do título" type="search"
                                value="<?= htmlspecialchars($filtro_titulo, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="numero_identificacao">Número de identificação</label>
                            <input class="form-control" id="numero_identificacao"
                                name="numero_identificacao" placeholder="Número ou parte dele" type="search"
                                value="<?= htmlspecialchars($filtro_numero_identificacao, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label" for="data_inicio">Data inicial</label>
                            <input class="form-control" id="data_inicio" name="data_inicio" type="date"
                                value="<?= htmlspecialchars($filtro_data_inicio, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label" for="data_fim">Data final</label>
                            <input class="form-control" id="data_fim" name="data_fim" type="date"
                                value="<?= htmlspecialchars($filtro_data_fim, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="ativo" <?= $filtro_status === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="inativo" <?= $filtro_status === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h3 class="h6 fw-semibold mb-3">Metadados pesquisáveis</h3>

                    <div class="row g-3" id="campos-metadados">
                        <?php $this->load->view('pesquisa/pesquisa_campos_metadados', [
                            'metadados_pesquisa' => $metadados_pesquisa,
                            'filtros_metadados' => $filtros_metadados
                        ]); ?>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                        <a class="btn btn-light border" href="<?= base_url('pesquisa/avancada'); ?>">Limpar</a>
                        <button class="btn btn-primary" id="pesquisar" type="submit">
                            <i class="fa-solid fa-magnifying-glass me-2"></i>
                            Pesquisar
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <?php if ($pesquisar): ?>
            <section class="card border shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-semibold mb-1">Resultados</h2>
                    <p class="small text-secondary mb-0">
                        <?= $total_documentos; ?> documentos encontrados
                    </p>
                </div>

                <?php if ($documentos): ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light text-uppercase">
                                <tr class="small text-secondary">
                                    <th class="px-3 py-3">Documento</th>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th>Localização</th>
                                    <th class="px-3 text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documentos as $documento): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-semibold">
                                                <?= htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <small class="d-block text-body-secondary">
                                                <?= htmlspecialchars($documento['numero_identificacao'] ?? 'Sem identificação', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </td>
                                        <td><?= htmlspecialchars($documento['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= $documento['data_documento'] ? date('d/m/Y', strtotime($documento['data_documento'])) : 'Não informada'; ?></td>
                                        <td>
                                            <?= htmlspecialchars($documento['localizacao_classificacao'] . ' — ' . $documento['localizacao'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <a class="btn btn-sm btn-primary"
                                                href="<?= base_url('documento/detalhes/' . $documento['codigo']); ?>">
                                                Visualizar
                                            </a>
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
                                <?= $total_documentos; ?>
                            </p>
                            <?php parse_str($_SERVER['QUERY_STRING'], $params); unset($params['pagina']); ?>
                            <nav aria-label="Paginação da pesquisa">
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
                <?php else: ?>
                    <div class="card-body text-center py-5">
                        <i class="fa-solid fa-magnifying-glass fa-2x text-secondary mb-3"></i>
                        <h3 class="h5">Nenhum documento encontrado</h3>
                        <p class="text-secondary mb-0">Revise os filtros e tente novamente.</p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <?php $this->load->view('js'); ?>

    <script>
        const base_url = '<?= base_url(); ?>';

        $(document).ready(function () {
            $('#tipo_documento_codigo').on(
                'change',
                function () {
                    const tipo_documento_codigo =
                        $(this).val();

                    if (!tipo_documento_codigo) {
                        $('#campos-metadados').html(
                            '<div class="col-12"><div class="alert alert-light border mb-0">Selecione um tipo de documento.</div></div>'
                        );

                        return;
                    }

                    $('#alerta-pesquisa')
                        .empty()
                        .addClass('d-none');

                    $('#campos-metadados').html(
                        '<div class="col-12"><span class="spinner-border spinner-border-sm me-2"></span>Carregando campos...</div>'
                    );

                    $.ajax({
                        url:
                            base_url +
                            'pesquisa/campos_metadados/' +
                            tipo_documento_codigo,
                        method: 'GET',
                        dataType: 'json'
                    }).done(function (response) {
                        if (!response.sucesso) {
                            mostrar_erros(
                                response.dados?.erros ||
                                response.mensagem?.conteudo,
                                'alerta-pesquisa'
                            );

                            return;
                        }

                        $('#campos-metadados').html(
                            response.dados.html
                        );
                    }).fail(function (xhr) {
                        mostrar_erro_ajax(
                            xhr,
                            'alerta-pesquisa'
                        );
                    });
                }
            );

            $('#formulario-pesquisa').on(
                'submit',
                function () {
                    $('#pesquisar')
                        .prop('disabled', true)
                        .html(
                            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
                        );
                }
            );
        });
    </script>
</body>
</html>
