<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Pesquisa de documentos por localização no sistema e-Doc">
    <title>Pesquisa por localização | e-Doc</title>
    <?php $this->load->view('css'); ?>
</head>
<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1">Pesquisa por localização</h1>
                <p class="text-body-secondary mb-0">
                    Navegue pela estrutura e consulte os documentos armazenados em cada local.
                </p>
            </section>
            <a class="btn btn-light border flex-shrink-0" href="<?= base_url('pesquisa/avancada'); ?>">
                <i class="fa-solid fa-sliders me-2"></i>
                Pesquisa avançada
            </a>
        </header>

        <nav aria-label="Caminho da localização" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <?php if ($localizacao): ?>
                        <a class="text-decoration-none" href="<?= base_url('pesquisa/localizacao'); ?>">
                            <i class="fa-solid fa-folder-tree me-1"></i>
                            Localizações
                        </a>
                    <?php else: ?>
                        <span class="active">
                            <i class="fa-solid fa-folder-tree me-1"></i>
                            Localizações
                        </span>
                    <?php endif; ?>
                </li>

                <?php foreach ($caminho as $indice => $item): ?>
                    <li class="breadcrumb-item <?= $indice === count($caminho) - 1 ? 'active' : ''; ?>">
                        <?php if ($indice === count($caminho) - 1): ?>
                            <?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php else: ?>
                            <a class="text-decoration-none"
                                href="<?= base_url('pesquisa/localizacao/' . $item['codigo']); ?>">
                                <?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>

        <?php if ($localizacao): ?>
            <section class="card border shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-3">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <div>
                            <span class="badge text-bg-light border mb-2">
                                <?= htmlspecialchars($localizacao['tipo_localizacao'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <h2 class="h4 mb-1">
                                <?= htmlspecialchars($localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            </h2>
                            <p class="text-body-secondary mb-0">
                                <?= htmlspecialchars($localizacao['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="card border shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-semibold mb-1">
                    <?= $localizacao ? 'Sublocalizações' : 'Localizações principais'; ?>
                </h2>
                <p class="small text-secondary mb-0">
                    <?= count($localizacoes); ?> localizações disponíveis
                </p>
            </div>

            <?php if ($localizacoes): ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3">Localização</th>
                                <th>Tipo</th>
                                <th>Classificação</th>
                                <th class="text-center">Documentos</th>
                                <th class="px-3 text-end">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($localizacoes as $item): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold">
                                        <?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light border fw-normal">
                                            <?= htmlspecialchars($item['tipo_localizacao'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-monospace small">
                                            <?= htmlspecialchars($item['classificacao'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= (int) $item['total_documentos']; ?></td>
                                    <td class="pe-3 text-end">
                                        <a class="btn btn-sm btn-primary"
                                            href="<?= base_url('pesquisa/localizacao/' . $item['codigo']); ?>">
                                            Acessar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card-body text-center py-4">
                    <p class="text-body-secondary mb-0">Nenhuma sublocalização disponível.</p>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($localizacao): ?>
            <section class="card border shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-semibold mb-1">Documentos desta localização</h2>
                    <p class="small text-secondary mb-0">
                        <?= count($documentos); ?> documentos encontrados
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
                                    <th class="text-center">Status</th>
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
                                        <td class="text-center">
                                            <span class="badge <?= $documento['ativo'] ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                                <?= $documento['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <?php if ($this->controle_acesso->tem_permissao('documentos.visualizar')): ?>
                                                <a class="btn btn-sm btn-primary"
                                                    href="<?= base_url('documento/detalhes/' . $documento['codigo']); ?>">
                                                    Visualizar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="card-body text-center py-5">
                        <i class="fa-regular fa-folder-open fa-2x text-secondary mb-3"></i>
                        <h3 class="h5">Nenhum documento nesta localização</h3>
                        <p class="text-secondary mb-0">
                            Acesse uma sublocalização para continuar a navegação.
                        </p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <?php $this->load->view('js'); ?>
</body>
</html>
