<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tipo_documento extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_permissao(
            'tipos_documento.gerenciar'
        );
        $this->load->library('auditoria');
        $this->load->model('tipo_documento_model');
        $this->load->model('tipo_documento_metadado_model');
        $this->load->model('localizacao_tipo_documento_model');
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

        $pagina_atual = (int) $this->input->get('pagina', TRUE);
        if ($pagina_atual < 1) {
            $pagina_atual = 1;
        }

        $limite = 20;
        $offset = ($pagina_atual - 1) * $limite;

        $total_tipos_documento = $this->tipo_documento_model->contar_tudo(
            $filtro_termo,
            $filtro_status
        );

        $dados = [
            'filtro_termo' => $filtro_termo,
            'filtro_status' => $filtro_status,
            'tipos_documento' => $this->tipo_documento_model->listar_tudo(
                $filtro_termo,
                $filtro_status,
                $limite,
                $offset
            ),
            'total_tipos_documento' => $total_tipos_documento,
            'limite' => $limite,
            'offset' => $offset + 1,
            'pagina_atual' => $pagina_atual,
            'total_paginas' => ceil($total_tipos_documento / $limite)
        ];

        $this->load->view('tipo_documento/tipo_documento_lista', $dados);
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

            $this->db->trans_begin();

            $codigo = $this->tipo_documento_model->cadastrar(
                $resultado['dados']
            );

            $tipo_documento_novo = $codigo
                ? $this->tipo_documento_model->buscar_por_codigo(
                    $codigo
                )
                : FALSE;

            $auditoria_salva = $tipo_documento_novo
                ? $this->auditoria->registrar(
                    'tipos_documento',
                    'TIPO_DOCUMENTO_CADASTRADO',
                    'tipos_documento',
                    $codigo,
                    NULL,
                    $tipo_documento_novo
                )
                : FALSE;

            if (
                !$codigo ||
                !$tipo_documento_novo ||
                !$auditoria_salva ||
                $this->db->trans_status() === FALSE
            ) {
                $this->db->trans_rollback();

                resposta_json(
                    FALSE,
                    'Não foi possível cadastrar o tipo de documento.',
                    [],
                    500
                );
            }

            $this->db->trans_commit();

            resposta_json(
                TRUE,
                'Tipo de documento cadastrado com sucesso.',
                ['codigo' => $codigo],
                201
            );
        }

        $this->load->view('tipo_documento/tipo_documento_form');
    }

    public function atualizar($codigo = NULL)
    {
        $tipo_documento = $this->buscar_tipo_documento($codigo);
        $codigo = (int) $tipo_documento['codigo'];

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

            if (
                (int) $tipo_documento['ativo'] === 1 &&
                (int) $resultado['dados']['ativo'] === 0
            ) {
                $erros = [];

                if ($this->tipo_documento_model->possui_documentos($codigo)) {
                    $erros[] = 'O tipo de documento possui documentos vinculados.';
                }

                if ($this->localizacao_tipo_documento_model->possui_localizacoes($codigo)) {
                    $erros[] = 'O tipo de documento possui localizações vinculadas.';
                }

                if (!empty($erros)) {
                    resposta_json(
                        FALSE,
                        'Não é possível inativar o tipo de documento.',
                        ['erros' => $erros],
                        409
                    );
                }
            }

            $this->db->trans_begin();

            $atualizado = $this->tipo_documento_model->atualizar(
                $codigo,
                $resultado['dados']
            );

            $tipo_documento_atualizado = $atualizado
                ? $this->tipo_documento_model->buscar_por_codigo(
                    $codigo
                )
                : FALSE;

            $auditoria_salva = $tipo_documento_atualizado
                ? $this->auditoria->registrar(
                    'tipos_documento',
                    'TIPO_DOCUMENTO_ATUALIZADO',
                    'tipos_documento',
                    $codigo,
                    $tipo_documento,
                    $tipo_documento_atualizado
                )
                : FALSE;

            if (
                !$atualizado ||
                !$tipo_documento_atualizado ||
                !$auditoria_salva ||
                $this->db->trans_status() === FALSE
            ) {
                $this->db->trans_rollback();

                resposta_json(
                    FALSE,
                    'Não foi possível atualizar o tipo de documento.',
                    [],
                    500
                );
            }

            $this->db->trans_commit();

            resposta_json(
                TRUE,
                'Tipo de documento atualizado com sucesso.',
                ['codigo' => $codigo]
            );
        }

        $dados['tipo_documento'] = $tipo_documento;

        $this->load->view('tipo_documento/tipo_documento_form', $dados);
    }

    public function excluir($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $tipo_documento = $this->buscar_tipo_documento($codigo, TRUE);
        $codigo = (int) $tipo_documento['codigo'];
        $erros = [];

        if ($this->tipo_documento_model->possui_documentos($codigo)) {
            $erros[] = 'O tipo de documento possui documentos vinculados.';
        }

        if ($this->localizacao_tipo_documento_model->possui_localizacoes($codigo)) {
            $erros[] = 'O tipo de documento possui localizações vinculadas.';
        }

        if (!empty($erros)) {
            resposta_json(
                FALSE,
                'Não foi possível excluir o tipo de documento.',
                ['erros' => $erros],
                409
            );
        }

        $this->db->trans_begin();

        $dados_anteriores = [
            'tipo_documento' => $tipo_documento,
            'metadados' =>
                $this->tipo_documento_metadado_model
                    ->listar_por_tipo_documento($codigo)
        ];

        $vinculos_excluidos = $this->tipo_documento_metadado_model->excluir_por_tipo_documento(
            $codigo
        );

        $tipo_documento_excluido = $this->tipo_documento_model->excluir(
            $codigo
        );

        $auditoria_salva = $tipo_documento_excluido
            ? $this->auditoria->registrar(
                'tipos_documento',
                'TIPO_DOCUMENTO_EXCLUIDO',
                'tipos_documento',
                $codigo,
                $dados_anteriores,
                NULL
            )
            : FALSE;

        if (
            !$vinculos_excluidos ||
            !$tipo_documento_excluido ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível excluir o tipo de documento.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Tipo de documento excluído com sucesso.',
            ['codigo' => $codigo]
        );
    }

    public function detalhes($codigo = NULL)
    {
        $tipo_documento = $this->buscar_tipo_documento($codigo);
        $codigo = (int) $tipo_documento['codigo'];

        $dados = [
            'tipo_documento' => $tipo_documento,
            'metadados_vinculados' => $this->tipo_documento_metadado_model->listar_por_tipo_documento(
                $codigo
            ),
            'metadados_disponiveis' => $this->tipo_documento_metadado_model->listar_disponiveis(
                $codigo
            ),
            'proxima_ordem' => $this->tipo_documento_metadado_model->obter_proxima_ordem(
                $codigo
            )
        ];

        $this->load->view(
            'tipo_documento/tipo_documento_detalhes',
            $dados
        );
    }

    public function vincular_metadado($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $tipo_documento = $this->buscar_tipo_documento($codigo, TRUE);
        $codigo = (int) $tipo_documento['codigo'];

        $resultado = $this->validar_vinculo(
            $this->input->post()
        );

        if (!$resultado['sucesso']) {
            resposta_json(
                FALSE,
                'Verifique os campos informados.',
                ['erros' => $resultado['erros']],
                422
            );
        }

        $metadado_codigo = $resultado['dados']['metadado_codigo'];

        if (
            $this->tipo_documento_metadado_model->buscar_vinculo(
                $codigo,
                $metadado_codigo
            )
        ) {
            resposta_json(
                FALSE,
                'Não foi possível vincular o metadado.',
                ['erros' => ['O metadado já está vinculado ao tipo de documento.']],
                409
            );
        }

        $dados = $resultado['dados'];
        $dados['tipo_documento_codigo'] = $codigo;

        $this->db->trans_begin();

        $vinculado = $this->tipo_documento_metadado_model->vincular(
            $dados
        );

        $vinculo_novo = $vinculado
            ? $this->tipo_documento_metadado_model->buscar_vinculo(
                $codigo,
                $metadado_codigo
            )
            : FALSE;

        $auditoria_salva = $vinculo_novo
            ? $this->auditoria->registrar(
                'tipos_documento',
                'METADADO_VINCULADO',
                'tipo_documento_metadados',
                $codigo,
                NULL,
                $vinculo_novo
            )
            : FALSE;

        if (
            !$vinculado ||
            !$vinculo_novo ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível vincular o metadado.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Metadado vinculado com sucesso.',
            [
                'tipo_documento_codigo' => $codigo,
                'metadado_codigo' => $metadado_codigo
            ],
            201
        );
    }

    public function atualizar_metadado(
        $codigo = NULL,
        $metadado_codigo = NULL
    ) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $tipo_documento = $this->buscar_tipo_documento($codigo, TRUE);
        $codigo = (int) $tipo_documento['codigo'];

        if (
            empty($metadado_codigo) ||
            !ctype_digit((string) $metadado_codigo)
        ) {
            resposta_json(
                FALSE,
                'Não foi possível identificar o metadado.',
                ['erros' => ['O código do metadado é inválido.']],
                422
            );
        }

        $metadado_codigo = (int) $metadado_codigo;

        $vinculo = $this->tipo_documento_metadado_model->buscar_vinculo(
            $codigo,
            $metadado_codigo
        );

        if (!$vinculo) {
            resposta_json(
                FALSE,
                'O vínculo informado não foi encontrado.',
                [],
                404
            );
        }

        $dados_post = $this->input->post();
        $dados_post['metadado_codigo'] = $metadado_codigo;

        $resultado = $this->validar_vinculo($dados_post, TRUE);

        if (!$resultado['sucesso']) {
            resposta_json(
                FALSE,
                'Verifique os campos informados.',
                ['erros' => $resultado['erros']],
                422
            );
        }

        $dados = $resultado['dados'];
        unset($dados['metadado_codigo']);

        $this->db->trans_begin();

        $atualizado = $this->tipo_documento_metadado_model->atualizar(
            $codigo,
            $metadado_codigo,
            $dados
        );

        $vinculo_atualizado = $atualizado
            ? $this->tipo_documento_metadado_model->buscar_vinculo(
                $codigo,
                $metadado_codigo
            )
            : FALSE;

        $auditoria_salva = $vinculo_atualizado
            ? $this->auditoria->registrar(
                'tipos_documento',
                'VINCULO_METADADO_ATUALIZADO',
                'tipo_documento_metadados',
                $codigo,
                $vinculo,
                $vinculo_atualizado
            )
            : FALSE;

        if (
            !$atualizado ||
            !$vinculo_atualizado ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível atualizar o vínculo.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Vínculo atualizado com sucesso.',
            [
                'tipo_documento_codigo' => $codigo,
                'metadado_codigo' => $metadado_codigo
            ]
        );
    }

    public function desvincular_metadado(
        $codigo = NULL,
        $metadado_codigo = NULL
    ) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $tipo_documento = $this->buscar_tipo_documento($codigo, TRUE);
        $codigo = (int) $tipo_documento['codigo'];

        if (
            empty($metadado_codigo) ||
            !ctype_digit((string) $metadado_codigo)
        ) {
            resposta_json(
                FALSE,
                'Não foi possível identificar o metadado.',
                ['erros' => ['O código do metadado é inválido.']],
                422
            );
        }

        $metadado_codigo = (int) $metadado_codigo;

        $vinculo = $this->tipo_documento_metadado_model->buscar_vinculo(
            $codigo,
            $metadado_codigo
        );

        if (!$vinculo) {
            resposta_json(
                FALSE,
                'O vínculo informado não foi encontrado.',
                [],
                404
            );
        }

        $this->db->trans_begin();

        $desvinculado =
            $this->tipo_documento_metadado_model->desvincular(
                $codigo,
                $metadado_codigo
            );

        $auditoria_salva = $desvinculado
            ? $this->auditoria->registrar(
                'tipos_documento',
                'METADADO_DESVINCULADO',
                'tipo_documento_metadados',
                $codigo,
                $vinculo,
                NULL
            )
            : FALSE;

        if (
            !$desvinculado ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível desvincular o metadado.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Metadado desvinculado com sucesso.',
            [
                'tipo_documento_codigo' => $codigo,
                'metadado_codigo' => $metadado_codigo
            ]
        );
    }

    private function buscar_tipo_documento(
        $codigo,
        $resposta_json = FALSE
    ) {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            if ($resposta_json) {
                resposta_json(
                    FALSE,
                    'Não foi possível identificar o tipo de documento.',
                    ['erros' => ['O código do tipo de documento é inválido.']],
                    422
                );
            }

            show_404();
        }

        $tipo_documento = $this->tipo_documento_model->buscar_por_codigo(
            (int) $codigo
        );

        if (!$tipo_documento) {
            if ($resposta_json) {
                resposta_json(
                    FALSE,
                    'Tipo de documento não encontrado ou já excluído.',
                    [],
                    404
                );
            }

            show_404();
        }

        return $tipo_documento;
    }

    private function validar($reg)
    {
        $reg = [
            'nome' => trim($reg['nome'] ?? ''),
            'descricao' => trim($reg['descricao'] ?? ''),
            'ativo' => trim($reg['ativo'] ?? '')
        ];

        $erros = [];

        if ($reg['nome'] === '') {
            $erros[] = 'O campo Nome é obrigatório.';
        } elseif (strlen($reg['nome']) > 150) {
            $erros[] = 'O campo Nome deve possuir no máximo 150 caracteres.';
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

        $reg['ativo'] = (int) $reg['ativo'];

        return [
            'sucesso' => TRUE,
            'dados' => $reg
        ];
    }

    private function validar_vinculo($reg, $permitir_inativo = FALSE)
    {
        $reg = [
            'metadado_codigo' => trim($reg['metadado_codigo'] ?? ''),
            'ordem' => trim($reg['ordem'] ?? ''),
            'obrigatorio' => trim($reg['obrigatorio'] ?? ''),
            'visivel' => trim($reg['visivel'] ?? ''),
            'pesquisavel' => trim($reg['pesquisavel'] ?? '')
        ];

        $erros = [];

        if (
            $reg['metadado_codigo'] === '' ||
            !ctype_digit($reg['metadado_codigo']) ||
            (int) $reg['metadado_codigo'] <= 0
        ) {
            $erros[] = 'O metadado informado é inválido.';
        } else {
            $metadado = $this->metadado_model->buscar_por_codigo(
                (int) $reg['metadado_codigo']
            );

            if (
                !$metadado ||
                (!$permitir_inativo && (int) $metadado['ativo'] !== 1)
            ) {
                $erros[] = 'O metadado informado não existe ou está inativo.';
            }
        }

        if (
            $reg['ordem'] === '' ||
            !ctype_digit($reg['ordem']) ||
            (int) $reg['ordem'] <= 0 ||
            (int) $reg['ordem'] > 65535
        ) {
            $erros[] = 'A ordem deve ser um número entre 1 e 65535.';
        }

        foreach (['obrigatorio', 'visivel', 'pesquisavel'] as $campo) {
            if (!in_array($reg[$campo], ['1', '0'], TRUE)) {
                $erros[] = 'O campo ' . ucfirst($campo) . ' é inválido.';
            }
        }

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        $reg['metadado_codigo'] = (int) $reg['metadado_codigo'];
        $reg['ordem'] = (int) $reg['ordem'];
        $reg['obrigatorio'] = (int) $reg['obrigatorio'];
        $reg['visivel'] = (int) $reg['visivel'];
        $reg['pesquisavel'] = (int) $reg['pesquisavel'];

        return [
            'sucesso' => TRUE,
            'dados' => $reg
        ];
    }
}
