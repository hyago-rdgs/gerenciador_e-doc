<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Acesso ao sistema de gerenciamento eletrônico de documentos.">
    <title>Acessar sistema | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-light">
    <main class="min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
                    <section class="card border shadow-sm" aria-labelledby="login-title">
                        <div class="card-body p-4 p-md-5">
                            <header class="text-center mb-4">
                                <div class="mb-3" aria-hidden="true">
                                    <span
                                        class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded p-3">
                                        <i class="fa-solid fa-file-shield fs-4"></i>
                                    </span>
                                </div>

                                <p class="fw-bold text-dark mb-1">e-Doc</p>
                                <p class="small text-secondary mb-4">
                                    Gerenciamento eletrônico de documentos
                                </p>

                                <h1 class="h3 fw-bold text-dark mb-2" id="login-title">
                                    Acessar sistema
                                </h1>

                                <p class="text-secondary mb-0">
                                    Informe suas credenciais para continuar.
                                </p>
                            </header>

                            <form id="formulario" method="post" novalidate>
                                <div id="alerta-login" class="alert alert-danger d-none" role="alert"></div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="usuario">Usuário</label>

                                    <div class="input-group">
                                        <span class="input-group-text" aria-hidden="true">
                                            <i class="fa-solid fa-user text-secondary"></i>
                                        </span>

                                        <input class="form-control" type="text" id="usuario" name="usuario"
                                            placeholder="Digite seu usuário" autocomplete="username" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold" for="senha">Senha</label>

                                    <div class="input-group">
                                        <span class="input-group-text" aria-hidden="true">
                                            <i class="fa-solid fa-lock text-secondary"></i>
                                        </span>

                                        <input class="form-control" type="password" id="senha" name="senha"
                                            placeholder="Digite sua senha" autocomplete="current-password" required>

                                        <button class="btn btn-outline-secondary" type="button" id="alternar-senha"
                                            aria-label="Mostrar senha" aria-pressed="false">
                                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <button class="btn btn-primary w-100" type="submit" id="entrar">
                                    <i class="fa-solid fa-arrow-right-to-bracket me-2" aria-hidden="true"></i>
                                    Entrar
                                </button>
                            </form>

                            <footer class="border-top text-center mt-4 pt-4">
                                <p class="small text-secondary mb-0">
                                    <i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i>
                                    Acesso restrito a usuários autorizados.
                                </p>
                            </footer>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <?php $this->load->view('js'); ?>

    <script>
        $(document).ready(function () {
            const base_url = '<?= base_url(); ?>';

            $('#alternar-senha').on('click', function () {
                const $botao = $(this);
                const $campo_senha = $('#senha');
                const senha_visivel = $campo_senha.attr('type') === 'text';

                $campo_senha.attr(
                    'type',
                    senha_visivel ? 'password' : 'text'
                );

                $botao.attr(
                    'aria-label',
                    senha_visivel ? 'Mostrar senha' : 'Ocultar senha'
                );

                $botao.attr('aria-pressed', String(!senha_visivel));
                $botao.find('i').toggleClass('fa-eye fa-eye-slash');
            });

            $('#formulario').on('submit', function (e) {
                e.preventDefault();

                const $botao = $('#entrar');

                $('#alerta-login')
                    .empty()
                    .addClass('d-none');

                $botao
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Entrando...'
                    );

                $.ajax({
                    url: base_url + 'autenticacao/login',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json'
                }).done(function (response) {
                    if (response.sucesso) {
                        window.location.href = base_url;
                        return;
                    }

                    mostrar_erros(
                        response.dados?.erros ||
                        response.mensagem?.conteudo,
                        'alerta-login'
                    );
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-login');
                }).always(function () {
                    $botao
                        .prop('disabled', false)
                        .html(
                            '<i class="fa-solid fa-arrow-right-to-bracket me-2" aria-hidden="true"></i>Entrar'
                        );
                });
            });
        });
    </script>
</body>

</html>
