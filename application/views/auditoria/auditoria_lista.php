<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Auditoria geral do sistema e-Doc">
    <title>Auditoria | e-Doc</title>
    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="mb-4">
            <h1 class="h3 mb-1">Auditoria</h1>
            <p class="text-body-secondary mb-0">
                Consulte alterações críticas realizadas no sistema.
            </p>
        </header>

        <section class="card border shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label class="form-label" for="termo">Buscar</label>
                            <input class="form-control" id="termo" name="termo"
                                placeholder="Módulo, ação, entidade ou usuário" type="search"
                                value="<?= htmlspecialchars($filtro_termo, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label" for="modulo">Módulo</label>
                            <select class="form-select" id="modulo" name="modulo">
                                <option value="">Todos</option>
                                <?php foreach ($modulos as $modulo): ?>
                                    <option value="<?= htmlspecialchars($modulo, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= $filtro_modulo === $modulo ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars(ucfirst($modulo), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label" for="acao">Ação</label>
                            <select class="form-select" id="acao" name="acao">
                                <option value="">Todas</option>
                                <?php foreach ($acoes as $acao): ?>
                                    <option value="<?= htmlspecialchars($acao, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= $filtro_acao === $acao ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($acao, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label" for="usuario_codigo">Usuário</label>
                            <select class="form-select" id="usuario_codigo" name="usuario_codigo">
                                <option value="">Todos</option>
                                <option value="sistema" <?= $filtro_usuario === 'sistema' ? 'selected' : ''; ?>>
                                    Sistema
                                </option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['codigo']; ?>"
                                        <?= (string) $filtro_usuario === (string) $usuario['codigo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label" for="data_inicio">Data inicial</label>
                            <input class="form-control" id="data_inicio" name="data_inicio" type="date"
                                value="<?= htmlspecialchars($filtro_data_inicio, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label" for="data_fim">Data final</label>
                            <input class="form-control" id="data_fim" name="data_fim" type="date"
                                value="<?= htmlspecialchars($filtro_data_fim, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar
                            </button>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <a class="btn btn-light border w-100" href="<?= base_url('auditoria'); ?>">
                                Limpar filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <section class="card border shadow-sm">
            <div class="card-header bg-white py-3">
                <span class="fw-semibold">
                    <?= number_format($total_auditorias, 0, ',', '.'); ?>
                </span>
                <span class="text-body-secondary">registros encontrados</span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light text-uppercase">
                        <tr class="small text-secondary">
                            <th class="px-3 py-3">Data</th>
                            <th>Usuário</th>
                            <th>Módulo</th>
                            <th>Ação</th>
                            <th>Entidade</th>
                            <th>IP</th>
                            <th class="px-3 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($auditorias): ?>
                            <?php foreach ($auditorias as $auditoria): ?>
                                <tr>
                                    <td class="ps-3">
                                        <?= date('d/m/Y H:i:s', strtotime($auditoria['cadastro'])); ?>
                                    </td>
                                    <td>
                                        <span class="d-block">
                                            <?= htmlspecialchars(
                                                $auditoria['usuario_nome'] ?? 'Sistema',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </span>
                                        <?php if (!empty($auditoria['usuario_login'])): ?>
                                            <small class="text-body-secondary">
                                                <?= htmlspecialchars($auditoria['usuario_login'], ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($auditoria['modulo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="font-monospace small">
                                            <?= htmlspecialchars($auditoria['acao'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($auditoria['entidade'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if ($auditoria['entidade_codigo'] !== NULL): ?>
                                            <span class="text-body-secondary">
                                                #<?= (int) $auditoria['entidade_codigo']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(
                                            $auditoria['endereco_ip'] ?? '-',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <button class="btn btn-sm btn-light border visualizar-auditoria"
                                            data-codigo="<?= $auditoria['codigo']; ?>"
                                            title="Visualizar detalhes" type="button">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="text-center text-body-secondary py-5" colspan="7">
                                    Nenhum registro de auditoria encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if ($total_paginas > 1): ?>
            <?php
            parse_str($_SERVER['QUERY_STRING'] ?? '', $params);
            unset($params['pagina']);

            $gerar_url = function ($pagina) use ($params) {
                $params['pagina'] = $pagina;
                return '?' . http_build_query($params);
            };

            $adjacencia = 2;
            $inicio = max(1, $pagina_atual - $adjacencia);
            $fim = min($total_paginas, $pagina_atual + $adjacencia);
            ?>

            <nav aria-label="Paginação da auditoria" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $pagina_atual <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link"
                            href="<?= htmlspecialchars($gerar_url($pagina_atual - 1), ENT_QUOTES, 'UTF-8'); ?>">
                            Anterior
                        </a>
                    </li>

                    <?php if ($inicio > 1): ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="<?= htmlspecialchars($gerar_url(1), ENT_QUOTES, 'UTF-8'); ?>">1</a>
                        </li>
                        <?php if ($inicio > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link border-0 bg-transparent text-muted">&hellip;</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($pagina = $inicio; $pagina <= $fim; $pagina++): ?>
                        <li class="page-item <?= $pagina === $pagina_atual ? 'active' : ''; ?>">
                            <a class="page-link"
                                href="<?= htmlspecialchars($gerar_url($pagina), ENT_QUOTES, 'UTF-8'); ?>">
                                <?= $pagina; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($fim < $total_paginas): ?>
                        <?php if ($fim < $total_paginas - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link border-0 bg-transparent text-muted">&hellip;</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="<?= htmlspecialchars($gerar_url($total_paginas), ENT_QUOTES, 'UTF-8'); ?>">
                                <?= $total_paginas; ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item <?= $pagina_atual >= $total_paginas ? 'disabled' : ''; ?>">
                        <a class="page-link"
                            href="<?= htmlspecialchars($gerar_url($pagina_atual + 1), ENT_QUOTES, 'UTF-8'); ?>">
                            Próxima
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </main>

    <div class="modal fade" id="modal-auditoria" tabindex="-1"
        aria-labelledby="titulo-modal-auditoria" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="titulo-modal-auditoria">Detalhes da auditoria</h2>
                    <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-0" id="conteudo-auditoria"></div>
            </div>
        </div>
    </div>

    <?php $this->load->view('js'); ?>

    <script>
        $(document).ready(function () {
            const modal_auditoria = bootstrap.Modal.getOrCreateInstance(
                $('#modal-auditoria')[0]
            );

            $('.visualizar-auditoria').on('click', function () {
                const codigo = $(this).data('codigo');

                $('#conteudo-auditoria').html(
                    '<div class="text-center py-5">' +
                        '<span class="spinner-border text-primary" aria-hidden="true"></span>' +
                        '<span class="visually-hidden">Carregando...</span>' +
                    '</div>'
                );

                modal_auditoria.show();

                $.ajax({
                    url: '<?= base_url('auditoria/detalhes/'); ?>' + codigo,
                    method: 'GET',
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        $('#conteudo-auditoria').html(
                            '<div class="alert alert-danger m-4">' +
                                'Não foi possível carregar o registro de auditoria.' +
                            '</div>'
                        );
                        return;
                    }

                    $('#conteudo-auditoria').html(response.dados?.html || '');
                }).fail(function () {
                    $('#conteudo-auditoria').html(
                        '<div class="alert alert-danger m-4">' +
                            'Não foi possível carregar o registro de auditoria.' +
                        '</div>'
                    );
                });
            });
        });
    </script>
</body>

</html>
