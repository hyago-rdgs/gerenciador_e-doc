<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Relatório de digitalização do e-Doc">
    <title>Digitalização | e-Doc</title>
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
                <h1 class="h3 mt-2 mb-1">Digitalização</h1>
                <p class="text-body-secondary mb-0">
                    Analise cobertura digital, versões e espaço de armazenamento.
                </p>
            </div>

            <?php if ($pode_exportar): ?>
                <?php $query_exportacao = http_build_query($filtros); ?>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light border"
                        href="<?= base_url('relatorio/digitalizacao/pdf'); ?>?<?= htmlspecialchars($query_exportacao, ENT_QUOTES, 'UTF-8'); ?>"
                        target="_blank" rel="noopener">
                        <i class="fa-regular fa-file-pdf me-2"></i>PDF
                    </a>
                    <a class="btn btn-success"
                        href="<?= base_url('relatorio/digitalizacao/excel'); ?>?<?= htmlspecialchars($query_exportacao, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fa-regular fa-file-excel me-2"></i>Excel
                    </a>
                </div>
            <?php endif; ?>
        </header>

        <section class="card border shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label" for="termo">Buscar</label>
                            <input class="form-control" id="termo" name="termo" type="search"
                                placeholder="Documento, identificação, tipo ou localização"
                                value="<?= htmlspecialchars($filtros['termo'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                            <label class="form-label" for="situacao">Situação digital</label>
                            <select class="form-select" id="situacao" name="situacao">
                                <option value="">Todas</option>
                                <option value="com_arquivo" <?= $filtros['situacao'] === 'com_arquivo' ? 'selected' : ''; ?>>Com arquivo</option>
                                <option value="sem_arquivo" <?= $filtros['situacao'] === 'sem_arquivo' ? 'selected' : ''; ?>>Sem arquivo</option>
                                <option value="multiplas_versoes" <?= $filtros['situacao'] === 'multiplas_versoes' ? 'selected' : ''; ?>>Com múltiplas versões</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                            <label class="form-label" for="tipo_documento_codigo">Tipo documental</label>
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
                                        <?= htmlspecialchars($localizacao['classificacao'] . ' — ' . $localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="data_inicio">Cadastro inicial</label>
                            <input class="form-control" id="data_inicio" name="data_inicio" type="date"
                                value="<?= htmlspecialchars($filtros['data_inicio'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="data_fim">Cadastro final</label>
                            <input class="form-control" id="data_fim" name="data_fim" type="date"
                                value="<?= htmlspecialchars($filtros['data_fim'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar
                            </button>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <a class="btn btn-light border w-100" href="<?= base_url('relatorio/digitalizacao'); ?>">
                                Limpar filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <?php
            $indicadores = [
                ['Documentos', number_format($resumo['total'], 0, ',', '.')],
                ['Com arquivo', number_format($resumo['com_arquivo'], 0, ',', '.')],
                ['Sem arquivo', number_format($resumo['sem_arquivo'], 0, ',', '.')],
                ['Cobertura', number_format($resumo['cobertura_percentual'], 1, ',', '.') . '%'],
                ['Arquivos atuais', number_format($resumo['total_arquivos'], 0, ',', '.')],
                ['Versões armazenadas', number_format($resumo['total_versoes'], 0, ',', '.')],
                ['Múltiplas versões', number_format($resumo['multiplas_versoes'], 0, ',', '.')],
                ['Armazenamento', $resumo['tamanho_label']]
            ];
            ?>
            <?php foreach ($indicadores as $indicador): ?>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <span class="text-body-secondary small"><?= $indicador[0]; ?></span>
                            <strong class="d-block fs-4 mt-1"><?= $indicador[1]; ?></strong>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="card border shadow-sm">
            <div class="card-header bg-white py-3">
                <strong><?= number_format($total_documentos, 0, ',', '.'); ?></strong>
                <span class="text-body-secondary">documentos encontrados</span>
            </div>

            <?php if ($documentos): ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3">Documento</th>
                                <th>Tipo</th>
                                <th>Localização</th>
                                <th>Situação</th>
                                <th class="text-end">Arquivos</th>
                                <th class="text-end">Versões</th>
                                <th>Armazenamento</th>
                                <th>Último arquivo</th>
                                <th class="px-3 text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $documento): ?>
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-semibold d-block">
                                            <?= htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <small class="font-monospace text-body-secondary">
                                            <?= htmlspecialchars($documento['protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </small>
                                        <?php if (!empty($documento['numero_identificacao'])): ?>
                                            <small class="text-body-secondary d-block">
                                                <?= htmlspecialchars($documento['numero_identificacao'], ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($documento['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($documento['localizacao_label'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="badge <?= (int) $documento['total_arquivos'] > 0 ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                            <?= htmlspecialchars($documento['situacao_digital'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="text-end"><?= number_format((int) $documento['total_arquivos'], 0, ',', '.'); ?></td>
                                    <td class="text-end">
                                        <?= number_format((int) $documento['total_versoes'], 0, ',', '.'); ?>
                                        <?php if ((int) $documento['total_versoes'] > (int) $documento['total_arquivos']): ?>
                                            <small class="text-primary d-block">Versionado</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($documento['tamanho_label'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?= !empty($documento['ultimo_arquivo_em'])
                                            ? date('d/m/Y H:i', strtotime($documento['ultimo_arquivo_em']))
                                            : '—'; ?>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a class="btn btn-sm btn-light border"
                                            href="<?= base_url('documento/detalhes/' . $documento['codigo']); ?>">
                                            Acessar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card-body text-center py-5">
                    <i class="fa-solid fa-file-circle-check fa-2x text-body-secondary mb-3"></i>
                    <h2 class="h5">Nenhum documento encontrado</h2>
                    <p class="text-body-secondary mb-0">Ajuste os filtros para consultar outro conjunto.</p>
                </div>
            <?php endif; ?>
        </section>

        <?php $this->load->view('relatorio/relatorio_paginacao', [
            'filtros' => $filtros,
            'pagina_atual' => $pagina_atual,
            'total_paginas' => $total_paginas,
            'aria_paginacao' => 'Paginação do relatório de digitalização'
        ]); ?>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
