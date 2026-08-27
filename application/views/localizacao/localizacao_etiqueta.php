<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="Etiqueta de localização do sistema e-Doc">
    <title>Etiqueta | <?= htmlspecialchars($localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?> | e-Doc</title>

    <?php $this->load->view('css'); ?>

    <style>
        @page {
            size: landscape;
            margin: 10mm;
        }

        @media print {
            .nao-imprimir {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .etiqueta {
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body class="bg-body-tertiary">
    <main class="container py-4 py-lg-5">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4 nao-imprimir">
            <div>
                <h1 class="h4 mb-1">Etiqueta da localização</h1>
                <p class="text-body-secondary mb-0">Revise as informações antes de imprimir.</p>
            </div>

            <div class="d-flex gap-2">
                <a class="btn btn-light border"
                    href="<?= base_url('localizacao/detalhes/' . $localizacao['codigo']); ?>">
                    Voltar
                </a>
                <button class="btn btn-primary" type="button" onclick="window.print();">
                    <i class="fa-solid fa-print me-2" aria-hidden="true"></i>
                    Imprimir etiqueta
                </button>
            </div>
        </div>

        <?php
        $url_qr = 'https://api.qrserver.com/v1/create-qr-code/'
            . '?size=300x300'
            . '&data=' . rawurlencode($url_consulta);
        ?>

        <section class="card border shadow-sm etiqueta">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-md-4 text-center">
                        <img
                            src="<?= htmlspecialchars($url_qr, ENT_QUOTES, 'UTF-8'); ?>"
                            alt="QR Code da localização"
                            class="img-fluid"
                            width="300"
                            height="300"
                        >
                    </div>

                    <div class="col-12 col-md-8">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded p-2"
                                aria-hidden="true">
                                <i class="fa-solid fa-file-shield"></i>
                            </span>
                            <span class="fs-5 fw-semibold">e-Doc</span>
                        </div>

                        <h2 class="h3 mb-2">
                            <?= htmlspecialchars($localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                        </h2>

                        <?php if (!empty($localizacao['descricao'])): ?>
                            <p class="text-body-secondary mb-4">
                                <?= htmlspecialchars($localizacao['descricao'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        <?php endif; ?>

                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-body-secondary">Protocolo</dt>
                            <dd class="col-sm-8 font-monospace fw-semibold">
                                <?= htmlspecialchars($localizacao['protocolo'], ENT_QUOTES, 'UTF-8'); ?>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary">Classificação</dt>
                            <dd class="col-sm-8 fw-semibold">
                                <?= htmlspecialchars($localizacao['classificacao'], ENT_QUOTES, 'UTF-8'); ?>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary">Tipo de localização</dt>
                            <dd class="col-sm-8">
                                <?= htmlspecialchars($localizacao['tipo_localizacao'], ENT_QUOTES, 'UTF-8'); ?>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary">Tipo documental</dt>
                            <dd class="col-sm-8 mb-0">
                                <?php if ($tipo_documento): ?>
                                    <?= htmlspecialchars($tipo_documento['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php else: ?>
                                    <span class="text-body-secondary">Não definido</span>
                                <?php endif; ?>
                            </dd>
                        </dl>

                        <hr class="my-4">

                        <p class="small text-body-secondary mb-1">Consulta</p>
                        <p class="small font-monospace text-break mb-0">
                            <?= htmlspecialchars($url_consulta, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
