<?php $modo_edicao = !empty($perfil) ? TRUE : FALSE; ?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Configuração de perfil de acesso do sistema e-Doc">
    <title><?= $modo_edicao ? 'Configurar perfil' : 'Novo perfil'; ?> | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="mb-4">
            <h1 class="h3 mb-1">
                <?= $modo_edicao ? 'Configurar perfil' : 'Novo perfil'; ?>
            </h1>
            <p class="text-body-secondary mb-0">
                Defina a identificação e as permissões efetivas do perfil.
            </p>
        </header>

        <nav aria-label="Caminho do perfil" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a class="text-decoration-none" href="<?= base_url('perfil'); ?>">
                        Perfis de acesso
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= $modo_edicao ? 'Configurar' : 'Cadastrar'; ?>
                </li>
            </ol>
        </nav>

        <form id="formulario" method="post" novalidate>
            <div id="alerta-formulario" class="alert alert-danger d-none" role="alert"></div>

            <section class="card border shadow-sm mb-4" aria-labelledby="identificacao-title">
                <div class="card-body p-4">
                    <h2 class="h5 fw-semibold mb-1" id="identificacao-title">Identificação</h2>
                    <p class="small text-secondary mb-4">
                        A chave é técnica e permanece estável após o cadastro.
                    </p>

                    <div class="row g-3">
                        <div class="col-12 col-lg-7">
                            <label class="form-label" for="nome">Nome</label>
                            <input class="form-control" id="nome" name="nome"
                                maxlength="100" required type="text"
                                value="<?= html_escape($perfil['nome'] ?? ''); ?>">
                        </div>

                        <div class="col-12 col-lg-5">
                            <label class="form-label" for="chave">Chave</label>
                            <input class="form-control font-monospace" id="chave"
                                name="chave" maxlength="50" required type="text"
                                pattern="[a-z0-9_]+"
                                <?= $modo_edicao ? 'readonly' : ''; ?>
                                value="<?= html_escape($perfil['chave'] ?? ''); ?>">
                            <div class="form-text">Letras minúsculas, números e _.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card border shadow-sm" aria-labelledby="permissoes-title">
                <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between gap-3 py-3">
                    <div>
                        <h2 class="h5 fw-semibold mb-1" id="permissoes-title">Permissões</h2>
                        <p class="small text-secondary mb-0">
                            Selecione exatamente as operações permitidas para este perfil.
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light border" type="button" id="selecionar_todas">
                            Selecionar todas
                        </button>
                        <button class="btn btn-sm btn-light border" type="button" id="limpar_todas">
                            Limpar
                        </button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">
                        <?php foreach ($modulos as $modulo): ?>
                            <div class="col-12 col-xl-6">
                                <fieldset class="border rounded p-3 h-100">
                                    <legend class="float-none w-auto px-2 h6 fw-semibold mb-2">
                                        <?= html_escape($modulo['nome']); ?>
                                    </legend>

                                    <div class="d-flex flex-column gap-3">
                                        <?php foreach ($modulo['permissoes'] as $permissao): ?>
                                            <?php $selecionada = in_array(
                                                (int) $permissao['codigo'],
                                                $permissoes_selecionadas,
                                                TRUE
                                            ); ?>

                                            <div class="form-check">
                                                <input class="form-check-input permissao modulo-<?= $modulo['codigo']; ?>"
                                                    type="checkbox"
                                                    name="permissoes[]"
                                                    value="<?= $permissao['codigo']; ?>"
                                                    id="permissao_<?= $permissao['codigo']; ?>"
                                                    <?= $selecionada ? 'checked' : ''; ?>>

                                                <label class="form-check-label" for="permissao_<?= $permissao['codigo']; ?>">
                                                    <span class="fw-semibold d-block">
                                                        <?= html_escape($permissao['nome']); ?>
                                                    </span>
                                                    <span class="small text-secondary">
                                                        <?= html_escape($permissao['descricao'] ?? ''); ?>
                                                    </span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <button class="btn btn-sm btn-link text-decoration-none px-0 mt-3 selecionar-modulo"
                                        type="button"
                                        data-modulo="<?= $modulo['codigo']; ?>">
                                        Alternar módulo
                                    </button>
                                </fieldset>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2 mt-4">
                <a class="btn btn-light border" href="<?= base_url('perfil'); ?>">Voltar</a>
                <button class="btn btn-primary" type="submit" id="salvar">
                    <?= $modo_edicao ? 'Salvar configurações' : 'Cadastrar perfil'; ?>
                </button>
            </div>
        </form>
    </main>

    <?php $this->load->view('js'); ?>

    <script>
        $(document).ready(function () {
            const base_url = '<?= base_url(); ?>';

            $('#selecionar_todas').on('click', function () {
                $('.permissao').prop('checked', true);
            });

            $('#limpar_todas').on('click', function () {
                $('.permissao').prop('checked', false);
            });

            $('.selecionar-modulo').on('click', function () {
                const modulo = $(this).data('modulo');
                const $permissoes = $('.modulo-' + modulo);
                const todas_selecionadas =
                    $permissoes.filter(':checked').length === $permissoes.length;

                $permissoes.prop('checked', !todas_selecionadas);
            });

            $('#formulario').on('submit', function (e) {
                e.preventDefault();

                const $botao = $('#salvar');

                $('#alerta-formulario')
                    .empty()
                    .addClass('d-none');

                $botao
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Salvando...'
                    );

                $.ajax({
                    url: window.location.href,
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json'
                }).done(function (response) {
                    window.location.href = base_url + 'perfil';
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-formulario');
                }).always(function () {
                    $botao
                        .prop('disabled', false)
                        .html(
                            '<?= $modo_edicao ? 'Salvar configurações' : 'Cadastrar perfil'; ?>'
                        );
                });
            });
        });
    </script>
</body>

</html>
