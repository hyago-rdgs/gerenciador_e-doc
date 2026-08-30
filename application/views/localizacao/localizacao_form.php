<?php
$modo_edicao = !empty($localizacao) ? TRUE : FALSE;

$localizacao_pai_selecionada =
    $localizacao['localizacao_codigo_pai'] ??
    $localizacao_codigo_pai ??
    NULL;
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Formulário de localização do sistema e-Doc">
    <title><?= $modo_edicao ? 'Editar localização' : 'Nova localização'; ?> | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1"><?= $modo_edicao ? 'Editar localização' : 'Nova localização'; ?></h1>
                <p class="text-body-secondary mb-0">
                    <?= $modo_edicao
                        ? 'Atualize as informações desta localização.'
                        : 'Cadastre uma nova localização na estrutura de armazenamento.'; ?>
                </p>
            </section>
        </header>

        <nav aria-label="Caminho da localização" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a class="text-decoration-none" href="<?= base_url('localizacao'); ?>">
                        <i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i>
                        Localizações
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= $modo_edicao ? 'Editar' : 'Cadastrar'; ?>
                </li>
            </ol>
        </nav>

        <section aria-labelledby="dados-localizacao-title" class="card border shadow-sm">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h2 class="h5 fw-semibold mb-1" id="dados-localizacao-title">Dados da localização</h2>
                    <p class="small text-body-secondary mb-3">
                        Informe os dados utilizados para identificar e organizar a localização.
                    </p>
                    <div id="alerta-formulario" class="alert alert-danger mb-0 d-none" role="alert"></div>
                </div>

                <form id="formulario" method="post" novalidate>
                    <fieldset>
                        <legend class="visually-hidden">Informações cadastrais da localização</legend>

                        <div class="row g-3">
                            <div class="col-12 col-lg-8">
                                <label class="form-label" for="nome">Nome</label>
                                <input class="form-control" id="nome" maxlength="255" name="nome"
                                    placeholder="Ex.: Arquivo administrativo" required type="text"
                                    value="<?= htmlspecialchars($localizacao['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="tipo_localizacao_codigo">Tipo de localização</label>
                                <select class="form-select" id="tipo_localizacao_codigo" name="tipo_localizacao_codigo"
                                    required>
                                    <option value="">Selecione</option>

                                    <?php foreach ($tipos_localizacao as $tipo_localizacao): ?>
                                        <option value="<?= $tipo_localizacao['codigo']; ?>"
                                            <?= isset($localizacao['tipo_localizacao_codigo']) && $localizacao['tipo_localizacao_codigo'] == $tipo_localizacao['codigo'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($tipo_localizacao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="descricao">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3"
                                    placeholder="Descreva a finalidade ou identificação desta localização."><?= htmlspecialchars($localizacao['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label" for="localizacao_codigo_pai">Localização superior</label>
                                <select class="form-select" id="localizacao_codigo_pai" name="localizacao_codigo_pai">
                                    <option value="">Nenhuma — localização raiz</option>

                                    <?php foreach ($localizacoes_opcoes as $opcao): ?>
                                        <option value="<?= $opcao['codigo']; ?>"
                                            <?= $localizacao_pai_selecionada == $opcao['codigo'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($opcao['classificacao'] . ' — ' . $opcao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Deixe vazio para cadastrar uma localização raiz.</div>
                            </div>

                            <div class="col-12 col-md-8 col-lg-4">
                                <label class="form-label" for="tipo_documento_codigo">Tipo de documento</label>
                                <select class="form-select" id="tipo_documento_codigo" name="tipo_documento_codigo">
                                    <option value="">Nenhum — sem armazenamento documental</option>

                                    <?php foreach ($tipos_documento as $tipo_documento): ?>
                                        <option value="<?= $tipo_documento['codigo']; ?>"
                                            <?= isset($tipo_documento_codigo) && $tipo_documento_codigo == $tipo_documento['codigo'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($tipo_documento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Quando definido, somente documentos deste tipo poderão ser armazenados nesta localização.
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label" for="ativo">Status</label>
                                <select class="form-select" id="ativo" name="ativo" required>
                                    <option value="1" <?= !isset($localizacao['ativo']) || $localizacao['ativo'] == 1 ? 'selected' : ''; ?>>
                                        Ativa
                                    </option>
                                    <option value="0" <?= isset($localizacao['ativo']) && $localizacao['ativo'] == 0 ? 'selected' : ''; ?>>
                                        Inativa
                                    </option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <hr class="my-4">

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                        <button class="btn btn-light border" id="cancelar" type="button">Voltar</button>
                        <button class="btn btn-primary" type="submit" id="salvar">
                            <?= $modo_edicao ? 'Salvar alterações' : 'Cadastrar localização'; ?>
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

                window.location = base_url + 'localizacao';
            });

            $('#formulario').on('submit', function (e) {
                e.preventDefault();

                const data_string = $('#formulario').serialize();
                const texto_botao = '<?= $modo_edicao ? 'Salvar alterações' : 'Cadastrar localização'; ?>';

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
                    mostrar_erro_ajax(xhr, 'alerta-formulario');
                }).always(function () {
                    $('#salvar').prop('disabled', false).html(texto_botao);
                });
            });
        });
    </script>
</body>

</html>
