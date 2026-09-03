<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditoria_geral extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->controle_acesso->valida_acesso();
        $this->controle_acesso->valida_permissao(
            'auditoria.visualizar'
        );

        $this->load->model('auditoria_model');
    }

    public function index()
    {
        $this->listar();
        return;
    }

    public function listar()
    {
        $filtro_termo = trim(
            (string) ($this->input->get('termo', TRUE) ?? '')
        );
        $filtro_modulo = trim(
            (string) ($this->input->get('modulo', TRUE) ?? '')
        );
        $filtro_acao = trim(
            (string) ($this->input->get('acao', TRUE) ?? '')
        );
        $filtro_usuario = trim(
            (string) ($this->input->get('usuario_codigo', TRUE) ?? '')
        );
        $filtro_data_inicio = $this->validar_data(
            $this->input->get('data_inicio', TRUE)
        );
        $filtro_data_fim = $this->validar_data(
            $this->input->get('data_fim', TRUE)
        );

        if (
            $filtro_usuario !== '' &&
            $filtro_usuario !== 'sistema' &&
            !ctype_digit($filtro_usuario)
        ) {
            $filtro_usuario = '';
        }

        $pagina_atual = (int) $this->input->get('pagina', TRUE);

        if ($pagina_atual < 1) {
            $pagina_atual = 1;
        }

        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;

        $total_auditorias = $this->auditoria_model->contar_tudo(
            $filtro_termo,
            $filtro_modulo,
            $filtro_acao,
            $filtro_usuario,
            $filtro_data_inicio,
            $filtro_data_fim
        );

        $dados = [
            'filtro_termo' => $filtro_termo,
            'filtro_modulo' => $filtro_modulo,
            'filtro_acao' => $filtro_acao,
            'filtro_usuario' => $filtro_usuario,
            'filtro_data_inicio' => $filtro_data_inicio,
            'filtro_data_fim' => $filtro_data_fim,
            'auditorias' => $this->auditoria_model->listar_tudo(
                $filtro_termo,
                $filtro_modulo,
                $filtro_acao,
                $filtro_usuario,
                $filtro_data_inicio,
                $filtro_data_fim,
                $limite,
                $offset
            ),
            'modulos' => $this->auditoria_model->listar_modulos(),
            'acoes' => $this->auditoria_model->listar_acoes(),
            'usuarios' => $this->auditoria_model->listar_usuarios(),
            'total_auditorias' => $total_auditorias,
            'limite' => $limite,
            'offset' => $offset + 1,
            'pagina_atual' => $pagina_atual,
            'total_paginas' => ceil($total_auditorias / $limite)
        ];

        $this->load->view(
            'auditoria/auditoria_lista',
            $dados
        );
    }

    public function detalhes($codigo = NULL)
    {
        if (
            empty($codigo) ||
            !ctype_digit((string) $codigo)
        ) {
            resposta_json(
                FALSE,
                'Não foi possível identificar o registro de auditoria.',
                [],
                422
            );
        }

        $auditoria = $this->auditoria_model->buscar_por_codigo(
            (int) $codigo
        );

        if (!$auditoria) {
            resposta_json(
                FALSE,
                'Registro de auditoria não encontrado.',
                [],
                404
            );
        }

        $auditoria['dados_anteriores_formatados'] =
            $this->formatar_json(
                $auditoria['dados_anteriores']
            );

        $auditoria['dados_novos_formatados'] =
            $this->formatar_json(
                $auditoria['dados_novos']
            );

        $html = $this->load->view(
            'auditoria/auditoria_detalhes',
            ['auditoria' => $auditoria],
            TRUE
        );

        resposta_json(
            TRUE,
            'Registro de auditoria carregado.',
            ['html' => $html]
        );
    }

    private function validar_data($data)
    {
        $data = trim((string) $data);

        if ($data === '') {
            return '';
        }

        $objeto = DateTime::createFromFormat(
            'Y-m-d',
            $data
        );

        return (
            $objeto &&
            $objeto->format('Y-m-d') === $data
        )
            ? $data
            : '';
    }

    private function formatar_json($json)
    {
        if ($json === NULL || $json === '') {
            return NULL;
        }

        $dados = json_decode($json, TRUE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $json;
        }

        return json_encode(
            $dados,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );
    }
}
