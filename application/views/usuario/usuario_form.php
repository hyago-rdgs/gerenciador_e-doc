<?php $modo_edicao = !empty($usuario) ? TRUE : FALSE; ?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Formulário de usuário do sistema e-Doc">
    <title><?= $modo_edicao ? 'Editar usuário' : 'Novo usuário'; ?> | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1"><?= $modo_edicao ? 'Editar usuário' : 'Novo usuário'; ?></h1>
                <p class="text-body-secondary mb-0">
                    <?= $modo_edicao
                        ? 'Atualize os dados e as credenciais deste usuário.'
                        : 'Cadastre um usuário para acesso ao sistema.'; ?>
                </p>
            </section>
        </header>

        <nav aria-label="Caminho do usuário" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a class="text-decoration-none" href="<?= base_url('usuario'); ?>">
                        <i class="fa-solid fa-users me-1" aria-hidden="true"></i>
                        Usuários
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= $modo_edicao ? 'Editar' : 'Cadastrar'; ?>
                </li>
            </ol>
        </nav>

        <section aria-labelledby="dados-usuario-title" class="card border shadow-sm">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h2 class="h5 fw-semibold mb-1" id="dados-usuario-title">Dados do usuário</h2>
                    <p class="small text-body-secondary mb-3">
                        Informe os dados pessoais, credenciais de acesso e situação do usuário.
                    </p>
                    <div id="alerta-formulario" class="alert alert-danger mb-0 d-none" role="alert"></div>
                </div>

                <form id="formulario" method="post" novalidate>
                    <fieldset>
                        <legend class="visually-hidden">Informações cadastrais do usuário</legend>

                        <div class="row g-3">
                            <div class="col-12 col-lg-8">
                                <label class="form-label" for="nome">Nome</label>
                                <input class="form-control" id="nome" maxlength="255" name="nome"
                                    placeholder="Nome completo" required type="text"
                                    value="<?= htmlspecialchars($usuario['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="usuario">Usuário</label>
                                <input class="form-control" id="usuario" maxlength="100" name="usuario"
                                    placeholder="Usuário de acesso" required type="text" autocomplete="username"
                                    value="<?= htmlspecialchars($usuario['usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label" for="email">E-mail</label>
                                <input class="form-control" id="email" maxlength="255" name="email"
                                    placeholder="usuario@exemplo.com" required type="email"
                                    value="<?= htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label" for="perfil_codigo">Perfil</label>
                                <select class="form-select" id="perfil_codigo" name="perfil_codigo" required>
                                    <option value="">Selecione</option>

                                    <?php foreach ($perfis as $perfil): ?>
                                        <option value="<?= $perfil['codigo']; ?>"
                                            <?= isset($usuario['perfil_codigo']) && $usuario['perfil_codigo'] == $perfil['codigo'] ? 'selected' : ''; ?>>
                                            <?= html_escape($perfil['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label" for="ativo">Status</label>
                                <select class="form-select" id="ativo" name="ativo" required>
                                    <option value="1" <?= !isset($usuario['ativo']) || $usuario['ativo'] == 1 ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="0" <?= isset($usuario['ativo']) && $usuario['ativo'] == 0 ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                            </div>

                            <div class="col-12">
                                <h3 class="h6 fw-semibold mb-0">Credenciais de acesso</h3>
                                <?php if ($modo_edicao): ?>
                                    <p class="small text-body-secondary mb-0">
                                        Deixe os campos de senha em branco para manter a senha atual.
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="senha">Senha</label>
                                <input class="form-control" id="senha" minlength="8" name="senha"
                                    <?= !$modo_edicao ? 'required' : ''; ?> type="password" autocomplete="new-password">
                                <div class="form-text">Utilize pelo menos 8 caracteres.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="confirmar_senha">Confirmar senha</label>
                                <input class="form-control" id="confirmar_senha" minlength="8" name="confirmar_senha"
                                    <?= !$modo_edicao ? 'required' : ''; ?> type="password" autocomplete="new-password">
                            </div>
                        </div>
                    </fieldset>

                    <hr class="my-4">

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                        <button class="btn btn-light border" id="cancelar" type="button">Voltar</button>
                        <button class="btn btn-primary" type="submit" id="salvar">
                            <?= $modo_edicao ? 'Salvar alterações' : 'Cadastrar usuário'; ?>
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

                window.location = base_url + 'usuario';
            });

            $('#formulario').on('submit', function (e) {
                e.preventDefault();

                const data_string = $('#formulario').serialize();
                const texto_botao = '<?= $modo_edicao ? 'Salvar alterações' : 'Cadastrar usuário'; ?>';

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
                        $('#ativo').val('1');
                        $('#nome').trigger('focus');
                    <?php else: ?>
                        $('#senha, #confirmar_senha').val('');
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
