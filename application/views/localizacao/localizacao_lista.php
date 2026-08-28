<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tela inicial do módulo de localizações do sistema e-Doc">
    <title>Localizações | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1">Localizações</h1>
                <p class="text-body-secondary mb-0">
                    Organize e gerencie os locais físicos utilizados para armazenamento dos documentos.
                </p>
            </section>
            <a class="btn btn-primary flex-shrink-0" href="<?= base_url('localizacao/cadastrar'); ?>">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Nova localização
            </a>
        </header>

        <nav aria-label="Caminho da localização" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i>
                    Localizações
                </li>
            </ol>
        </nav>

        <section aria-labelledby="filtros-title" class="card border shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3" id="filtros-title">Filtros</h2>
                <form action="#" method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-lg-7">
                            <label class="form-label" for="termo">Buscar localização</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                                </span>
                                <input class="form-control" id="termo" name="termo"
                                    placeholder="Nome, descrição ou protocolo" type="search"
                                    value="<?= htmlspecialchars($filtro_termo ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="ativo" <?= isset($filtro_status) && $filtro_status == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="inativo" <?= isset($filtro_status) && $filtro_status == 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <a class="btn btn-primary flex-fill" id="filtrar" role="button">
                                    <i class="fa-solid fa-filter me-2"></i>
                                    Filtrar
                                </a>
                                <a class="btn btn-light border flex-fill" id="limpar_filtro">Limpar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <?php if ($localizacoes): ?>
            <section aria-labelledby="lista-localizacoes-title" class="card border shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <div>
                        <h2 class="h6 fw-semibold mb-1" id="lista-localizacoes-title">Estruturas principais</h2>
                        <p class="small text-secondary mb-0"><?= $total_localizacoes; ?> localizações encontradas</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3" scope="col">Nome</th>
                                <th class="py-3" scope="col">Tipo</th>
                                <th class="py-3" scope="col">Identificação</th>
                                <th class="py-3 text-center" scope="col">Sublocalizações</th>
                                <th class="py-3 text-center" scope="col">Documentos</th>
                                <th class="py-3 text-center" scope="col">Status</th>
                                <th class="px-3 py-3 text-end" scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($localizacoes as $localizacao): ?>
                                <tr>
                                    <td class="ps-3 ps-lg-4">
                                        <a class="d-flex align-items-center gap-3 text-decoration-none fw-semibold acessar"
                                            data-codigo="<?= $localizacao['codigo']; ?>" role="button">
                                            <span
                                                class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-2"
                                                aria-hidden="true">
                                                <i class="fa-solid fa-building"></i>
                                            </span>
                                            <span>
                                                <?= htmlspecialchars($localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                                <small class="d-block text-body-secondary fw-normal">
                                                    <?= htmlspecialchars($localizacao['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </small>
                                            </span>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light border fw-normal">
                                            <?= htmlspecialchars($localizacao['tipo_localizacao'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-monospace small d-block">
                                            <?= htmlspecialchars($localizacao['classificacao'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <small class="text-body-secondary font-monospace">
                                            <?= htmlspecialchars($localizacao['protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <?= htmlspecialchars($localizacao['total_sublocalizacoes'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?= htmlspecialchars($localizacao['total_documentos'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-center">
                                        <?php if ($localizacao['ativo'] == 1): ?>
                                            <span class="badge text-bg-success">Ativa</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Inativa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3 pe-lg-4">
                                        <div class="d-inline-flex align-items-center gap-1">

                                            <button class="btn btn-sm btn-primary acessar"
                                                data-codigo="<?= $localizacao['codigo']; ?>" type="button">
                                                Acessar
                                            </button>

                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border dropdown-toggle-acoes" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mais ações">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="<?= base_url(
                                                            'etiqueta/localizacao/' .
                                                            rawurlencode(
                                                                $localizacao['protocolo']
                                                            )
                                                        ); ?>" target="_blank" rel="noopener">
                                                            <i class="fa-solid fa-qrcode fa-fw me-2"></i>
                                                            Imprimir etiqueta
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a class="dropdown-item" href="<?= base_url(
                                                            'localizacao/atualizar/' .
                                                            $localizacao['codigo']
                                                        ); ?>">
                                                            <i class="fa-solid fa-pen fa-fw me-2"></i>
                                                            Editar
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>

                                                    <li>
                                                        <button class="dropdown-item text-danger excluir-localizacao"
                                                            type="button" data-codigo="<?= $localizacao['codigo']; ?>"
                                                            data-nome="<?= htmlspecialchars(
                                                                $localizacao['nome'],
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ); ?>" data-classificacao="<?= htmlspecialchars(
                                                                 $localizacao['classificacao'],
                                                                 ENT_QUOTES,
                                                                 'UTF-8'
                                                             ); ?>" data-bs-toggle="modal" data-bs-target="#modalExcluirLocalizacao">
                                                            <i class="fa-solid fa-trash-can fa-fw me-2"></i>
                                                            Excluir
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white py-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <p class="small text-secondary mb-0">
                            Exibindo <?= $offset; ?> – <?= min($offset + $limite - 1, $total_localizacoes); ?> de
                            <?= $total_localizacoes; ?> localizações
                        </p>

                        <?php
                        parse_str($_SERVER['QUERY_STRING'], $params);
                        unset($params['pagina']);
                        $gerar_url = function ($num_pagina) use ($params) {
                            $params['pagina'] = $num_pagina;
                            return '?' . http_build_query($params);
                        };
                        ?>

                        <nav aria-label="Paginação de localizações">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item">
                                    <?php if ($pagina_atual > 1): ?>
                                        <a aria-label="Anterior" class="page-link"
                                            href="<?= $gerar_url($pagina_atual - 1) ?>">Anterior</a>
                                    <?php else: ?>
                                        <a aria-label="Anterior" class="page-link disabled">Anterior</a>
                                    <?php endif; ?>
                                </li>

                                <?php $adjacentes = 3; ?>
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <?php if ($i == 1 || $i == $total_paginas || ($i >= $pagina_atual - $adjacentes && $i <= $pagina_atual + $adjacentes)): ?>
                                        <?php if ($i == $pagina_atual): ?>
                                            <li aria-current="page" class="page-item active">
                                                <span class="page-link bg-primary"><?= $i; ?></span>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item">
                                                <a class="page-link text-primary" href="<?= $gerar_url($i); ?>"><?= $i; ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <li class="page-item">
                                    <?php if ($pagina_atual < $total_paginas): ?>
                                        <a aria-label="Próximo" class="page-link"
                                            href="<?= $gerar_url($pagina_atual + 1) ?>">Próximo</a>
                                    <?php else: ?>
                                        <a aria-label="Próximo" class="page-link disabled">Próximo</a>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section aria-labelledby="estado-vazio" class="card border shadow-sm mt-4">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fa-solid fa-landmark fa-2x text-secondary"></i>
                    </div>
                    <h2 class="h5 fw-semibold" id="estado-vazio">Nenhuma localização cadastrada</h2>
                    <p class="text-secondary mb-4">
                        Cadastre sua primeira localização para começar a organizar seus documentos.
                    </p>
                    <a class="btn btn-primary flex-shrink-0" href="<?= base_url('localizacao/cadastrar'); ?>">
                        <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                        Cadastrar localização
                    </a>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <div class="modal fade" id="modalExcluirLocalizacao" tabindex="-1" aria-labelledby="modalExcluirLocalizacaoLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <form id="formulario_exclusao_localizacao" method="post">
                    <div class="modal-body text-center p-4">
                        <div class="mb-3">
                            <i class="fa-solid fa-trash-can text-danger fa-2x" aria-hidden="true"></i>
                        </div>
                        <h2 class="modal-title fs-5 mb-2" id="modalExcluirLocalizacaoLabel">Excluir localização?</h2>
                        <p class="text-body-secondary mb-2">Você está prestes a excluir:</p>
                        <p class="fw-semibold mb-1" id="nome-localizacao-exclusao"></p>
                        <p class="small text-body-secondary mb-3" id="classificacao-localizacao-exclusao"></p>
                        <div id="alerta-exclusao-localizacao" class="alert alert-danger text-start d-none" role="alert">
                        </div>
                        <p class="small text-body-secondary mb-0">A localização deixará de aparecer nas listagens.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                        <a class="btn btn-danger" id="submit_exclusao_localizacao">Excluir localização</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="true">
        <div id="toast-feedback" class="toast border-0 shadow" role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-body d-flex align-items-center gap-3">
                <i id="toast-icone" class="fa-solid fa-circle-check text-success fs-5" aria-hidden="true"></i>
                <span id="toast-mensagem" class="flex-grow-1"></span>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <?php $this->load->view('js'); ?>

    <script>
        const base_url = '<?= base_url(); ?>';

        $(document).ready(function () {
            $('.acessar').on('click', function () {
                window.location = base_url + 'localizacao/detalhes/' + $(this).data('codigo');
            });

            $('#limpar_filtro').click(function () {
                window.location = base_url + 'localizacao';
            });

            $('#filtrar').click(function (e) {
                e.preventDefault();
                $(this).prop('disabled', true).html("<span class='spinner-border spinner-border-sm' aria-hidden='true'></span>");

                const params = new URLSearchParams();
                const termo = $('#termo').val().trim();
                const status = $('#status').val();

                if (termo) params.append('termo', termo);
                if (status) params.append('status', status);

                window.location = base_url + 'localizacao?' + params.toString();
            });

            $('#submit_exclusao_localizacao').on('click', function () {
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
                $('#formulario_exclusao_localizacao').submit();
            });

            $('#formulario_exclusao_localizacao').on('submit', function (e) {
                e.preventDefault();
                const url = $(this).attr('action');
                $('#alerta-exclusao-localizacao').empty().addClass('d-none');

                $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(response.dados?.erros, 'alerta-exclusao-localizacao');
                        return;
                    }

                    mostrar_feedback(response.mensagem?.conteudo, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-exclusao-localizacao');
                }).always(function () {
                    $('#submit_exclusao_localizacao').prop('disabled', false).html('Excluir localização');
                });
            });

            $('#modalExcluirLocalizacao').on('show.bs.modal', function (e) {
                const btn = e.relatedTarget;
                const codigo = btn.getAttribute('data-codigo');
                const nome = btn.getAttribute('data-nome');
                const classificacao = btn.getAttribute('data-classificacao');

                $('#alerta-exclusao-localizacao').empty().addClass('d-none');
                $('#nome-localizacao-exclusao').text(nome);
                $('#classificacao-localizacao-exclusao').text('Classificação: ' + classificacao);
                $('#formulario_exclusao_localizacao').attr('action', base_url + 'localizacao/excluir/' + codigo);
            });
        });
    </script>
</body>

</html>