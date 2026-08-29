<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Perfis de acesso do sistema e-Doc">
    <title>Perfis de acesso | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1">Perfis de acesso</h1>
                <p class="text-body-secondary mb-0">
                    Configure visualmente quais operações cada perfil pode realizar.
                </p>
            </section>

            <a class="btn btn-primary" href="<?= base_url('perfil/cadastrar'); ?>">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Novo perfil
            </a>
        </header>

        <?php if (!empty($perfis)): ?>
            <section class="card border shadow-sm" aria-labelledby="lista-perfis-title">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-semibold mb-1" id="lista-perfis-title">Perfis cadastrados</h2>
                    <p class="small text-secondary mb-0">
                        <?= count($perfis); ?> perfis encontrados
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3" scope="col">Perfil</th>
                                <th class="py-3" scope="col">Chave</th>
                                <th class="py-3 text-center" scope="col">Usuários</th>
                                <th class="py-3 text-center" scope="col">Permissões</th>
                                <th class="px-3 py-3 text-end" scope="col">Ações</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($perfis as $perfil): ?>
                                <tr>
                                    <td class="ps-3 ps-lg-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-2"
                                                aria-hidden="true">
                                                <i class="fa-solid fa-user-shield"></i>
                                            </span>

                                            <span class="fw-semibold">
                                                <?= html_escape($perfil['nome']); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="font-monospace small">
                                            <?= html_escape($perfil['chave']); ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <?= (int) $perfil['total_usuarios']; ?>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge text-bg-light border">
                                            <?= (int) $perfil['total_permissoes']; ?>
                                        </span>
                                    </td>

                                    <td class="text-end pe-3 pe-lg-4">
                                        <a class="btn btn-sm btn-light border"
                                            href="<?= base_url('perfil/atualizar/' . $perfil['codigo']); ?>"
                                            aria-label="Configurar <?= html_escape($perfil['nome']); ?>">
                                            <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i>
                                            Configurar
                                        </a>

                                        <?php if (
                                            (int) $perfil['codigo'] !==
                                            (int) $this->controle_acesso->get('perfil_codigo')
                                        ): ?>
                                            <button class="btn btn-sm btn-light border text-danger excluir-perfil"
                                                type="button"
                                                data-codigo="<?= $perfil['codigo']; ?>"
                                                data-nome="<?= html_escape($perfil['nome']); ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalExcluirPerfil"
                                                aria-label="Excluir <?= html_escape($perfil['nome']); ?>">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php else: ?>
            <section class="card border shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fa-solid fa-user-shield fa-2x text-secondary mb-3" aria-hidden="true"></i>
                    <h2 class="h5 fw-semibold">Nenhum perfil cadastrado</h2>
                    <p class="text-secondary mb-4">Cadastre um perfil para configurar as permissões.</p>
                    <a class="btn btn-primary" href="<?= base_url('perfil/cadastrar'); ?>">
                        Cadastrar perfil
                    </a>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <div class="modal fade" id="modalExcluirPerfil" tabindex="-1"
        aria-labelledby="modalExcluirPerfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5" id="modalExcluirPerfilLabel">Excluir perfil</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <form id="formulario_exclusao_perfil" method="post">
                    <div class="modal-body">
                        <div id="alerta-exclusao" class="alert alert-danger d-none" role="alert"></div>
                        <p class="mb-2">Deseja realmente excluir este perfil?</p>
                        <p class="fw-semibold mb-0" id="nome-perfil-exclusao"></p>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button class="btn btn-light border" type="button" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="btn btn-danger" type="submit" id="confirmar_exclusao">
                            Excluir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="true">
        <div id="toast-feedback" class="toast border-0 shadow" role="status">
            <div class="toast-body d-flex align-items-center gap-3">
                <i id="toast-icone" class="fa-solid fa-circle-check text-success fs-5" aria-hidden="true"></i>
                <span id="toast-mensagem" class="flex-grow-1"></span>
                <button class="btn-close" type="button" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <?php $this->load->view('js'); ?>

    <script>
        $(document).ready(function () {
            const base_url = '<?= base_url(); ?>';
            let perfil_exclusao = null;

            $('.excluir-perfil').on('click', function () {
                perfil_exclusao = $(this).data('codigo');

                $('#nome-perfil-exclusao').text(
                    $(this).data('nome')
                );

                $('#alerta-exclusao')
                    .empty()
                    .addClass('d-none');
            });

            $('#formulario_exclusao_perfil').on('submit', function (e) {
                e.preventDefault();

                if (!perfil_exclusao) {
                    return;
                }

                const $botao = $('#confirmar_exclusao');

                $botao
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
                    );

                $.ajax({
                    url: base_url + 'perfil/excluir/' + perfil_exclusao,
                    method: 'POST',
                    dataType: 'json'
                }).done(function (response) {
                    bootstrap.Modal
                        .getOrCreateInstance(
                            document.getElementById('modalExcluirPerfil')
                        )
                        .hide();

                    mostrar_feedback(
                        response.mensagem?.conteudo,
                        'success'
                    );

                    setTimeout(function () {
                        window.location.reload();
                    }, 600);
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-exclusao');
                }).always(function () {
                    $botao
                        .prop('disabled', false)
                        .html('Excluir');
                });
            });
        });
    </script>
</body>

</html>
