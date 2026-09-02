<script>
    $(document).ready(function () {
        const graficos = [];

        $.ajax({
            url: '<?= base_url('dashboard/dados'); ?>',
            type: 'GET',
            dataType: 'json'
        })
            .done(function (response) {
                if (!response?.sucesso) {
                    exibir_erro_dashboard(
                        response?.mensagem?.conteudo ||
                        'Não foi possível carregar os indicadores.'
                    );
                    return;
                }

                const dados = response.dados || {};

                preencher_resumo(dados.resumo || {});
                preencher_atencoes(dados.atencoes || {});
                preencher_movimentacoes(dados.movimentacoes_recentes || []);

                graficos.push(
                    criar_grafico_documentos_mes(dados.documentos_por_mes || []),
                    criar_grafico_documentos_tipo(dados.documentos_por_tipo || []),
                    criar_grafico_digitalizacao(dados.digitalizacao || {}),
                    criar_grafico_documentos_localizacao(
                        dados.documentos_por_localizacao || []
                    )
                );
            })
            .fail(function (xhr) {
                const response = xhr.responseJSON;

                if (
                    xhr.status === 401 &&
                    response?.dados?.redirecionar
                ) {
                    window.location.href = response.dados.redirecionar;
                    return;
                }

                exibir_erro_dashboard(
                    response?.mensagem?.conteudo ||
                    'Não foi possível carregar os indicadores.'
                );
            });

        $(window).on('resize', function () {
            graficos.forEach(function (grafico) {
                if (grafico) {
                    grafico.resize();
                }
            });
        });
    });

    function preencher_resumo(resumo) {
        $('#indicador-total-documentos').text(formatar_numero(resumo.total_documentos));
        $('#indicador-documentos-mes').text(formatar_numero(resumo.documentos_mes));
        $('#indicador-localizacoes').text(formatar_numero(resumo.total_localizacoes));
        $('#indicador-movimentacoes-mes').text(formatar_numero(resumo.movimentacoes_mes));
        $('#indicador-digitalizacao').text(
            formatar_decimal(resumo.digitalizacao_percentual) + '%'
        );
    }

    function preencher_atencoes(atencoes) {
        $('#atencao-sem-arquivo').text(
            formatar_numero(atencoes.documentos_sem_arquivo)
        );
        $('#atencao-retiradas-abertas').text(
            formatar_numero(atencoes.retiradas_abertas)
        );
        $('#atencao-retiradas-atrasadas').text(
            formatar_numero(atencoes.retiradas_atrasadas)
        );
    }

    function preencher_movimentacoes(movimentacoes) {
        const tbody = $('#movimentacoes-recentes');
        tbody.empty();

        if (!movimentacoes.length) {
            $('<tr>')
                .append(
                    $('<td>', {
                        colspan: 6,
                        class: 'text-center text-body-secondary py-4',
                        text: 'Nenhuma movimentação registrada.'
                    })
                )
                .appendTo(tbody);
            return;
        }

        movimentacoes.forEach(function (movimentacao) {
            const documento = $('<a>', {
                href: '<?= base_url('documento/detalhes/'); ?>' +
                    movimentacao.documento_codigo,
                class: 'text-decoration-none',
                text: movimentacao.documento_protocolo ||
                    movimentacao.documento_titulo
            });

            $('<tr>')
                .append(
                    $('<td>', {
                        class: 'text-nowrap',
                        text: formatar_data_hora(movimentacao.data_movimentacao)
                    }),
                    $('<td>').append(documento),
                    $('<td>').append(
                        $('<span>', {
                            class: 'badge text-bg-light border',
                            text: formatar_tipo_movimentacao(
                                movimentacao.tipo_movimentacao
                            )
                        })
                    ),
                    $('<td>', {
                        text: movimentacao.localizacao_origem || '—'
                    }),
                    $('<td>', {
                        text: movimentacao.localizacao_destino ||
                            movimentacao.responsavel_nome ||
                            '—'
                    }),
                    $('<td>', {
                        text: movimentacao.usuario_nome || '—'
                    })
                )
                .appendTo(tbody);
        });
    }

    function criar_grafico_documentos_mes(dados) {
        const grafico = echarts.init(
            document.getElementById('grafico-documentos-mes')
        );

        grafico.setOption({
            tooltip: {
                trigger: 'axis'
            },
            grid: {
                left: 20,
                right: 20,
                top: 30,
                bottom: 20,
                containLabel: true
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: dados.map(function (item) {
                    return item.rotulo;
                })
            },
            yAxis: {
                type: 'value',
                minInterval: 1
            },
            series: [{
                name: 'Documentos',
                type: 'line',
                smooth: true,
                data: dados.map(function (item) {
                    return Number(item.total || 0);
                })
            }]
        });

        return grafico;
    }

    function criar_grafico_documentos_tipo(dados) {
        const grafico = echarts.init(
            document.getElementById('grafico-documentos-tipo')
        );

        const ordenados = [...dados].reverse();

        grafico.setOption({
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            grid: {
                left: 20,
                right: 20,
                top: 10,
                bottom: 20,
                containLabel: true
            },
            xAxis: {
                type: 'value',
                minInterval: 1
            },
            yAxis: {
                type: 'category',
                data: ordenados.map(function (item) {
                    return item.nome;
                })
            },
            series: [{
                name: 'Documentos',
                type: 'bar',
                data: ordenados.map(function (item) {
                    return Number(item.total || 0);
                })
            }]
        });

        return grafico;
    }

    function criar_grafico_digitalizacao(dados) {
        const grafico = echarts.init(
            document.getElementById('grafico-digitalizacao')
        );

        grafico.setOption({
            tooltip: {
                trigger: 'item'
            },
            legend: {
                bottom: 0
            },
            series: [{
                name: 'Documentos',
                type: 'pie',
                radius: ['48%', '72%'],
                avoidLabelOverlap: true,
                label: {
                    formatter: '{b}\n{c} ({d}%)'
                },
                data: [
                    {
                        name: 'Com arquivo',
                        value: Number(dados.com_arquivo || 0)
                    },
                    {
                        name: 'Sem arquivo',
                        value: Number(dados.sem_arquivo || 0)
                    }
                ]
            }]
        });

        return grafico;
    }

    function criar_grafico_documentos_localizacao(dados) {
        const grafico = echarts.init(
            document.getElementById('grafico-documentos-localizacao')
        );

        const ordenados = [...dados].reverse();

        grafico.setOption({
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            grid: {
                left: 20,
                right: 20,
                top: 10,
                bottom: 20,
                containLabel: true
            },
            xAxis: {
                type: 'value',
                minInterval: 1
            },
            yAxis: {
                type: 'category',
                data: ordenados.map(function (item) {
                    const classificacao = item.classificacao
                        ? item.classificacao + ' · '
                        : '';

                    return classificacao + item.nome;
                })
            },
            series: [{
                name: 'Documentos',
                type: 'bar',
                data: ordenados.map(function (item) {
                    return Number(item.total || 0);
                })
            }]
        });

        return grafico;
    }

    function formatar_numero(valor) {
        return new Intl.NumberFormat('pt-BR').format(
            Number(valor || 0)
        );
    }

    function formatar_decimal(valor) {
        return new Intl.NumberFormat('pt-BR', {
            minimumFractionDigits: 1,
            maximumFractionDigits: 1
        }).format(Number(valor || 0));
    }

    function formatar_data_hora(valor) {
        if (!valor) {
            return '—';
        }

        const partes = valor.split(' ');
        const data = partes[0]?.split('-') || [];
        const hora = partes[1]?.substring(0, 5) || '';

        if (data.length !== 3) {
            return valor;
        }

        return `${data[2]}/${data[1]}/${data[0]} ${hora}`.trim();
    }

    function formatar_tipo_movimentacao(tipo) {
        const tipos = {
            CADASTRO: 'Cadastro',
            TRANSFERENCIA: 'Transferência',
            RETIRADA: 'Retirada',
            DEVOLUCAO: 'Devolução'
        };

        return tipos[tipo] || tipo || '—';
    }

    function exibir_erro_dashboard(mensagem) {
        $('#dashboard-erro')
            .text(mensagem)
            .removeClass('d-none');
    }
</script>
