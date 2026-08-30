<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="Consulta de localização do sistema e-Doc">
    <title>Consulta de localização | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php if ($autenticado): ?>
        <?php $this->load->view('nav'); ?>
    <?php else: ?>
        <header class="bg-white border-bottom">
            <nav class="navbar" aria-label="Identificação do sistema">
                <div class="container-fluid px-3 px-lg-4">
                    <span class="navbar-brand d-flex align-items-center gap-2 fw-semibold mb-0">
                        <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded p-2"
                            aria-hidden="true">
                            <i class="fa-solid fa-file-shield"></i>
                        </span>
                        <span>e-Doc</span>
                    </span>
                </div>
            </nav>
        </header>
    <?php endif; ?>

    <main class="container py-4 py-lg-5">
        <?php if (!$autenticado): ?>
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <section class="card border shadow-sm">
                        <div class="card-body text-center p-4 p-lg-5">
                            <div class="mb-3">
                                <i class="fa-solid fa-qrcode fa-2x text-primary" aria-hidden="true"></i>
                            </div>

                            <span class="badge text-bg-success mb-3">Etiqueta e-Doc válida</span>

                            <h1 class="h4 mb-2">Localização identificada</h1>
                            <p class="text-body-secondary mb-4">
                                Esta etiqueta corresponde a uma localização cadastrada no sistema e-Doc.
                            </p>

                            <p class="small text-body-secondary mb-1">Protocolo</p>
                            <p class="font-monospace fw-semibold mb-4">
                                <?= htmlspecialchars($protocolo, ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <div class="alert alert-light border text-start mb-0">
                                <i class="fa-solid fa-lock me-2" aria-hidden="true"></i>
                                As informações do acervo estão disponíveis somente para usuários autenticados.
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        <?php else: ?>
            <nav aria-label="Caminho da consulta" class="mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a class="text-decoration-none" href="<?= base_url('localizacao'); ?>">Localizações</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Consulta por protocolo</li>
                </ol>
            </nav>

            <section class="card border shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="badge text-bg-light border">
                                    <?= htmlspecialchars($localizacao['tipo_localizacao'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <?php if ($tipo_documento): ?>
                                    <span class="badge text-bg-light border fw-normal">
                                        <i class="fa-regular fa-file-lines me-1" aria-hidden="true"></i>
                                        <?= htmlspecialchars($tipo_documento['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php endif; ?>

                                <span class="badge <?= $localizacao['ativo'] ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                    <?= $localizacao['ativo'] ? 'Ativa' : 'Inativa'; ?>
                                </span>
                            </div>

                            <h1 class="h3 mb-1">
                                <?= htmlspecialchars($localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            </h1>
                            <p class="text-body-secondary mb-0">
                                <?= htmlspecialchars($localizacao['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>

                        <a class="btn btn-primary flex-shrink-0"
                            href="<?= base_url('localizacao/detalhes/' . $localizacao['codigo']); ?>">
                            <i class="fa-solid fa-arrow-up-right-from-square me-2" aria-hidden="true"></i>
                            Abrir localização
                        </a>
                    </div>

                    <dl class="row border-top pt-3 mb-0">
                        <div class="col-sm-6 col-lg mb-3 mb-lg-0">
                            <dt class="small text-body-secondary fw-normal mb-1">Protocolo</dt>
                            <dd class="fw-semibold font-monospace mb-0">
                                <?= htmlspecialchars($localizacao['protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                            </dd>
                        </div>

                        <div class="col-sm-6 col-lg mb-3 mb-lg-0">
                            <dt class="small text-body-secondary fw-normal mb-1">Classificação</dt>
                            <dd class="fw-semibold mb-0">
                                <?= htmlspecialchars($localizacao['classificacao'], ENT_QUOTES, 'UTF-8'); ?>
                            </dd>
                        </div>

                        <div class="col-12 col-lg-6">
                            <dt class="small text-body-secondary fw-normal mb-1">Caminho</dt>
                            <dd class="mb-0">
                                <?php foreach ($caminho as $indice => $item): ?>
                                    <?= $indice ? ' / ' : ''; ?>
                                    <?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php endforeach; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="card border shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-semibold mb-1">Documentos armazenados</h2>
                    <p class="small text-secondary mb-0">
                        <?= $total_documentos; ?> documentos encontrados nesta localização
                    </p>
                </div>

                <?php if ($documentos): ?>
                    <div class="accordion accordion-flush" id="documentos-localizacao">
                        <?php foreach ($documentos as $indice => $documento): ?>
                            <?php $accordion_id = 'documento-' . (int) $documento['codigo']; ?>
                            <article class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button <?= $indice ? 'collapsed' : ''; ?>" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#<?= $accordion_id; ?>"
                                        aria-expanded="<?= $indice ? 'false' : 'true'; ?>"
                                        aria-controls="<?= $accordion_id; ?>">
                                        <span>
                                            <span class="fw-semibold d-block">
                                                <?= htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <small class="text-body-secondary font-monospace">
                                                <?= htmlspecialchars($documento['protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </span>
                                    </button>
                                </h3>

                                <div id="<?= $accordion_id; ?>"
                                    class="accordion-collapse collapse <?= $indice ? '' : 'show'; ?>"
                                    data-bs-parent="#documentos-localizacao">
                                    <div class="accordion-body p-4">
                                        <div class="row g-4">
                                            <div class="col-12 col-lg-5">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-5 text-body-secondary">Tipo</dt>
                                                    <dd class="col-sm-7">
                                                        <?= htmlspecialchars($documento['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </dd>

                                                    <dt class="col-sm-5 text-body-secondary">Identificação</dt>
                                                    <dd class="col-sm-7">
                                                        <?= htmlspecialchars($documento['numero_identificacao'] ?? 'Não informada', ENT_QUOTES, 'UTF-8'); ?>
                                                    </dd>

                                                    <dt class="col-sm-5 text-body-secondary">Data</dt>
                                                    <dd class="col-sm-7">
                                                        <?= $documento['data_documento'] ? date('d/m/Y', strtotime($documento['data_documento'])) : 'Não informada'; ?>
                                                    </dd>

                                                    <dt class="col-sm-5 text-body-secondary">Status</dt>
                                                    <dd class="col-sm-7">
                                                        <span class="badge <?= $documento['ativo'] ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                                            <?= $documento['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                                        </span>
                                                    </dd>

                                                    <dt class="col-sm-5 text-body-secondary">Descrição</dt>
                                                    <dd class="col-sm-7 mb-0">
                                                        <?= nl2br(htmlspecialchars($documento['descricao'] ?? 'Não informada', ENT_QUOTES, 'UTF-8')); ?>
                                                    </dd>
                                                </dl>
                                            </div>

                                            <div class="col-12 col-lg-7">
                                                <h4 class="h6 fw-semibold mb-3">Metadados</h4>

                                                <?php if ($documento['metadados']): ?>
                                                    <dl class="row mb-0">
                                                        <?php foreach ($documento['metadados'] as $metadado): ?>
                                                            <?php
                                                            $valor = $metadado['valor'];

                                                            if ($metadado['tipo_campo'] === 'checkbox') {
                                                                $valores = json_decode($valor, TRUE);
                                                                if (is_array($valores)) {
                                                                    $valor = implode(', ', $valores);
                                                                }
                                                            }
                                                            ?>
                                                            <dt class="col-sm-5 text-body-secondary">
                                                                <?= htmlspecialchars($metadado['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </dt>
                                                            <dd class="col-sm-7">
                                                                <?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>
                                                            </dd>
                                                        <?php endforeach; ?>
                                                    </dl>
                                                <?php else: ?>
                                                    <p class="text-body-secondary mb-0">Nenhum metadado preenchido.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if ($this->controle_acesso->tem_permissao('documentos.visualizar')): ?>
                                            <div class="text-end mt-4">
                                                <a class="btn btn-sm btn-primary"
                                                    href="<?= base_url('documento/detalhes/' . $documento['codigo']); ?>">
                                                    Acessar documento
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card-body text-center py-5">
                        <i class="fa-regular fa-file-lines fa-2x text-secondary mb-3" aria-hidden="true"></i>
                        <h3 class="h5">Nenhum documento nesta localização</h3>
                        <p class="text-body-secondary mb-0">A localização não possui documentos armazenados neste nível.</p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
