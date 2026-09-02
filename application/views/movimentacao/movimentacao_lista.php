<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Rastreabilidade de documentos no sistema e-Doc">
    <title>Movimentações | e-Doc</title>
    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="mb-4">
            <h1 class="h3 mb-1">Movimentações</h1>
            <p class="text-body-secondary mb-0">
                Consulte transferências, retiradas, devoluções e a custódia dos documentos.
            </p>
        </header>

        <nav aria-label="Caminho da movimentação" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fa-solid fa-right-left me-1" aria-hidden="true"></i>
                    Movimentações
                </li>
            </ol>
        </nav>

        <section class="card border shadow-sm mb-4" aria-labelledby="filtros-title">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3" id="filtros-title">Filtros</h2>
                <form action="<?= base_url('movimentacao'); ?>" method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label" for="termo">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                                </span>
                                <input class="form-control" id="termo" name="termo"
                                    placeholder="Movimentação, documento ou responsável" type="search"
                                    value="<?= htmlspecialchars($filtro_termo, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                            <label class="form-label" for="tipo">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="">Todos</option>
                                <option value="CADASTRO" <?= $filtro_tipo === 'CADASTRO' ? 'selected' : ''; ?>>Cadastro</option>
                                <option value="TRANSFERENCIA" <?= $filtro_tipo === 'TRANSFERENCIA' ? 'selected' : ''; ?>>Transferência</option>
                                <option value="RETIRADA" <?= $filtro_tipo === 'RETIRADA' ? 'selected' : ''; ?>>Retirada</option>
                                <option value="DEVOLUCAO" <?= $filtro_tipo === 'DEVOLUCAO' ? 'selected' : ''; ?>>Devolução</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                            <label class="form-label" for="situacao">Situação</label>
                            <select class="form-select" id="situacao" name="situacao">
                                <option value="">Todas</option>
                                <option value="aberta" <?= $filtro_situacao === 'aberta' ? 'selected' : ''; ?>>Em aberto</option>
                                <option value="atrasada" <?= $filtro_situacao === 'atrasada' ? 'selected' : ''; ?>>Em atraso</option>
                                <option value="concluida" <?= $filtro_situacao === 'concluida' ? 'selected' : ''; ?>>Concluída</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label" for="data_inicio">Data inicial</label>
                            <input class="form-control" id="data_inicio" name="data_inicio" type="date"
                                value="<?= htmlspecialchars($filtro_data_inicio, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label" for="data_fim">Data final</label>
                            <input class="form-control" id="data_fim" name="data_fim" type="date"
                                value="<?= htmlspecialchars($filtro_data_fim, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="col-12 d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a class="btn btn-light border" href="<?= base_url('movimentacao'); ?>">Limpar</a>
                            <button class="btn btn-primary" type="submit">
                                <i class="fa-solid fa-filter me-2"></i>Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <?php if ($movimentacoes): ?>
            <section class="card border shadow-sm" aria-labelledby="lista-title">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-semibold mb-1" id="lista-title">Histórico de movimentações</h2>
                    <p class="small text-secondary mb-0">
                        <?= $total_movimentacoes; ?> movimentações encontradas
                    </p>
                </div>

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
                                <?php
                                $retirada_aberta = $movimentacao['tipo_movimentacao'] === 'RETIRADA'
                                    && empty($movimentacao['data_devolucao']);
                                $atrasada = $retirada_aberta
                                    && !empty($movimentacao['data_prevista_devolucao'])
                                    && $movimentacao['data_prevista_devolucao'] < date('Y-m-d');
                                $tipos = [
                                    'CADASTRO' => 'Cadastro',
                                    'TRANSFERENCIA' => 'Transferência',
                                    'RETIRADA' => 'Retirada',
                                    'DEVOLUCAO' => 'Devolução'
                                ];
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <span class="font-monospace fw-semibold d-block">
                                            <?= htmlspecialchars($movimentacao['protocolo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <small class="text-secondary">
                                            <?= date('d/m/Y H:i', strtotime($movimentacao['data_movimentacao'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold d-block">
                                            <?= htmlspecialchars($movimentacao['documento_titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <small class="font-monospace text-secondary">
                                            <?= htmlspecialchars($movimentacao['documento_protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(
                                            $tipos[$movimentacao['tipo_movimentacao']] ?? $movimentacao['tipo_movimentacao'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                        <?php if (!empty($movimentacao['observacao'])): ?>
                                            <small class="text-secondary d-block">
                                                <?= htmlspecialchars($movimentacao['observacao'], ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="d-block">
                                            <span class="text-secondary">De:</span>
                                            <?= htmlspecialchars(
                                                !empty($movimentacao['localizacao_origem'])
                                                    ? $movimentacao['localizacao_origem_classificacao'] . ' - ' . $movimentacao['localizacao_origem']
                                                    : 'Externo',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </small>
                                        <small class="d-block">
                                            <span class="text-secondary">Para:</span>
                                            <?= htmlspecialchars(
                                                !empty($movimentacao['localizacao_destino'])
                                                    ? $movimentacao['localizacao_destino_classificacao'] . ' - ' . $movimentacao['localizacao_destino']
                                                    : 'Sob responsabilidade',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($movimentacao['responsavel_nome'] ?? 'Não se aplica', ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if (!empty($movimentacao['responsavel_contato'])): ?>
                                            <small class="text-secondary d-block">
                                                <?= htmlspecialchars($movimentacao['responsavel_contato'], ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($atrasada): ?>
                                            <span class="badge text-bg-danger">Em atraso</span>
                                        <?php elseif ($retirada_aberta): ?>
                                            <span class="badge text-bg-warning">Em aberto</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-success">Concluída</span>
                                        <?php endif; ?>
                                        <?php if (!empty($movimentacao['data_prevista_devolucao'])): ?>
                                            <small class="text-secondary d-block mt-1">
                                                Prevista: <?= date('d/m/Y', strtotime($movimentacao['data_prevista_devolucao'])); ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!empty($movimentacao['data_devolucao'])): ?>
                                            <small class="text-secondary d-block mt-1">
                                                Devolvida: <?= date('d/m/Y H:i', strtotime($movimentacao['data_devolucao'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($movimentacao['usuario_nome'] ?? 'Sistema', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="pe-3 text-end">
                                        <?php if (empty($movimentacao['documento_exclusao'])): ?>
                                            <a class="btn btn-sm btn-light border"
                                                href="<?= base_url('documento/detalhes/' . $movimentacao['documento_codigo']); ?>"
                                                aria-label="Acessar documento">
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

                <div class="card-footer bg-white py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <p class="small text-secondary mb-0">
                            Exibindo <?= $offset; ?> –
                            <?= min($offset + $limite - 1, $total_movimentacoes); ?> de
                            <?= $total_movimentacoes; ?> movimentações
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
                            <nav aria-label="Paginação de movimentações">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item">
                                        <a class="page-link <?= $pagina_atual <= 1 ? 'disabled' : ''; ?>"
                                            href="<?= $pagina_atual > 1 ? $gerar_url($pagina_atual - 1) : '#'; ?>">
                                            Anterior
                                        </a>
                                    </li>
                                    <?php $adjacentes = 2; ?>
                                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                        <?php if ($i == 1 || $i == $total_paginas || ($i >= $pagina_atual - $adjacentes && $i <= $pagina_atual + $adjacentes)): ?>
                                            <li class="page-item <?= $i == $pagina_atual ? 'active' : ''; ?>">
                                                <a class="page-link" href="<?= $gerar_url($i); ?>"><?= $i; ?></a>
                                            </li>
                                        <?php elseif ($i == 2 || $i == $total_paginas - 1): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link border-0 bg-transparent text-muted">&hellip;</span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <li class="page-item">
                                        <a class="page-link <?= $pagina_atual >= $total_paginas ? 'disabled' : ''; ?>"
                                            href="<?= $pagina_atual < $total_paginas ? $gerar_url($pagina_atual + 1) : '#'; ?>">
                                            Próximo
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="card border shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fa-solid fa-right-left fa-2x text-secondary mb-3"></i>
                    <h2 class="h5">Nenhuma movimentação encontrada</h2>
                    <p class="text-secondary mb-0">Ajuste os filtros ou movimente um documento.</p>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
