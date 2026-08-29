<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tela inicial do módulo de usuários do sistema e-Doc">
    <title>Usuários | e-Doc</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1">Usuários</h1>
                <p class="text-body-secondary mb-0">
                    Cadastre e gerencie os usuários com acesso ao sistema.
                </p>
            </section>
            <a class="btn btn-primary flex-shrink-0" href="<?= base_url('usuario/cadastrar'); ?>">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Novo usuário
            </a>
        </header>

        <nav aria-label="Caminho do usuário" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fa-solid fa-users me-1" aria-hidden="true"></i>
                    Usuários
                </li>
            </ol>
        </nav>

        <section aria-labelledby="filtros-title" class="card border shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3" id="filtros-title">Filtros</h2>
                <form action="#" method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-7 col-lg-7">
                            <label class="form-label" for="termo">Buscar usuário</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                                </span>
                                <input class="form-control" id="termo" name="termo"
                                    placeholder="Nome, usuário ou e-mail" type="search"
                                    value="<?= htmlspecialchars($filtro_termo ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2 col-lg-2">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="ativo" <?= isset($filtro_status) && $filtro_status == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="inativo" <?= isset($filtro_status) && $filtro_status == 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <a class="btn btn-primary flex-fill" id="filtrar" role="button">
                                    <i class="fa-solid fa-filter me-2"></i>
                                    Filtrar
                                </a>
                                <a class="btn btn-light border flex-fill" id="limpar_filtro" role="button">Limpar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <?php if ($usuarios): ?>
            <section aria-labelledby="lista-usuarios-title" class="card border shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <div>
                        <h2 class="h6 fw-semibold mb-1" id="lista-usuarios-title">Usuários cadastrados</h2>
                        <p class="small text-secondary mb-0"><?= $total_usuarios; ?> usuários encontrados</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="small text-secondary">
                                <th class="px-3 py-3" scope="col">Usuário</th>
                                <th class="py-3" scope="col">Login</th>
                                <th class="py-3" scope="col">E-mail</th>
                                <th class="py-3" scope="col">Perfil</th>
                                <th class="py-3 text-center" scope="col">Status</th>
                                <th class="px-3 py-3 text-end" scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td class="ps-3 ps-lg-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-2"
                                                aria-hidden="true">
                                                <i class="fa-regular fa-user"></i>
                                            </span>
                                            <span class="fw-semibold">
                                                <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-monospace small">
                                            <?= htmlspecialchars($usuario['usuario'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?= html_escape($usuario['perfil_nome']); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($usuario['ativo'] == 1): ?>
                                            <span class="badge text-bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3 pe-lg-4">
                                        <a class="btn btn-sm btn-light border"
                                            href="<?= base_url('usuario/atualizar/' . $usuario['codigo']); ?>"
                                            aria-label="Editar <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <?php if ($usuario['codigo'] != $this->controle_acesso->get('codigo')): ?>
                                            <button type="button" class="btn btn-sm btn-light border text-danger excluir-usuario"
                                                data-codigo="<?= $usuario['codigo']; ?>"
                                                data-nome="<?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalExcluirUsuario"
                                                aria-label="Excluir <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white py-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <p class="small text-secondary mb-0">
                            Exibindo <?= $offset; ?> – <?= min($offset + $limite - 1, $total_usuarios); ?> de
                            <?= $total_usuarios; ?> usuários
                        </p>

                        <?php
                        parse_str($_SERVER['QUERY_STRING'], $params);
                        unset($params['pagina']);

                        $gerar_url = function ($num_pagina) use ($params) {
                            $params['pagina'] = $num_pagina;
                            return '?' . http_build_query($params);
                        };
                        ?>

                        <nav aria-label="Paginação de usuários">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item">
                                    <?php if ($pagina_atual > 1): ?>
                                        <a aria-label="Anterior" class="page-link" href="<?= $gerar_url($pagina_atual - 1); ?>">Anterior</a>
                                    <?php else: ?>
                                        <a aria-label="Anterior" class="page-link disabled">Anterior</a>
                                    <?php endif; ?>
                                </li>

                                <?php $adjacentes = 3; ?>
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <?php if ($i == 1 || $i == $total_paginas || ($i >= $pagina_atual - $adjacentes && $i <= $pagina_atual + $adjacentes)): ?>
                                        <?php if ($i == $pagina_atual): ?>
                                            <li aria-current="page" class="page-item active">
                                                <span class="page-link bg-primary"><?= $i; ?></span>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item">
                                                <a class="page-link text-primary" href="<?= $gerar_url($i); ?>"><?= $i; ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php $mostrar_reticencias_esquerda = $i == 2 && $pagina_atual > $adjacentes + 2; ?>
                                    <?php $mostrar_reticencias_direita = $i == $total_paginas - 1 && $pagina_atual < $total_paginas - $adjacentes - 1; ?>

                                    <?php if ($mostrar_reticencias_esquerda || $mostrar_reticencias_direita): ?>
                                        <li><span class="pagination-ellipsis">&hellip;</span></li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <li class="page-item">
                                    <?php if ($pagina_atual < $total_paginas): ?>
                                        <a aria-label="Próximo" class="page-link" href="<?= $gerar_url($pagina_atual + 1); ?>">Próximo</a>
                                    <?php else: ?>
                                        <a aria-label="Próximo" class="page-link disabled">Próximo</a>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section aria-labelledby="estado-vazio" class="card border shadow-sm mt-4">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fa-solid fa-users fa-2x text-secondary"></i>
                    </div>
                    <h2 class="h5 fw-semibold" id="estado-vazio">Nenhum usuário cadastrado</h2>
                    <p class="text-secondary mb-4">Cadastre o primeiro usuário para permitir o acesso ao sistema.</p>
                    <a class="btn btn-primary" href="<?= base_url('usuario/cadastrar'); ?>">
                        <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                        Cadastrar usuário
                    </a>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <div class="modal fade" id="modalExcluirUsuario" tabindex="-1" aria-labelledby="modalExcluirUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5" id="modalExcluirUsuarioLabel">Excluir usuário</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <form id="formulario_exclusao_usuario" method="post">
                    <div class="modal-body">
                        <div id="alerta-exclusao" class="alert alert-danger d-none" role="alert"></div>
                        <p class="mb-2">Deseja realmente excluir este usuário?</p>
                        <p class="fw-semibold mb-0" id="nome-usuario-exclusao"></p>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger" id="confirmar_exclusao">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
        let usuario_exclusao = null;

        $(document).ready(function () {
            $('#limpar_filtro').click(function () {
                window.location = base_url + 'usuario';
                return;
            });

            $('#filtrar').click(function () {
                const params = new URLSearchParams();
                const termo = $('#termo').val().trim();
                const status = $('#status').val();

                if (termo) params.set('termo', termo);
                if (status) params.set('status', status);

                const query = params.toString();
                window.location = base_url + 'usuario' + (query ? '?' + query : '');
            });

            $('#termo').keypress(function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#filtrar').trigger('click');
                }
            });

            $('.excluir-usuario').click(function () {
                usuario_exclusao = $(this).data('codigo');
                $('#nome-usuario-exclusao').text($(this).data('nome'));
                $('#alerta-exclusao').empty().addClass('d-none');
            });

            $('#formulario_exclusao_usuario').on('submit', function (e) {
                e.preventDefault();

                if (!usuario_exclusao) {
                    return;
                }

                $('#confirmar_exclusao').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
                $('#alerta-exclusao').empty().addClass('d-none');

                $.ajax({
                    url: base_url + 'usuario/excluir/' + usuario_exclusao,
                    method: 'POST',
                    dataType: 'json'
                }).done(function (response) {
                    if (!response.sucesso) {
                        mostrar_erros(response.dados?.erros, 'alerta-exclusao');
                        return;
                    }

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalExcluirUsuario')).hide();
                    mostrar_feedback(response.mensagem?.conteudo, 'success');

                    setTimeout(function () {
                        window.location.reload();
                    }, 600);
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-exclusao');
                }).always(function () {
                    $('#confirmar_exclusao').prop('disabled', false).html('Excluir');
                });
            });
        });
    </script>
</body>

</html>
