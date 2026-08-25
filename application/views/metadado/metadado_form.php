<?php $modo_edicao = !empty($metadado) ? TRUE : FALSE; ?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Formulário de metadado do sistema e-Doc">
    <title><?= $modo_edicao ? 'Editar metadado' : 'Novo metadado'; ?> | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1"><?= $modo_edicao ? 'Editar metadado' : 'Novo metadado'; ?></h1>
                <p class="text-body-secondary mb-0">
                    <?= $modo_edicao
                        ? 'Atualize as configurações deste campo documental.'
                        : 'Cadastre um campo que poderá compor os tipos de documento.'; ?>
                </p>
            </section>
        </header>

        <nav aria-label="Caminho do metadado" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a class="text-decoration-none" href="<?= base_url('metadado'); ?>">
                        <i class="fa-solid fa-tags me-1" aria-hidden="true"></i>
                        Metadados
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= $modo_edicao ? 'Editar' : 'Cadastrar'; ?>
                </li>
            </ol>
        </nav>

        <section aria-labelledby="dados-metadado-title" class="card border shadow-sm">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h2 class="h5 fw-semibold mb-1" id="dados-metadado-title">Dados do metadado</h2>
                    <p class="small text-body-secondary mb-3">
                        Defina a identificação e o comportamento do campo no formulário de documentos.
                    </p>
                    <div id="alerta-formulario" class="alert alert-danger mb-0 d-none" role="alert"></div>
                </div>

                <form id="formulario" method="post" novalidate>
                    <fieldset>
                        <legend class="visually-hidden">Informações cadastrais do metadado</legend>

                        <div class="row g-3">
                            <div class="col-12 col-lg-8">
                                <label class="form-label" for="nome">Nome</label>
                                <input class="form-control" id="nome" maxlength="150" name="nome"
                                    placeholder="Ex.: Número do contrato" required type="text"
                                    value="<?= htmlspecialchars($metadado['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="col-12 col-md-8 col-lg-4">
                                <label class="form-label" for="tipo_campo">Tipo de campo</label>
                                <select class="form-select" id="tipo_campo" name="tipo_campo" required>
                                    <option value="">Selecione</option>
                                    <option value="text" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'text' ? 'selected' : ''; ?>>Texto</option>
                                    <option value="number" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'number' ? 'selected' : ''; ?>>Número</option>
                                    <option value="date" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'date' ? 'selected' : ''; ?>>Data</option>
                                    <option value="time" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'time' ? 'selected' : ''; ?>>Hora</option>
                                    <option value="datetime-local" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'datetime-local' ? 'selected' : ''; ?>>Data e hora</option>
                                    <option value="email" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'email' ? 'selected' : ''; ?>>E-mail</option>
                                    <option value="tel" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'tel' ? 'selected' : ''; ?>>Telefone</option>
                                    <option value="url" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'url' ? 'selected' : ''; ?>>URL</option>
                                    <option value="textarea" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'textarea' ? 'selected' : ''; ?>>Texto longo</option>
                                    <option value="select" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'select' ? 'selected' : ''; ?>>Lista de seleção</option>
                                    <option value="radio" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'radio' ? 'selected' : ''; ?>>Seleção única</option>
                                    <option value="checkbox" <?= isset($metadado['tipo_campo']) && $metadado['tipo_campo'] == 'checkbox' ? 'selected' : ''; ?>>Caixa de seleção</option>
                                </select>
                                <div class="form-text">Os valores correspondem aos tipos de campo utilizados no HTML.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="descricao">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3"
                                    placeholder="Explique qual informação deverá ser preenchida neste campo."><?= htmlspecialchars($metadado['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <div class="col-12 col-md-8" id="grupo-mascara">
                                <label class="form-label" for="mascara">Máscara</label>
                                <input class="form-control" id="mascara" maxlength="100" name="mascara"
                                    placeholder="Ex.: 000.000.000-00" type="text"
                                    value="<?= htmlspecialchars($metadado['mascara'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text">Opcional. Utilize quando o campo precisar de um formato específico.</div>
                            </div>

                            <div class="col-12" id="grupo-opcoes">
                                <label class="form-label" for="opcoes">Opções</label>
                                <textarea class="form-control" id="opcoes" name="opcoes" rows="4"
                                    placeholder="Uma opção por linha"><?= htmlspecialchars($metadado['opcoes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <div class="form-text">Disponível para lista de seleção, seleção única e caixa de seleção.</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label" for="ativo">Status</label>
                                <select class="form-select" id="ativo" name="ativo" required>
                                    <option value="1" <?= !isset($metadado['ativo']) || $metadado['ativo'] == 1 ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="0" <?= isset($metadado['ativo']) && $metadado['ativo'] == 0 ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <hr class="my-4">

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                        <button class="btn btn-light border" id="cancelar" type="button">Voltar</button>
                        <button class="btn btn-primary" type="submit" id="salvar">
                            <?= $modo_edicao ? 'Salvar alterações' : 'Cadastrar metadado'; ?>
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
            function configurar_campos() {
                const tipo_campo = $('#tipo_campo').val();
                const possui_opcoes = ['select', 'radio', 'checkbox'].includes(tipo_campo);
                const permite_mascara = ['text', 'tel'].includes(tipo_campo);

                $('#grupo-opcoes').toggleClass('d-none', !possui_opcoes);
                $('#grupo-mascara').toggleClass('d-none', !permite_mascara);
            }

            configurar_campos();

            $('#tipo_campo').change(function () {
                const tipo_campo = $(this).val();

                if (!['select', 'radio', 'checkbox'].includes(tipo_campo)) {
                    $('#opcoes').val('');
                }

                if (!['text', 'tel'].includes(tipo_campo)) {
                    $('#mascara').val('');
                }

                configurar_campos();
            });

            $('#cancelar').click(function () {
                if (window.history.length > 1) {
                    window.history.back();
                    return;
                }

                window.location = base_url + 'metadado';
            });

            $('#formulario').on('submit', function (e) {
                e.preventDefault();

                const data_string = $('#formulario').serialize();
                const texto_botao = '<?= $modo_edicao ? 'Salvar alterações' : 'Cadastrar metadado'; ?>';

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
                        configurar_campos();
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
