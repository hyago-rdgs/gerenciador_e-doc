<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Relatório de custódia e retiradas do e-Doc">
    <title>Custódia e Retiradas | e-Doc</title>
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
                <h1 class="h3 mt-2 mb-1">Custódia e retiradas</h1>
                <p class="text-body-secondary mb-0">
                    Acompanhe responsáveis, previsões, devoluções e atrasos.
                </p>
            </div>

            <?php if ($pode_exportar): ?>
                <?php $query_exportacao = http_build_query($filtros); ?>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light border"
                        href="<?= base_url('relatorio/custodia/pdf'); ?>?<?= htmlspecialchars($query_exportacao, ENT_QUOTES, 'UTF-8'); ?>"
                        target="_blank" rel="noopener">
                        <i class="fa-regular fa-file-pdf me-2"></i>PDF
                    </a>
                    <a class="btn btn-success"
                        href="<?= base_url('relatorio/custodia/excel'); ?>?<?= htmlspecialchars($query_exportacao, ENT_QUOTES, 'UTF-8'); ?>">
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
                                placeholder="Retirada, documento, responsável ou contato"
                                value="<?= htmlspecialchars($filtros['termo'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                            <label class="form-label" for="situacao">Situação</label>
                            <select class="form-select" id="situacao" name="situacao">
                                <option value="">Todas</option>
                                <option value="aberta" <?= $filtros['situacao'] === 'aberta' ? 'selected' : ''; ?>>Em aberto</option>
                                <option value="atrasada" <?= $filtros['situacao'] === 'atrasada' ? 'selected' : ''; ?>>Em atraso</option>
                                <option value="vence_hoje" <?= $filtros['situacao'] === 'vence_hoje' ? 'selected' : ''; ?>>Vence hoje</option>
                                <option value="sem_previsao" <?= $filtros['situacao'] === 'sem_previsao' ? 'selected' : ''; ?>>Sem previsão</option>
                                <option value="devolvida" <?= $filtros['situacao'] === 'devolvida' ? 'selected' : ''; ?>>Devolvida</option>
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
                            <label class="form-label" for="localizacao_codigo">Localização de origem</label>
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

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label" for="usuario_codigo">Registrado por</label>
                            <select class="form-select" id="usuario_codigo" name="usuario_codigo">
                                <option value="">Todos</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['codigo']; ?>"
                                        <?= (string) $filtros['usuario_codigo'] === (string) $usuario['codigo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="data_inicio">Retirada inicial</label>
                            <input class="form-control" id="data_inicio" name="data_inicio" type="date"
                                value="<?= htmlspecialchars($filtros['data_inicio'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="data_fim">Retirada final</label>
                            <input class="form-control" id="data_fim" name="data_fim" type="date"
                                value="<?= htmlspecialchars($filtros['data_fim'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar
                            </button>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <a class="btn btn-light border w-100" href="<?= base_url('relatorio/custodia'); ?>">
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
                ['Abertas', $resumo['abertas'], 'text-body'],
                ['Em atraso', $resumo['atrasadas'], 'text-danger'],
                ['Vencem hoje', $resumo['vencem_hoje'], 'text-warning'],
                ['Sem previsão', $resumo['sem_previsao'], 'text-secondary']
            ];
            ?>
            <?php foreach ($indicadores as $indicador): ?>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <span class="text-body-secondary small"><?= $indicador[0]; ?></span>
                            <strong class="d-block fs-4 mt-1 <?= $indicador[2]; ?>">
                                <?= number_format($indicador[1], 0, ',', '.'); ?>
                            </strong>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="card border shadow-sm">
            <div class="card-header bg-white py-3">
                <strong><?= number_format($total_custodias, 0, ',', '.'); ?></strong>
                <span class="text-body-secondary">retiradas encontradas</span>
            </div>

            <?php if ($custodias): ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3">Retirada</th>
                                <th>Documento</th>
                                <th>Origem</th>
                                <th>Responsável</th>
                                <th>Previsão / devolução</th>
                                <th>Situação</th>
                                <th>Registrado por</th>
                                <th class="px-3 text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($custodias as $custodia): ?>
                                <?php
                                $classe = $custodia['situacao_label'] === 'Em atraso'
                                    ? 'text-bg-danger'
                                    : ($custodia['situacao_label'] === 'Em aberto'
                                        ? 'text-bg-warning'
                                        : 'text-bg-success');
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <span class="font-monospace fw-semibold d-block">
                                            <?= htmlspecialchars($custodia['protocolo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <small class="text-body-secondary">
                                            <?= date('d/m/Y H:i', strtotime($custodia['data_movimentacao'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold d-block">
                                            <?= htmlspecialchars($custodia['documento_titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <small class="font-monospace text-body-secondary">
                                            <?= htmlspecialchars($custodia['documento_protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </small>
                                        <small class="text-body-secondary d-block">
                                            <?= htmlspecialchars($custodia['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($custodia['origem_label'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="d-block"><?= htmlspecialchars($custodia['responsavel_nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if (!empty($custodia['responsavel_contato'])): ?>
                                            <small class="text-body-secondary">
                                                <?= htmlspecialchars($custodia['responsavel_contato'], ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="d-block">
                                            Prevista:
                                            <?= !empty($custodia['data_prevista_devolucao'])
                                                ? date('d/m/Y', strtotime($custodia['data_prevista_devolucao']))
                                                : 'Não informada'; ?>
                                        </small>
                                        <?php if (!empty($custodia['data_devolucao'])): ?>
                                            <small class="text-body-secondary d-block">
                                                Devolvida: <?= date('d/m/Y H:i', strtotime($custodia['data_devolucao'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $classe; ?>">
                                            <?= htmlspecialchars($custodia['situacao_label'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <small class="text-body-secondary d-block mt-1">
                                            <?= number_format((int) $custodia['dias_custodia'], 0, ',', '.'); ?> dias em custódia
                                        </small>
                                        <?php if ((int) $custodia['dias_atraso'] > 0): ?>
                                            <small class="text-danger d-block">
                                                <?= number_format((int) $custodia['dias_atraso'], 0, ',', '.'); ?> dias de atraso
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($custodia['usuario_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="pe-3 text-end">
                                        <?php if (empty($custodia['documento_exclusao'])): ?>
                                            <a class="btn btn-sm btn-light border"
                                                href="<?= base_url('documento/detalhes/' . $custodia['documento_codigo']); ?>">
                                                Acessar
                                            </a>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Documento excluído</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card-body text-center py-5">
                    <i class="fa-solid fa-hand-holding fa-2x text-body-secondary mb-3"></i>
                    <h2 class="h5">Nenhuma retirada encontrada</h2>
                    <p class="text-body-secondary mb-0">Ajuste os filtros para consultar outra situação.</p>
                </div>
            <?php endif; ?>
        </section>

        <?php $this->load->view('relatorio/relatorio_paginacao', [
            'filtros' => $filtros,
            'pagina_atual' => $pagina_atual,
            'total_paginas' => $total_paginas,
            'aria_paginacao' => 'Paginação do relatório de custódia'
        ]); ?>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
