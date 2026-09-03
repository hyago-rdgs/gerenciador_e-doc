<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Relatório de acervo do e-Doc">
    <title>Relatório de Acervo | e-Doc</title>
    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <a class="text-decoration-none small" href="<?= base_url('relatorio'); ?>">
                    <i class="fa-solid fa-arrow-left me-1"></i>Relatórios
                </a>
                <h1 class="h3 mt-2 mb-1">Acervo documental</h1>
                <p class="text-body-secondary mb-0">
                    Consulte os documentos que compõem o acervo e sua cobertura digital.
                </p>
            </div>

            <?php if ($pode_exportar): ?>
                <?php
                $query_exportacao = http_build_query($filtros);
                ?>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light border"
                        href="<?= base_url('relatorio/acervo/pdf'); ?>?<?= htmlspecialchars($query_exportacao, ENT_QUOTES, 'UTF-8'); ?>"
                        target="_blank" rel="noopener">
                        <i class="fa-regular fa-file-pdf me-2"></i>PDF
                    </a>
                    <a class="btn btn-success"
                        href="<?= base_url('relatorio/acervo/excel'); ?>?<?= htmlspecialchars($query_exportacao, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fa-regular fa-file-excel me-2"></i>Excel
                    </a>
                </div>
            <?php endif; ?>
        </header>

        <section class="card border shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label class="form-label" for="termo">Buscar</label>
                            <input class="form-control" id="termo" name="termo" type="search"
                                placeholder="Protocolo, título, identificação, tipo ou localização"
                                value="<?= htmlspecialchars($filtros['termo'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label" for="tipo_documento_codigo">Tipo de documento</label>
                            <select class="form-select" id="tipo_documento_codigo" name="tipo_documento_codigo">
                                <option value="">Todos</option>
                                <?php foreach ($tipos_documento as $tipo_documento): ?>
                                    <option value="<?= $tipo_documento['codigo']; ?>"
                                        <?= (string) $filtros['tipo_documento_codigo'] === (string) $tipo_documento['codigo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label" for="localizacao_codigo">Localização</label>
                            <select class="form-select" id="localizacao_codigo" name="localizacao_codigo">
                                <option value="">Todas</option>
                                <?php foreach ($localizacoes as $localizacao): ?>
                                    <option value="<?= $localizacao['codigo']; ?>"
                                        <?= (string) $filtros['localizacao_codigo'] === (string) $localizacao['codigo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars(
                                            $localizacao['classificacao'] . ' — ' . $localizacao['nome'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label" for="digitalizacao">Digitalização</label>
                            <select class="form-select" id="digitalizacao" name="digitalizacao">
                                <option value="">Todos</option>
                                <option value="com_arquivo" <?= $filtros['digitalizacao'] === 'com_arquivo' ? 'selected' : ''; ?>>
                                    Com arquivo
                                </option>
                                <option value="sem_arquivo" <?= $filtros['digitalizacao'] === 'sem_arquivo' ? 'selected' : ''; ?>>
                                    Sem arquivo
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label" for="data_inicio">Cadastro inicial</label>
                            <input class="form-control" id="data_inicio" name="data_inicio" type="date"
                                value="<?= htmlspecialchars($filtros['data_inicio'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <label class="form-label" for="data_fim">Cadastro final</label>
                            <input class="form-control" id="data_fim" name="data_fim" type="date"
                                value="<?= htmlspecialchars($filtros['data_fim'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar
                            </button>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <a class="btn btn-light border w-100" href="<?= base_url('relatorio/acervo'); ?>">
                                Limpar filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body">
                        <span class="text-body-secondary small">Documentos encontrados</span>
                        <strong class="d-block fs-4 mt-1">
                            <?= number_format($resumo['total'], 0, ',', '.'); ?>
                        </strong>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body">
                        <span class="text-body-secondary small">Com arquivo digital</span>
                        <strong class="d-block fs-4 mt-1">
                            <?= number_format($resumo['com_arquivo'], 0, ',', '.'); ?>
                        </strong>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body">
                        <span class="text-body-secondary small">Sem arquivo digital</span>
                        <strong class="d-block fs-4 mt-1">
                            <?= number_format($resumo['sem_arquivo'], 0, ',', '.'); ?>
                        </strong>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body">
                        <span class="text-body-secondary small">Cobertura digital</span>
                        <strong class="d-block fs-4 mt-1">
                            <?= number_format($resumo['cobertura_percentual'], 1, ',', '.'); ?>%
                        </strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="card border shadow-sm">
            <div class="card-header bg-white py-3">
                <span class="fw-semibold">
                    <?= number_format($total_documentos, 0, ',', '.'); ?>
                </span>
                <span class="text-body-secondary">documentos encontrados</span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light text-uppercase">
                        <tr class="small text-secondary">
                            <th class="px-3 py-3">Protocolo</th>
                            <th>Documento</th>
                            <th>Tipo</th>
                            <th>Localização</th>
                            <th>Data documento</th>
                            <th>Cadastro</th>
                            <th class="text-center">Digital</th>
                            <th class="px-3 text-end">Arquivos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($documentos): ?>
                            <?php foreach ($documentos as $documento): ?>
                                <tr>
                                    <td class="ps-3 font-monospace small">
                                        <?= htmlspecialchars($documento['protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td>
                                        <span class="d-block fw-semibold">
                                            <?= htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <?php if (!empty($documento['numero_identificacao'])): ?>
                                            <small class="text-body-secondary">
                                                <?= htmlspecialchars($documento['numero_identificacao'], ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($documento['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="font-monospace small text-body-secondary">
                                            <?= htmlspecialchars($documento['localizacao_classificacao'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <span class="d-block">
                                            <?= htmlspecialchars($documento['localizacao'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= !empty($documento['data_documento'])
                                            ? date('d/m/Y', strtotime($documento['data_documento']))
                                            : '-'; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($documento['cadastro'])); ?></td>
                                    <td class="text-center">
                                        <?php if ((int) $documento['total_arquivos'] > 0): ?>
                                            <span class="badge text-bg-success">Com arquivo</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-light border text-body-secondary">Sem arquivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <?= number_format((int) $documento['total_arquivos'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="text-center text-body-secondary py-5" colspan="8">
                                    Nenhum documento encontrado para os filtros informados.
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

            <nav aria-label="Paginação do relatório de acervo" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $pagina_atual <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link"
                            href="<?= htmlspecialchars($gerar_url($pagina_atual - 1), ENT_QUOTES, 'UTF-8'); ?>">
                            Anterior
                        </a>
                    </li>

                    <?php if ($inicio > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= htmlspecialchars($gerar_url(1), ENT_QUOTES, 'UTF-8'); ?>">1</a>
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

    <?php $this->load->view('js'); ?>
</body>

</html>
