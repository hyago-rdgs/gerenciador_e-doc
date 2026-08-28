<?php
class Metadado extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_acesso();
        $this->load->model('metadado_model');
    }

    public function index()
    {
        $this->listar();
        return;
    }

    public function listar()
    {
        $filtro_termo = $this->input->get('termo', TRUE) !== NULL ? $this->input->get('termo', TRUE) : '';
        $filtro_status = $this->input->get('status', TRUE) !== NULL ? $this->input->get('status', TRUE) : '';
        $filtro_tipo_campo = $this->input->get('tipo_campo', TRUE) !== NULL ? $this->input->get('tipo_campo', TRUE) : '';

        $pagina_atual = (int) $this->input->get('pagina', TRUE);
        if ($pagina_atual < 1) {
            $pagina_atual = 1;
        }

        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;

        $total_metadados = $this->metadado_model->contar_tudo(
            $filtro_termo,
            $filtro_status,
            $filtro_tipo_campo
        );

        $dados = [
            'filtro_termo' => $filtro_termo,
            'filtro_status' => $filtro_status,
            'filtro_tipo_campo' => $filtro_tipo_campo,

            'metadados' => $this->metadado_model->listar_tudo(
                $filtro_termo,
                $filtro_status,
                $filtro_tipo_campo,
                $limite,
                $offset
            ),
            'total_metadados' => $total_metadados,

            'limite' => $limite,
            'offset' => $offset + 1,
            'pagina_atual' => $pagina_atual,
            'total_paginas' => ceil($total_metadados / $limite)
        ];

        $this->load->view('metadado/metadado_lista', $dados);
    }

    public function cadastrar()
    {
        if ($this->input->method() === 'post') {
            $resultado = $this->validar($this->input->post());

            if (!$resultado['sucesso']) {
                resposta_json(
                    FALSE,
                    'Verifique os campos informados.',
                    ['erros' => $resultado['erros']],
                    422
                );
            }

            $codigo = $this->metadado_model->cadastrar(
                $resultado['dados']
            );

            if (!$codigo) {
                resposta_json(
                    FALSE,
                    'Não foi possível cadastrar o metadado.',
                    [],
                    500
                );
            }

            resposta_json(
                TRUE,
                'Metadado cadastrado com sucesso.',
                ['codigo' => $codigo],
                201
            );
        }

        $this->load->view('metadado/metadado_form');
    }

    public function atualizar($codigo = NULL)
    {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            show_404();
        }

        $codigo = (int) $codigo;

        $metadado = $this->metadado_model->buscar_por_codigo($codigo);

        if (!$metadado) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $resultado = $this->validar(
                $this->input->post(),
                $codigo
            );

            if (!$resultado['sucesso']) {
                resposta_json(
                    FALSE,
                    'Verifique os campos informados.',
                    ['erros' => $resultado['erros']],
                    422
                );
            }

            $atualizado = $this->metadado_model->atualizar(
                $codigo,
                $resultado['dados']
            );

            if (!$atualizado) {
                resposta_json(
                    FALSE,
                    'Não foi possível atualizar o metadado.',
                    [],
                    500
                );
            }

            resposta_json(
                TRUE,
                'Metadado atualizado com sucesso.',
                ['codigo' => $codigo]
            );
        }

        $dados['metadado'] = $metadado;

        $this->load->view('metadado/metadado_form', $dados);
    }

    public function excluir($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        if (
            empty($codigo) ||
            !ctype_digit((string) $codigo)
        ) {
            resposta_json(
                FALSE,
                'Não foi possível identificar o metadado.',
                ['erros' => ['O código do metadado é inválido.']],
                422
            );
        }

        $codigo = (int) $codigo;

        $metadado = $this->metadado_model->buscar_por_codigo($codigo);

        if (!$metadado) {
            resposta_json(
                FALSE,
                'Metadado não encontrado ou já excluído.',
                [],
                404
            );
        }

        if (!$this->metadado_model->excluir($codigo)) {
            resposta_json(
                FALSE,
                'Não foi possível excluir o metadado.',
                [],
                500
            );
        }

        resposta_json(
            TRUE,
            'Metadado excluído com sucesso.',
            ['codigo' => $codigo]
        );
    }

    private function validar($reg, $codigo = NULL)
    {
        $reg = [
            'chave' => strtolower(trim($reg['chave'] ?? '')),
            'nome' => trim($reg['nome'] ?? ''),
            'descricao' => trim($reg['descricao'] ?? ''),
            'tipo_campo' => trim($reg['tipo_campo'] ?? ''),
            'mascara' => trim($reg['mascara'] ?? ''),
            'opcoes' => trim($reg['opcoes'] ?? ''),
            'ativo' => trim($reg['ativo'] ?? '')
        ];

        $tipos_campo = [
            'text',
            'number',
            'date',
            'time',
            'datetime-local',
            'email',
            'tel',
            'url',
            'textarea',
            'select',
            'radio',
            'checkbox'
        ];

        $tipos_com_opcoes = [
            'select',
            'radio',
            'checkbox'
        ];

        $tipos_com_mascara = [
            'text',
            'tel'
        ];

        $erros = [];

        if ($reg['chave'] !== '') {
            if (strlen($reg['chave']) > 100) {
                $erros[] = 'A chave deve possuir no máximo 100 caracteres.';
            } elseif (!preg_match('/^[a-z0-9_]+$/', $reg['chave'])) {
                $erros[] = 'A chave deve conter apenas letras minúsculas, números e sublinhado.';
            } elseif ($this->metadado_model->chave_em_uso($reg['chave'], $codigo)) {
                $erros[] = 'A chave informada já foi utilizada por outro metadado.';
            }
        }

        if ($reg['nome'] === '') {
            $erros[] = 'O campo Nome é obrigatório.';
        }

        if ($reg['tipo_campo'] === '') {
            $erros[] = 'O campo Tipo de Campo é obrigatório.';
        } elseif (!in_array($reg['tipo_campo'], $tipos_campo, TRUE)) {
            $erros[] = 'O tipo de campo informado é inválido.';
        }

        if (
            in_array($reg['tipo_campo'], $tipos_com_opcoes, TRUE) &&
            $reg['opcoes'] === ''
        ) {
            $erros[] = 'Informe as opções disponíveis para o campo.';
        }

        if ($reg['ativo'] === '') {
            $erros[] = 'O campo Status é obrigatório.';
        } elseif (!in_array($reg['ativo'], ['1', '0'], TRUE)) {
            $erros[] = 'O status informado é inválido.';
        }

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        if (!in_array($reg['tipo_campo'], $tipos_com_opcoes, TRUE)) {
            $reg['opcoes'] = '';
        }

        if (!in_array($reg['tipo_campo'], $tipos_com_mascara, TRUE)) {
            $reg['mascara'] = '';
        }

        $reg['chave'] = $reg['chave'] !== '' ? $reg['chave'] : NULL;
        $reg['ativo'] = (int) $reg['ativo'];

        return [
            'sucesso' => TRUE,
            'dados' => $reg
        ];
    }
}
