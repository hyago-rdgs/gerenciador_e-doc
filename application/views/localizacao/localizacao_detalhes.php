<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Detalhes da localização do sistema e-Doc">
    <title><?= htmlspecialchars($localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?> | e-Doc</title>

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
            <a class="btn btn-primary flex-shrink-0" href="<?= base_url('localizacao/cadastrar/' . $localizacao['codigo']); ?>">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Nova localização
            </a>
        </header>

        <nav aria-label="Caminho da localização" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a class="text-decoration-none" href="<?= base_url('localizacao'); ?>">
                        <i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i>
                        Localizações
                    </a>
                </li>
                <?php foreach ($caminho as $indice => $item): ?>
                    <?php $ultimo = $indice === array_key_last($caminho); ?>

                    <?php if ($ultimo): ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8'); ?>
                        </li>
                    <?php else: ?>
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('localizacao/detalhes/' . $item['codigo']); ?>" class="text-decoration-none">
                                <?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>


        <section class="card border shadow-sm mb-4" aria-labelledby="titulo-localizacao-atual">
            <article class="card-body p-3 p-lg-4">
                <header class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
                    <section>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span
                                class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-2"
                                aria-hidden="true">
                                <i class="fa-solid fa-building"></i>
                            </span>
                            <span class="badge text-bg-light border">
                                <?= htmlspecialchars($localizacao['tipo_localizacao'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php if ($localizacao['ativo'] == 1): ?>
                                <span class="badge text-bg-success">Ativa</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inativa</span>
                            <?php endif; ?>
                        </div>
                        <h2 class="h4 mb-1" id="titulo-localizacao-atual">
                            <?= htmlspecialchars($localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                        </h2>
                        <p class="text-body-secondary mb-0">
                            <?= htmlspecialchars($localizacao['descricao'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </section>
                    <section class="d-grid d-sm-flex gap-2" aria-label="Ações da localização atual">
                        <a class="btn btn-outline-secondary"
                            href="<?= base_url('localizacao/atualizar/' . $localizacao['codigo']); ?>">
                            <i class="fa-regular fa-pen-to-square me-2" aria-hidden="true"></i>
                            Editar
                        </a>

                        <a class="btn btn-outline-primary flex-shrink-0"
                            href="<?= base_url('localizacao/cadastrar/' . $localizacao['codigo']); ?>">
                            <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                            Adicionar sublocalização
                        </a>

                        <button type="button" class="btn btn-outline-danger excluir-localizacao"
                            data-codigo="<?= $localizacao['codigo']; ?>"
                            data-nome="<?= htmlspecialchars($localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-classificacao="<?= htmlspecialchars($localizacao['classificacao'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-bs-toggle="modal" data-bs-target="#modalExcluirLocalizacao"
                            aria-label="Excluir <?= htmlspecialchars($localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>">

                            <i class="fa-regular fa-trash-can me-2" aria-hidden="true"></i>Excluir
                        </button>
                    </section>
                </header>

                <dl class="row border-top pt-3 mb-0">
                    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                        <dt class="small text-body-secondary fw-normal mb-1">Classificação</dt>
                        <dd class="fw-semibold mb-0">
                            <?= htmlspecialchars($localizacao['classificacao'], ENT_QUOTES, 'UTF-8'); ?>
                        </dd>
                    </div>
                    <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                        <dt class="small text-body-secondary fw-normal mb-1">Localização superior</dt>
                        <dd class="mb-0">
                            <?php if (!empty($localizacao['localizacao_pai_nome'])): ?>
                                <a class="text-decoration-none fw-semibold" href="#">
                                    <?= htmlspecialchars($localizacao['localizacao_pai_nome'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-secondary">
                                    Localização raiz
                                </span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div class="col-sm-6 col-lg-3 mb-3 mb-sm-0">
                        <dt class="small text-body-secondary fw-normal mb-1">Sublocalizações</dt>
                        <dd class="fw-semibold mb-0">
                            <?= htmlspecialchars($localizacao['total_sublocalizacoes'], ENT_QUOTES, 'UTF-8'); ?>
                        </dd>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <dt class="small text-body-secondary fw-normal mb-1">Documentos neste nível</dt>
                        <dd class="fw-semibold mb-0">
                            <?= htmlspecialchars($localizacao['total_documentos'], ENT_QUOTES, 'UTF-8'); ?>
                        </dd>
                    </div>
                </dl>
            </article>
        </section>

        <section aria-labelledby="filtros-title" class="card border shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3" id="filtros-title">Filtros</h2>
                <form action="#" method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-lg-7">
                            <label class="form-label" for="termo_localizacao">Buscar localização</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                                </span>
                                <input class="form-control" id="termo_localizacao" name="termo_localizacao"
                                    placeholder="Nome ou descrição" type="search"
                                    value="<?= htmlspecialchars($filtro_termo_localizacao ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                            <label class="form-label" for="status_localizacao">Status</label>
                            <select class="form-select" id="status_localizacao" name="status_localizacao">
                                <option value="">Todos</option>
                                <option value="ativo" <?php if (isset($filtro_status_localizacao) && $filtro_status_localizacao == 'ativo'): ?> selected <?php endif; ?>>Ativo
                                </option>
                                <option value="inativo" <?php if (isset($filtro_status_localizacao) && $filtro_status_localizacao == 'inativo'): ?>selected <?php endif; ?>>Inativo
                                </option>
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

        <?php if ($localizacoes_filho): ?>
            <section aria-labelledby="lista-localizacoes-title" class="card border shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <div>
                        <h2 class="h6 fw-semibold mb-1" id="lista-localizacoes-title">Sublocalizações</h2>
                        <p class="small text-secondary mb-0">
                            <?= count($localizacoes_filho); ?> localizações encontradas
                        </p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3" scope="col">Nome</th>
                                <th class="py-3" scope="col">Tipo</th>
                                <th class="py-3" scope="col">Classificação</th>
                                <th class="py-3 text-center" scope="col">Sublocalizações</th>
                                <th class="py-3 text-center" scope="col">Documentos</th>
                                <th class="py-3 text-center" scope="col">Status</th>
                                <th class="px-3 py-3 text-end" scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($localizacoes_filho as $k => $localizacao_filho): ?>
                                <tr>
                                    <td class="ps-3 ps-lg-4">
                                        <a class="d-flex align-items-center gap-3 text-decoration-none fw-semibold acessar"
                                            data-codigo="<?= $localizacao_filho['codigo']; ?>" role="button">
                                            <span
                                                class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-2"
                                                aria-hidden="true">
                                                <i class="fa-solid fa-building"></i>
                                            </span>
                                            <span>
                                                <?= htmlspecialchars($localizacao_filho['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                                <small class="d-block text-body-secondary fw-normal">
                                                    <?= htmlspecialchars($localizacao_filho['descricao'], ENT_QUOTES, 'UTF-8'); ?>
                                                </small>
                                            </span>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light border fw-normal">
                                            <?= htmlspecialchars($localizacao_filho['tipo_localizacao'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-monospace small">
                                            <?= htmlspecialchars($localizacao_filho['classificacao'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?= htmlspecialchars($localizacao_filho['total_sublocalizacoes'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?= htmlspecialchars($localizacao_filho['total_documentos'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($localizacao_filho['ativo'] == 1): ?>
                                            <span class="badge text-bg-success">Ativa</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Inativa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3 pe-lg-4">
                                        <button class="btn btn-sm btn-primary acessar"
                                            data-codigo="<?= $localizacao_filho['codigo']; ?>" role="button">
                                            <i class="fa-solid fa-arrow-right me-2" aria-hidden="true"></i>
                                            Acessar
                                        </button>

                                        <a class="btn btn-sm btn-light border"
                                            href="<?= base_url('localizacao/atualizar/' . $localizacao_filho['codigo']); ?>"
                                            aria-label="Editar localização">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <button type="button"
                                            class="btn btn-sm btn-light border text-danger excluir-localizacao"
                                            data-codigo="<?= $localizacao_filho['codigo']; ?>"
                                            data-nome="<?= htmlspecialchars($localizacao_filho['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-classificacao="<?= htmlspecialchars($localizacao_filho['classificacao'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalExcluirLocalizacao"
                                            aria-label="Excluir <?= htmlspecialchars($localizacao_filho['nome'], ENT_QUOTES, 'UTF-8'); ?>">

                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="card-footer bg-white py-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <p class="small text-secondary mb-0">
                            Exibindo
                            <?= $offset_localizacao; ?> –
                            <?= min($offset_localizacao + $limite - 1, $total_localizacoes); ?> de
                            <?= $total_localizacoes; ?> localizações
                        </p>

                        <?php
                        parse_str($_SERVER['QUERY_STRING'], $params);
                        unset($params['pagina_localizacao']);

                        $gerar_url = function ($num_pagina) use ($params) {
                            $params['pagina_localizacao'] = $num_pagina;
                            return '?' . http_build_query($params);
                        };
                        ?>
                        <nav aria-label="Paginação de localizações">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item">
                                    <?php if ($pagina_atual_localizacao > 1): ?>
                                        <a aria-label="Anterior" class="page-link"
                                            href="<?= $gerar_url($pagina_atual_localizacao - 1) ?>">Anterior</a>
                                    <?php else: ?>
                                        <a aria-label="Anterior" class="page-link disabled">Anterior</a>
                                    <?php endif; ?>
                                </li>

                                <?php $adjacentes = 3; ?>
                                <?php for ($i = 1; $i <= $total_paginas_localizacao; $i++): ?>
                                    <?php if ($i == 1 || $i == $total_paginas_localizacao || ($i >= $pagina_atual_localizacao - $adjacentes && $i <= $pagina_atual_localizacao + $adjacentes)): ?>
                                        <?php if ($i == $pagina_atual_localizacao): ?>
                                            <li aria-current="page" class="page-item active">
                                                <span class="page-link bg-primary">
                                                    <?= $i; ?>
                                                </span>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item">
                                                <a class="page-link text-primary" href="<?= $gerar_url($i); ?>">
                                                    <?= $i; ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php $mostrar_reticencias_esquerda = $i == 2 && $pagina_atual_localizacao > $adjacentes + 2; ?>
                                    <?php $mostrar_reticencias_direita = $i == $total_paginas_localizacao - 1 && $pagina_atual_localizacao < $total_paginas_localizacao - $adjacentes - 1; ?>

                                    <?php if ($mostrar_reticencias_esquerda || $mostrar_reticencias_direita): ?>
                                        <li>
                                            <span class="pagination-ellipsis">&hellip;</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <li class="page-item">
                                    <?php if ($pagina_atual_localizacao < $total_paginas_localizacao): ?>
                                        <a aria-label="Próximo" class="page-link"
                                            href="<?= $gerar_url($pagina_atual_localizacao + 1) ?>">Próximo</a>
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
            <!-- Empty State -->
            <section aria-labelledby="estado-vazio" class="card border shadow-sm mt-4 mb-4">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fa-solid fa-landmark fa-2x text-secondary"></i>
                    </div>
                    <h2 class="h5 fw-semibold" id="estado-vazio">Nenhuma sublocalização cadastrada</h2>
                    <p class="text-secondary mb-4">
                        Cadastre a primeira sublocalização desta estrutura.
                    </p>

                    <a class="btn btn-primary flex-shrink-0" href="<?= base_url('localizacao/cadastrar/' . $localizacao['codigo']); ?>">
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

                        <h2 class="modal-title fs-5 mb-2" id="modalExcluirLocalizacaoLabel">
                            Excluir localização?
                        </h2>

                        <p class="text-body-secondary mb-2">
                            Você está prestes a excluir:
                        </p>

                        <p class="fw-semibold mb-1" id="nome-localizacao-exclusao"></p>

                        <p class="small text-body-secondary mb-3" id="classificacao-localizacao-exclusao"></p>

                        <div id="alerta-exclusao-localizacao" class="alert alert-danger text-start d-none" role="alert">
                        </div>

                        <p class="small text-body-secondary mb-0">
                            A localização deixará de aparecer nas listagens.
                        </p>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <a class="btn btn-danger" id="submit_exclusao_localizacao">
                            Excluir localização
                        </a>
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
            $('.acessar').on('click', function () {
                const codigo = $(this).data('codigo');

                window.location = base_url + 'localizacao/detalhes/' + codigo;
                return;
            })

            $('#limpar_filtro').click(function () {
                window.location = base_url + 'localizacao/detalhes/<?= $localizacao['codigo']; ?>';
                return;
            });

            $('#filtrar').click(function (e) {
                e.preventDefault();
                $(this).prop('disabled', true).html("<span class='spinner-border spinner-border-sm' aria-hidden='true'></span>");

                const params = new URLSearchParams();

                const termo = $('#termo_localizacao').val().trim();
                const status = $('#status_localizacao').val();

                // Só adiciona na URL os campos que possuem valor preenchido
                if (termo) params.append('termo_localizacao', termo);
                if (status) params.append('status_localizacao', status);

                window.location = base_url + "localizacao/detalhes/<?= $localizacao['codigo']; ?>?" + params.toString();
                return;
            });

            $('#submit_exclusao_localizacao').on('click', function () {
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
                $('#formulario_exclusao_localizacao').submit();
            });

            $('#formulario_exclusao_localizacao').on('submit', function (e) {
                e.preventDefault();
                const url = $('#formulario_exclusao_localizacao').attr('action');
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

                    const modal = bootstrap.Modal.getInstance(
                        document.getElementById('modalExcluirLocalizacao')
                    );

                    modal?.hide();

                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }).fail(function (xhr) {
                    const response = xhr.responseJSON;

                    if (response?.dados?.erros) {
                        mostrar_erros(response.dados.erros, 'alerta-exclusao-localizacao');
                        return;
                    }

                    mostrar_erros(
                        response?.mensagem?.conteudo ||
                        'Não foi possível comunicar com o servidor. Tente novamente.',
                        'alerta-exclusao-localizacao'
                    );
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
