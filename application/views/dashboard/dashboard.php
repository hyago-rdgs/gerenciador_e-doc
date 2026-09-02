<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dashboard gerencial do sistema e-Doc">
    <title>Dashboard | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb small mb-2">
                        <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Início</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </nav>

                <h1 class="h3 mb-1">Dashboard e indicadores</h1>
                <p class="text-body-secondary mb-0">
                    Acompanhe o volume, a digitalização e a movimentação do acervo.
                </p>
            </div>

            <span class="small text-body-secondary">
                <i class="fa-regular fa-clock me-1"></i>
                Atualizado ao carregar a página
            </span>
        </header>

        <div id="dashboard-erro" class="alert alert-danger d-none" role="alert"></div>

        <section aria-labelledby="resumo-title" class="mb-4">
            <h2 class="visually-hidden" id="resumo-title">Resumo do acervo</h2>

            <div class="row g-3">
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <p class="small text-body-secondary mb-1">Documentos</p>
                                    <strong class="fs-3" id="indicador-total-documentos">—</strong>
                                    <p class="small text-body-secondary mb-0">no acervo</p>
                                </div>
                                <span class="text-primary fs-4" aria-hidden="true">
                                    <i class="fa-regular fa-file-lines"></i>
                                </span>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <p class="small text-body-secondary mb-1">Cadastros no mês</p>
                                    <strong class="fs-3" id="indicador-documentos-mes">—</strong>
                                    <p class="small text-body-secondary mb-0">novos documentos</p>
                                </div>
                                <span class="text-primary fs-4" aria-hidden="true">
                                    <i class="fa-regular fa-calendar-plus"></i>
                                </span>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <p class="small text-body-secondary mb-1">Digitalização</p>
                                    <strong class="fs-3" id="indicador-digitalizacao">—</strong>
                                    <p class="small text-body-secondary mb-0">documentos com arquivo</p>
                                </div>
                                <span class="text-primary fs-4" aria-hidden="true">
                                    <i class="fa-solid fa-file-arrow-up"></i>
                                </span>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <p class="small text-body-secondary mb-1">Localizações</p>
                                    <strong class="fs-3" id="indicador-localizacoes">—</strong>
                                    <p class="small text-body-secondary mb-0">na estrutura física</p>
                                </div>
                                <span class="text-primary fs-4" aria-hidden="true">
                                    <i class="fa-solid fa-location-dot"></i>
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="row g-3 mb-4" aria-label="Gráficos do acervo">
            <div class="col-12">
                <article class="card border shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">Documentos cadastrados nos últimos 12 meses</h2>
                    </div>
                    <div class="card-body">
                        <div id="grafico-documentos-mes" style="height: 320px;" role="img"
                            aria-label="Gráfico de documentos cadastrados por mês"></div>
                    </div>
                </article>
            </div>

            <div class="col-12 col-xl-6">
                <article class="card h-100 border shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">Documentos por tipo</h2>
                    </div>
                    <div class="card-body">
                        <div id="grafico-documentos-tipo" style="height: 360px;" role="img"
                            aria-label="Gráfico de documentos por tipo documental"></div>
                    </div>
                </article>
            </div>

            <div class="col-12 col-xl-6">
                <article class="card h-100 border shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">Digitalização do acervo</h2>
                    </div>
                    <div class="card-body">
                        <div id="grafico-digitalizacao" style="height: 360px;" role="img"
                            aria-label="Gráfico de documentos com e sem arquivo digital"></div>
                    </div>
                </article>
            </div>

            <div class="col-12">
                <article class="card border shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">Localizações com maior volume de documentos</h2>
                    </div>
                    <div class="card-body">
                        <div id="grafico-documentos-localizacao" style="height: 400px;" role="img"
                            aria-label="Gráfico das localizações com mais documentos"></div>
                    </div>
                </article>
            </div>
        </section>

        <section class="mb-4" aria-labelledby="atencoes-title">
            <header class="mb-3">
                <h2 class="h5 mb-1" id="atencoes-title">Atenções</h2>
                <p class="small text-body-secondary mb-0">
                    Pontos do acervo que podem exigir acompanhamento.
                </p>
            </header>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <article class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <p class="small text-body-secondary mb-1">Documentos sem arquivo</p>
                            <strong class="fs-4" id="atencao-sem-arquivo">—</strong>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-md-4">
                    <article class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <p class="small text-body-secondary mb-1">Retiradas em aberto</p>
                            <strong class="fs-4" id="atencao-retiradas-abertas">—</strong>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-md-4">
                    <article class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <p class="small text-body-secondary mb-1">Retiradas em atraso</p>
                            <strong class="fs-4" id="atencao-retiradas-atrasadas">—</strong>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section aria-labelledby="movimentacoes-title">
            <article class="card border shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h2 class="h6 mb-1" id="movimentacoes-title">Movimentações recentes</h2>
                        <p class="small text-body-secondary mb-0">
                            Últimas alterações de localização e custódia registradas.
                        </p>
                    </div>
                    <span class="badge text-bg-light border">
                        <span id="indicador-movimentacoes-mes">—</span> no mês
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Documento</th>
                                <th>Movimentação</th>
                                <th>Origem</th>
                                <th>Destino / responsável</th>
                                <th>Usuário</th>
                            </tr>
                        </thead>
                        <tbody id="movimentacoes-recentes">
                            <tr>
                                <td colspan="6" class="text-center text-body-secondary py-4">
                                    Carregando indicadores...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </main>

    <?php $this->load->view('js'); ?>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.6.0/dist/echarts.min.js"></script>
    <?php $this->load->view('dashboard/dashboard_js'); ?>
</body>

</html>
