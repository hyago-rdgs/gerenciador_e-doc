<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pesquisa extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_permissao(
            'pesquisa.acessar'
        );
        $this->load->model('pesquisa_model');
        $this->load->model('tipo_documento_model');
        $this->load->model('localizacao_model');
    }

    public function index()
    {
        $this->avancada();
        return;
    }

    public function avancada()
    {
        $filtro_tipo_documento = $this->input->get('tipo_documento_codigo', TRUE) !== NULL
            ? $this->input->get('tipo_documento_codigo', TRUE)
            : '';

        $filtro_protocolo = $this->input->get('protocolo', TRUE) !== NULL
            ? strtoupper(trim($this->input->get('protocolo', TRUE)))
            : '';

        $filtro_titulo = $this->input->get('titulo', TRUE) !== NULL
            ? $this->input->get('titulo', TRUE)
            : '';

        $filtro_numero_identificacao = $this->input->get('numero_identificacao', TRUE) !== NULL
            ? $this->input->get('numero_identificacao', TRUE)
            : '';

        $filtro_data_inicio = $this->input->get('data_inicio', TRUE) !== NULL
            ? $this->input->get('data_inicio', TRUE)
            : '';

        $filtro_data_fim = $this->input->get('data_fim', TRUE) !== NULL
            ? $this->input->get('data_fim', TRUE)
            : '';

        $filtro_status = $this->input->get('status', TRUE) !== NULL
            ? $this->input->get('status', TRUE)
            : '';

        $filtros_metadados = $this->input->get('metadados', TRUE);
        $filtros_metadados = is_array($filtros_metadados) ? $filtros_metadados : [];

        $pesquisar = $this->input->get('pesquisar', TRUE) == 1;
        $metadados_pesquisa = [];
        $metadados_validos = [];
        $documentos = [];
        $total_documentos = 0;
        $tipo_documento_valido = FALSE;

        if (!empty($filtro_tipo_documento) && ctype_digit((string) $filtro_tipo_documento)) {
            $tipo_documento = $this->tipo_documento_model->buscar_por_codigo(
                (int) $filtro_tipo_documento
            );

            if ($tipo_documento && (int) $tipo_documento['ativo'] === 1) {
                $tipo_documento_valido = TRUE;

                $metadados_pesquisa = $this->pesquisa_model->listar_metadados_pesquisa(
                    (int) $filtro_tipo_documento
                );

                $metadados_validos = $this->preparar_metadados(
                    $metadados_pesquisa,
                    $filtros_metadados
                );
            }
        }

        $pagina_atual = (int) $this->input->get('pagina', TRUE);
        if ($pagina_atual < 1) {
            $pagina_atual = 1;
        }

        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;

        if ($pesquisar && $tipo_documento_valido) {
            $total_documentos = $this->pesquisa_model->contar_avancada(
                $filtro_tipo_documento,
                $filtro_protocolo,
                $filtro_titulo,
                $filtro_numero_identificacao,
                $filtro_data_inicio,
                $filtro_data_fim,
                $filtro_status,
                $metadados_validos
            );

            $documentos = $this->pesquisa_model->listar_avancada(
                $filtro_tipo_documento,
                $filtro_protocolo,
                $filtro_titulo,
                $filtro_numero_identificacao,
                $filtro_data_inicio,
                $filtro_data_fim,
                $filtro_status,
                $metadados_validos,
                $limite,
                $offset
            );
        }

        $dados = [
            'tipos_documento' => $this->tipo_documento_model->listar_opcoes(),
            'metadados_pesquisa' => $metadados_pesquisa,
            'documentos' => $documentos,
            'pesquisar' => $pesquisar,
            'filtro_tipo_documento' => $filtro_tipo_documento,
            'filtro_protocolo' => $filtro_protocolo,
            'filtro_titulo' => $filtro_titulo,
            'filtro_numero_identificacao' => $filtro_numero_identificacao,
            'filtro_data_inicio' => $filtro_data_inicio,
            'filtro_data_fim' => $filtro_data_fim,
            'filtro_status' => $filtro_status,
            'filtros_metadados' => $filtros_metadados,
            'total_documentos' => $total_documentos,
            'limite' => $limite,
            'offset' => $offset + 1,
            'pagina_atual' => $pagina_atual,
            'total_paginas' => ceil($total_documentos / $limite)
        ];

        $this->load->view('pesquisa/pesquisa_avancada', $dados);
    }

    public function campos_metadados($tipo_documento_codigo = NULL)
    {
        if (
            empty($tipo_documento_codigo) ||
            !ctype_digit((string) $tipo_documento_codigo)
        ) {
            resposta_json(
                FALSE,
                'O tipo de documento informado é inválido.',
                [],
                422
            );
        }

        $tipo_documento = $this->tipo_documento_model->buscar_por_codigo(
            (int) $tipo_documento_codigo
        );

        if (!$tipo_documento || (int) $tipo_documento['ativo'] !== 1) {
            resposta_json(
                FALSE,
                'O tipo de documento não existe ou está inativo.',
                [],
                404
            );
        }

        $dados = [
            'metadados_pesquisa' => $this->pesquisa_model->listar_metadados_pesquisa(
                (int) $tipo_documento_codigo
            ),
            'filtros_metadados' => []
        ];

        $html = $this->load->view(
            'pesquisa/pesquisa_campos_metadados',
            $dados,
            TRUE
        );

        resposta_json(
            TRUE,
            'Campos carregados com sucesso.',
            ['html' => $html]
        );
    }

    public function localizacao($codigo = NULL)
    {
        $localizacao = NULL;
        $caminho = [];
        $documentos = [];

        if ($codigo === NULL) {
            $localizacoes = $this->localizacao_model->listar_raizes(
                '',
                'ativo'
            );
        } else {
            if (empty($codigo) || !ctype_digit((string) $codigo)) {
                show_404();
            }

            $localizacao = $this->localizacao_model->buscar_por_codigo(
                (int) $codigo
            );

            if (!$localizacao || (int) $localizacao['ativo'] !== 1) {
                show_404();
            }

            $caminho = $this->localizacao_model->buscar_caminho(
                (int) $codigo
            );

            $localizacoes = $this->localizacao_model->listar_filhos(
                (int) $codigo,
                '',
                'ativo'
            );

            $documentos = $this->pesquisa_model->listar_documentos_localizacao(
                (int) $codigo
            );
        }

        $dados = [
            'localizacao' => $localizacao,
            'caminho' => $caminho,
            'localizacoes' => $localizacoes,
            'documentos' => $documentos
        ];

        $this->load->view('pesquisa/pesquisa_localizacao', $dados);
    }

    private function preparar_metadados($campos, $valores)
    {
        $metadados = [];

        foreach ($campos as $campo) {
            $codigo = (int) $campo['metadado_codigo'];
            $valor = $valores[$codigo] ?? '';

            if (is_array($valor)) {
                $valor = array_values(
                    array_filter(
                        array_map('trim', $valor),
                        'strlen'
                    )
                );

                if (empty($valor)) {
                    continue;
                }
            } else {
                $valor = trim((string) $valor);

                if ($valor === '') {
                    continue;
                }
            }

            $metadados[] = [
                'codigo' => $codigo,
                'tipo_campo' => $campo['tipo_campo'],
                'valor' => $valor
            ];
        }

        return $metadados;
    }
}
