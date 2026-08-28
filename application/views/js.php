<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {
        $('.log-out').on('click', function () {
            window.location = '<?= base_url('autenticacao/logout') ?>';
            return;
        });

        $('.dropdown-toggle-acoes').each(function () {
                new bootstrap.Dropdown(this, {
                    boundary: 'viewport',
                    popperConfig: function (config) {
                        config.strategy = 'fixed';

                        return config;
                    }
                });
            });
    });

    function mostrar_erros(erros, elemento_id) {
        if (!Array.isArray(erros)) {
            erros = [erros || 'Não foi possível concluir a operação.'];
        }

        const titulo = $('<strong>').text(
            'Por favor, corrija os seguintes erros:'
        );

        const lista = $('<ul>', {
            class: 'mb-0 mt-2 ps-3'
        });

        erros.forEach(function (erro) {
            $('<li>').text(erro).appendTo(lista);
        });

        $('#' + elemento_id).empty().append(titulo, lista).removeClass('d-none');
    }

    function mostrar_erro_ajax(xhr, elemento_id) {
        const response = xhr.responseJSON;

        if (
            xhr.status === 401 &&
            response?.dados?.redirecionar
        ) {
            window.location.href = response.dados.redirecionar;
            return;
        }
        if (response?.dados?.erros) {
            mostrar_erros(
                response.dados.erros,
                elemento_id
            );

            return;
        }

        mostrar_erros(
            response?.mensagem?.conteudo ||
            'Não foi possível comunicar com o servidor. Tente novamente.',
            elemento_id
        );
    }

    function mostrar_feedback(mensagem, tipo = 'success') {
        const configuracoes = {
            success: {
                icone: 'fa-circle-check',
                cor: 'text-success'
            },
            error: {
                icone: 'fa-circle-xmark',
                cor: 'text-danger'
            },
            warning: {
                icone: 'fa-triangle-exclamation',
                cor: 'text-warning'
            },
            info: {
                icone: 'fa-circle-info',
                cor: 'text-primary'
            }
        };

        const configuracao = configuracoes[tipo] || configuracoes.info;

        $('#toast-mensagem').text(mensagem);

        $('#toast-icone')
            .attr(
                'class',
                `fa-solid ${configuracao.icone} ${configuracao.cor} fs-5`
            );

        const elemento = document.getElementById('toast-feedback');

        bootstrap.Toast.getOrCreateInstance(elemento, {
            delay: 4000
        }).show();
    }
</script>