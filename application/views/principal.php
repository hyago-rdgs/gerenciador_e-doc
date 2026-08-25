<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tela inicial do sistema e-Doc">
    <title>Início | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <section aria-labelledby="modulos-title">
            <header class="mb-3">
                <h2 class="h5 mb-1" id="modulos-title">Módulos</h2>
                <p class="small text-body-secondary mb-0">
                    Acesse as principais áreas de gerenciamento do e-Doc.
                </p>
            </header>

            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-4">
                    <a class="card h-100 border shadow-sm text-decoration-none text-body"
                        href="<?= base_url('documento'); ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded p-3"
                                    aria-hidden="true">
                                    <i class="fa-regular fa-file-lines fa-lg"></i>
                                </span>
                                <i class="fa-solid fa-arrow-right text-body-tertiary" aria-hidden="true"></i>
                            </div>
                            <h3 class="h6 fw-semibold">Documentos</h3>
                            <p class="small text-body-secondary mb-0">
                                Cadastre, consulte, atualize e gerencie documentos do acervo.
                            </p>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <a class="card h-100 border shadow-sm text-decoration-none text-body"
                        href="<?= base_url('pesquisa'); ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-3"
                                    aria-hidden="true">
                                    <i class="fa-solid fa-magnifying-glass fa-lg"></i>
                                </span>
                                <i class="fa-solid fa-arrow-right text-body-tertiary" aria-hidden="true"></i>
                            </div>
                            <h3 class="h6 fw-semibold">Pesquisa</h3>
                            <p class="small text-body-secondary mb-0">
                                Combine critérios, tipos, localizações e metadados para localizar registros.
                            </p>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <a class="card h-100 border shadow-sm text-decoration-none text-body"
                        href="<?= base_url('localizacao'); ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-3"
                                    aria-hidden="true">
                                    <i class="fa-solid fa-location-dot fa-lg"></i>
                                </span>
                                <i class="fa-solid fa-arrow-right text-body-tertiary" aria-hidden="true"></i>
                            </div>
                            <h3 class="h6 fw-semibold">Localizações</h3>
                            <p class="small text-body-secondary mb-0">
                                Navegue pela estrutura física e organize onde cada documento está armazenado.
                            </p>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <a class="card h-100 border shadow-sm text-decoration-none text-body"
                        href="<?= base_url('tipo_documento'); ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-3"
                                    aria-hidden="true">
                                    <i class="fa-solid fa-folder-tree fa-lg"></i>
                                </span>
                                <i class="fa-solid fa-arrow-right text-body-tertiary" aria-hidden="true"></i>
                            </div>
                            <h3 class="h6 fw-semibold">Tipos de documento</h3>
                            <p class="small text-body-secondary mb-0">
                                Defina categorias documentais e os metadados associados a cada tipo.
                            </p>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <a class="card h-100 border shadow-sm text-decoration-none text-body"
                        href="<?= base_url('metadado'); ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-3"
                                    aria-hidden="true">
                                    <i class="fa-solid fa-tags fa-lg"></i>
                                </span>
                                <i class="fa-solid fa-arrow-right text-body-tertiary" aria-hidden="true"></i>
                            </div>
                            <h3 class="h6 fw-semibold">Metadados</h3>
                            <p class="small text-body-secondary mb-0">
                                Gerencie os campos personalizados usados na classificação dos documentos.
                            </p>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 border shadow-sm bg-body-secondary">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center bg-white text-secondary border rounded p-3"
                                    aria-hidden="true">
                                    <i class="fa-solid fa-chart-column fa-lg"></i>
                                </span>
                                <span class="badge text-bg-light border fw-normal">Em breve</span>
                            </div>
                            <h3 class="h6 fw-semibold">Indicadores</h3>
                            <p class="small text-body-secondary mb-0">
                                Dashboards e análises do acervo serão disponibilizados em um módulo próprio.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
