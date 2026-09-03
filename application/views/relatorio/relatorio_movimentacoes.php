<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Relatório de movimentações do e-Doc">
    <title>Relatório de Movimentações | e-Doc</title>
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
                <h1 class="h3 mt-2 mb-1">Movimentações</h1>
                <p class="text-body-secondary mb-0">
                    Consulte transferências, retiradas, devoluções e responsáveis pelas movimentações.
                </p>
            </div>

            <?php if ($pode_exportar): ?>
                <?php $query_exportacao = http_build_query($filtros); ?>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light border"
                        href="<?= base_url('relatorio/movimentacoes/pdf'); ?>?<?= htmlspecialchars($query_exportacao, ENT_QUOTES, 'UTF-8'); ?>"
                        target="_blank" rel="noopener">
                        <i class="fa-regular fa-file-pdf me-2"></i>PDF
                    </a>
                    <a class="btn btn-success"
                        href="<?= base_url('relatorio/movimentacoes/excel'); ?>?<?= htmlspecialchars($query_exportacao, ENT_QUOTES, 'UTF-8'); ?>">
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
                                placeholder="Movimentação, documento ou responsável"
                                value="<?= htmlspecialchars($filtros['termo'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="tipo_movimentacao">Tipo</label>
                            <select class="form-select" id="tipo_movimentacao" name="tipo_movimentacao">
                                <option value="">Todos</option>
                                <option value="CADASTRO" <?= $filtros['tipo_movimentacao'] === 'CADASTRO' ? 'selected' : ''; ?>>Cadastro</option>
                                <option value="TRANSFERENCIA" <?= $filtros['tipo_movimentacao'] === 'TRANSFERENCIA' ? 'selected' : ''; ?>>Transferência</option>
                                <option value="RETIRADA" <?= $filtros['tipo_movimentacao'] === 'RETIRADA' ? 'selected' : ''; ?>>Retirada</option>
                                <option value="DEVOLUCAO" <?= $filtros['tipo_movimentacao'] === 'DEVOLUCAO' ? 'selected' : ''; ?>>Devolução</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="situacao">Situação</label>
                            <select class="form-select" id="situacao" name="situacao">
                                <option value="">Todas</option>
                                <option value="aberta" <?= $filtros['situacao'] === 'aberta' ? 'selected' : ''; ?>>Em aberto</option>
                                <option value="atrasada" <?= $filtros['situacao'] === 'atrasada' ? 'selected' : ''; ?>>Em atraso</option>
                                <option value="concluida" <?= $filtros['situacao'] === 'concluida' ? 'selected' : ''; ?>>Concluída</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label" for="localizacao_codigo">Localização envolvida</label>
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
                            <label class="form-label" for="data_inicio">Data inicial</label>
                            <input class="form-control" id="data_inicio" name="data_inicio" type="date"
                                value="<?= htmlspecialchars($filtros['data_inicio'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="data_fim">Data final</label>
                            <input class="form-control" id="data_fim" name="data_fim" type="date"
                                value="<?= htmlspecialchars($filtros['data_fim'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar
                            </button>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <a class="btn btn-light border w-100" href="<?= base_url('relatorio/movimentacoes'); ?>">
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
                'Movimentações encontradas' => $resumo['total'],
                'Documentos movimentados' => $resumo['documentos'],
                'Transferências' => $resumo['transferencias'],
                'Retiradas' => $resumo['retiradas']
            ];
            ?>
            <?php foreach ($indicadores as $rotulo => $valor): ?>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <span class="text-body-secondary small"><?= $rotulo; ?></span>
                            <strong class="d-block fs-4 mt-1">
                                <?= number_format($valor, 0, ',', '.'); ?>
                            </strong>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="card border shadow-sm">
            <div class="card-header bg-white py-3">
                <span class="fw-semibold">
                    <?= number_format($total_movimentacoes, 0, ',', '.'); ?>
                </span>
                <span class="text-body-secondary">movimentações encontradas</span>
            </div>

            <?php if ($movimentacoes): ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3">Movimentação</th>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Origem / destino</th>
                                <th>Responsável</th>
                                <th>Situação</th>
                                <th>Registrado por</th>
                                <th class="px-3 text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimentacoes as $movimentacao): ?>
                                <tr>
                                    <td class="ps-3">
                                        <span class="font-monospace fw-semibold d-block">
                                            <?= htmlspecialchars($movimentacao['protocolo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <small class="text-body-secondary">
                                            <?= date('d/m/Y H:i', strtotime($movimentacao['data_movimentacao'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold d-block">
                                            <?= htmlspecialchars($movimentacao['documento_titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <small class="font-monospace text-body-secondary">
                                            <?= htmlspecialchars($movimentacao['documento_protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($movimentacao['tipo_label'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if (!empty($movimentacao['observacao'])): ?>
                                            <small class="text-body-secondary d-block">
                                                <?= htmlspecialchars($movimentacao['observacao'], ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="d-block">
                                            <span class="text-body-secondary">De:</span>
                                            <?= htmlspecialchars($movimentacao['origem_label'], ENT_QUOTES, 'UTF-8'); ?>
                                        </small>
                                        <small class="d-block">
                                            <span class="text-body-secondary">Para:</span>
                                            <?= htmlspecialchars($movimentacao['destino_label'], ENT_QUOTES, 'UTF-8'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(
                                            !empty($movimentacao['responsavel_nome'])
                                                ? $movimentacao['responsavel_nome']
                                                : 'Não se aplica',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                        <?php if (!empty($movimentacao['responsavel_contato'])): ?>
                                            <small class="text-body-secondary d-block">
                                                <?= htmlspecialchars($movimentacao['responsavel_contato'], ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $classe_situacao = $movimentacao['situacao_label'] === 'Em atraso'
                                            ? 'text-bg-danger'
                                            : ($movimentacao['situacao_label'] === 'Em aberto'
                                                ? 'text-bg-warning'
                                                : 'text-bg-success');
                                        ?>
                                        <span class="badge <?= $classe_situacao; ?>">
                                            <?= htmlspecialchars($movimentacao['situacao_label'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <?php if (!empty($movimentacao['data_prevista_devolucao'])): ?>
                                            <small class="text-body-secondary d-block mt-1">
                                                Prevista: <?= date('d/m/Y', strtotime($movimentacao['data_prevista_devolucao'])); ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!empty($movimentacao['data_devolucao'])): ?>
                                            <small class="text-body-secondary d-block mt-1">
                                                Devolvida: <?= date('d/m/Y H:i', strtotime($movimentacao['data_devolucao'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($movimentacao['usuario_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="pe-3 text-end">
                                        <?php if (empty($movimentacao['documento_exclusao'])): ?>
                                            <a class="btn btn-sm btn-light border"
                                                href="<?= base_url('documento/detalhes/' . $movimentacao['documento_codigo']); ?>">
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
                    <i class="fa-solid fa-arrow-right-arrow-left fa-2x text-body-secondary mb-3"></i>
                    <h2 class="h5">Nenhuma movimentação encontrada</h2>
                    <p class="text-body-secondary mb-0">Ajuste os filtros para consultar outro período.</p>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($total_paginas > 1): ?>
            <?php
            $parametros = $filtros;
            $gerar_url = function ($pagina) use ($parametros) {
                $parametros['pagina'] = $pagina;
                return '?' . http_build_query($parametros);
            };
            $inicio = max(1, $pagina_atual - 2);
            $fim = min($total_paginas, $pagina_atual + 2);
            ?>
            <nav class="mt-4" aria-label="Paginação do relatório de movimentações">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item <?= $pagina_atual <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?= htmlspecialchars($gerar_url($pagina_atual - 1), ENT_QUOTES, 'UTF-8'); ?>">Anterior</a>
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
                            <a class="page-link" href="<?= htmlspecialchars($gerar_url($pagina), ENT_QUOTES, 'UTF-8'); ?>">
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
                            <a class="page-link" href="<?= htmlspecialchars($gerar_url($total_paginas), ENT_QUOTES, 'UTF-8'); ?>">
                                <?= $total_paginas; ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item <?= $pagina_atual >= $total_paginas ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?= htmlspecialchars($gerar_url($pagina_atual + 1), ENT_QUOTES, 'UTF-8'); ?>">Próxima</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
