<?php $modo_edicao = !empty($tipo_documento) ? TRUE : FALSE; ?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Formulário de tipo de documento do sistema e-Doc">
    <title><?= $modo_edicao ? 'Editar tipo de documento' : 'Novo tipo de documento'; ?> | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1">
                    <?= $modo_edicao ? 'Editar tipo de documento' : 'Novo tipo de documento'; ?>
                </h1>
                <p class="text-body-secondary mb-0">
                    <?= $modo_edicao
                        ? 'Atualize as informações deste tipo de documento.'
                        : 'Cadastre um tipo e depois defina os metadados que irão compor seus documentos.'; ?>
                </p>
            </section>
        </header>

        <nav aria-label="Caminho do tipo de documento" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a class="text-decoration-none" href="<?= base_url('tipo_documento'); ?>">
                        <i class="fa-solid fa-folder-tree me-1" aria-hidden="true"></i>
                        Tipos de documento
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= $modo_edicao ? 'Editar' : 'Cadastrar'; ?>
                </li>
            </ol>
        </nav>

        <section aria-labelledby="dados-tipo-documento-title" class="card border shadow-sm">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h2 class="h5 fw-semibold mb-1" id="dados-tipo-documento-title">
                        Dados do tipo de documento
                    </h2>
                    <p class="small text-body-secondary mb-3">
                        Informe os dados utilizados para identificar este tipo de documento.
                    </p>
                    <div id="alerta-formulario" class="alert alert-danger mb-0 d-none" role="alert"></div>
                </div>

                <form id="formulario" method="post" novalidate>
                    <fieldset>
                        <legend class="visually-hidden">Informações cadastrais do tipo de documento</legend>

                        <div class="row g-3">
                            <div class="col-12 col-lg-8">
                                <label class="form-label" for="nome">Nome</label>
                                <input class="form-control" id="nome" maxlength="150" name="nome"
                                    placeholder="Ex.: Contrato" required type="text"
                                    value="<?= htmlspecialchars($tipo_documento['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label" for="ativo">Status</label>
                                <select class="form-select" id="ativo" name="ativo" required>
                                    <option value="1"
                                        <?= !isset($tipo_documento['ativo']) || $tipo_documento['ativo'] == 1 ? 'selected' : ''; ?>>
                                        Ativo
                                    </option>
                                    <option value="0"
                                        <?= isset($tipo_documento['ativo']) && $tipo_documento['ativo'] == 0 ? 'selected' : ''; ?>>
                                        Inativo
                                    </option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="descricao">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3"
                                    placeholder="Descreva a finalidade deste tipo de documento."><?= htmlspecialchars($tipo_documento['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </div>
                    </fieldset>

                    <hr class="my-4">

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                        <button class="btn btn-light border" id="cancelar" type="button">Voltar</button>
                        <button class="btn btn-primary" type="submit" id="salvar">
                            <?= $modo_edicao ? 'Salvar alterações' : 'Cadastrar tipo de documento'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="true">
        <div id="toast-feedback" class="toast border-0 shadow" role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-body d-flex align-items-center gap-3">
                <i id="toast-icone" class="fa-solid fa-circle-check text-success fs-5" aria-hidden="true"></i>
                <span id="toast-mensagem" class="flex-grow-1"> </span>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <?php $this->load->view('js'); ?>

    <script>
        const base_url = '<?= base_url(); ?>';

        $(document).ready(function () {
            $('#cancelar').click(function () {
                if (window.history.length > 1) {
                    window.history.back();
                    return;
                }

                window.location = base_url + 'tipo_documento';
            });

            $('#formulario').on('submit', function (e) {
                e.preventDefault();

                const data_string = $('#formulario').serialize();
                const texto_botao = '<?= $modo_edicao ? 'Salvar alterações' : 'Cadastrar tipo de documento'; ?>';

                $('#salvar').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
                $('#alerta-formulario').empty().addClass('d-none');

                $.ajax({
                    url: base_url + '<?= uri_string(); ?>',
                    method: 'POST',
                    data: data_string,
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(response.dados?.erros, 'alerta-formulario');
                        return;
                    }

                    mostrar_feedback(response.mensagem?.conteudo, 'success');

                    <?php if (!$modo_edicao): ?>
                        $('#formulario')[0].reset();
                        $('#formulario').find('input, select, textarea').first().trigger('focus');
                    <?php endif; ?>
                }).fail(function (xhr) {
                    const response = xhr.responseJSON;

                    if (response?.dados?.erros) {
                        mostrar_erros(response.dados.erros, 'alerta-formulario');
                        return;
                    }

                    mostrar_erros(
                        response?.mensagem?.conteudo ||
                        'Não foi possível comunicar com o servidor. Tente novamente.',
                        'alerta-formulario'
                    );
                }).always(function () {
                    $('#salvar').prop('disabled', false).html(texto_botao);
                });
            });
        });
    </script>
</body>

</html>
