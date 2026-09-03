<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Central de relatórios do e-Doc">
    <title>Relatórios | e-Doc</title>
    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="mb-4">
            <h1 class="h3 mb-1">Relatórios</h1>
            <p class="text-body-secondary mb-0">
                Consulte e exporte informações operacionais e gerenciais do acervo.
            </p>
        </header>

        <section class="row g-4">
            <div class="col-12 col-md-6 col-xl-3">
                <a class="card h-100 border shadow-sm text-decoration-none text-reset"
                    href="<?= base_url('relatorio/acervo'); ?>">
                    <div class="card-body p-4">
                        <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-3 mb-3">
                            <i class="fa-solid fa-box-archive fa-lg"></i>
                        </span>
                        <h2 class="h5">Acervo documental</h2>
                        <p class="text-body-secondary mb-0">
                            Documentos, tipos, localizações, período de cadastro e cobertura digital.
                        </p>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body p-4">
                        <span class="d-inline-flex align-items-center justify-content-center bg-body-secondary text-body-secondary rounded p-3 mb-3">
                            <i class="fa-solid fa-arrow-right-arrow-left fa-lg"></i>
                        </span>
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <h2 class="h5 mb-0">Movimentações</h2>
                            <span class="badge text-bg-light border">Em breve</span>
                        </div>
                        <p class="text-body-secondary mt-3 mb-0">
                            Histórico de transferências, retiradas e devoluções.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body p-4">
                        <span class="d-inline-flex align-items-center justify-content-center bg-body-secondary text-body-secondary rounded p-3 mb-3">
                            <i class="fa-solid fa-hand-holding fa-lg"></i>
                        </span>
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <h2 class="h5 mb-0">Custódia e retiradas</h2>
                            <span class="badge text-bg-light border">Em breve</span>
                        </div>
                        <p class="text-body-secondary mt-3 mb-0">
                            Documentos em custódia, previsões e atrasos de devolução.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body p-4">
                        <span class="d-inline-flex align-items-center justify-content-center bg-body-secondary text-body-secondary rounded p-3 mb-3">
                            <i class="fa-solid fa-file-circle-check fa-lg"></i>
                        </span>
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <h2 class="h5 mb-0">Digitalização</h2>
                            <span class="badge text-bg-light border">Em breve</span>
                        </div>
                        <p class="text-body-secondary mt-3 mb-0">
                            Cobertura digital, arquivos, versões e armazenamento.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
