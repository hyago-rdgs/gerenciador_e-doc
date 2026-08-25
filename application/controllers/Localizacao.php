<?php
class Localizacao extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_acesso();
        $this->load->model('localizacao_model');
        $this->load->model('tipo_localizacao_model');
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

        $total_localizacoes = $this->localizacao_model->contar_raizes($filtro_termo, $filtro_status);

        $dados = [
            'filtro_termo' => $filtro_termo,
            'filtro_status' => $filtro_status,

            'localizacoes' => $this->localizacao_model->listar_raizes($filtro_termo, $filtro_status, $limite, $offset),
            'total_localizacoes' => $total_localizacoes,

            'limite' => $limite,
            'offset' => $offset + 1,
            'pagina_atual' => $pagina_atual,
            'total_paginas' => ceil($total_localizacoes / $limite)
        ];

        $this->load->view('localizacao/localizacao_lista', $dados);
    }

    public function cadastrar($localizacao_codigo_pai = NULL)
    {
        if ($localizacao_codigo_pai !== NULL) {
            if (
                !ctype_digit((string) $localizacao_codigo_pai) ||
                (int) $localizacao_codigo_pai <= 0
            ) {
                show_404();
            }

            $localizacao_codigo_pai = (int) $localizacao_codigo_pai;

            $localizacao_pai = $this->localizacao_model->buscar_por_codigo(
                $localizacao_codigo_pai
            );

            if (
                !$localizacao_pai ||
                (int) $localizacao_pai['ativo'] !== 1
            ) {
                show_404();
            }
        }

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

            $dados = $resultado['dados'];

            $sequencial = $this->localizacao_model->obter_proximo_sequencial(
                $dados['localizacao_codigo_pai']
            );

            if ($sequencial === FALSE) {
                resposta_json(
                    FALSE,
                    'Não foi possível gerar o sequencial da localização.',
                    [],
                    500
                );
            }

            $classificacao = $this->gerar_classificacao(
                $dados['localizacao_codigo_pai'],
                $sequencial
            );

            if ($classificacao === FALSE) {
                resposta_json(
                    FALSE,
                    'Não foi possível gerar a classificação da localização.',
                    [],
                    500
                );
            }

            $dados['sequencial'] = $sequencial;
            $dados['classificacao'] = $classificacao;

            $codigo = $this->localizacao_model->cadastrar($dados);

            if (!$codigo) {
                resposta_json(
                    FALSE,
                    'Não foi possível cadastrar a localização.',
                    [],
                    500
                );
            }

            resposta_json(
                TRUE,
                'Localização cadastrada com sucesso.',
                ['codigo' => $codigo],
                201
            );
        }

        $dados = [
            'tipos_localizacao' => $this->tipo_localizacao_model->listar(),
            'localizacoes_opcoes' => $this->localizacao_model->listar_opcoes(),
            'localizacao_codigo_pai' => $localizacao_codigo_pai
        ];

        $this->load->view('localizacao/localizacao_form', $dados);
    }

    public function atualizar($codigo = NULL)
    {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            show_404();
        }

        $codigo = (int) $codigo;

        $localizacao = $this->localizacao_model->buscar_por_codigo($codigo);

        if (!$localizacao) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $resultado = $this->validar($this->input->post(), $codigo);

            if (!$resultado['sucesso']) {
                resposta_json(
                    FALSE,
                    'Verifique os campos informados.',
                    ['erros' => $resultado['erros']],
                    422
                );
            }

            $dados = $resultado['dados'];

            if (
                $dados['localizacao_codigo_pai'] !== NULL &&
                $this->localizacao_model->eh_descendente(
                    $codigo,
                    $dados['localizacao_codigo_pai']
                )
            ) {
                resposta_json(
                    FALSE,
                    'Não é possível mover a localização para uma de suas sublocalizações.',
                    [
                        'erros' => [
                            'A localização superior selecionada pertence à própria estrutura da localização.'
                        ]
                    ],
                    422
                );
            }

            $pai_atual = $localizacao['localizacao_codigo_pai'] !== NULL
                ? (int) $localizacao['localizacao_codigo_pai']
                : NULL;

            $novo_pai = $dados['localizacao_codigo_pai'];
            $alterou_pai = $pai_atual !== $novo_pai;

            if ($alterou_pai) {
                $sequencial = $this->localizacao_model->obter_proximo_sequencial(
                    $novo_pai
                );

                if ($sequencial === FALSE) {
                    resposta_json(
                        FALSE,
                        'Não foi possível gerar o novo sequencial da localização.',
                        [],
                        500
                    );
                }

                $classificacao = $this->gerar_classificacao(
                    $novo_pai,
                    $sequencial
                );

                if ($classificacao === FALSE) {
                    resposta_json(
                        FALSE,
                        'Não foi possível gerar a nova classificação da localização.',
                        [],
                        500
                    );
                }

                $dados['sequencial'] = $sequencial;
                $dados['classificacao'] = $classificacao;
            } else {
                $dados['sequencial'] = (int) $localizacao['sequencial'];
                $dados['classificacao'] = $localizacao['classificacao'];
            }

            $atualizado = $this->localizacao_model->atualizar(
                $codigo,
                $dados,
                $localizacao['classificacao']
            );

            if (!$atualizado) {
                resposta_json(
                    FALSE,
                    'Não foi possível atualizar a localização.',
                    [],
                    500
                );
            }

            resposta_json(
                TRUE,
                'Localização atualizada com sucesso.',
                [
                    'codigo' => $codigo,
                    'classificacao' => $dados['classificacao']
                ]
            );
        }

        $dados = [
            'localizacao' => $localizacao,
            'tipos_localizacao' => $this->tipo_localizacao_model->listar(),
            'localizacoes_opcoes' => $this->localizacao_model->listar_opcoes(
                $codigo,
                $localizacao['classificacao']
            )
        ];

        $this->load->view('localizacao/localizacao_form', $dados);
    }

    public function excluir($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        if ($codigo === NULL || !ctype_digit((string) $codigo) || (int) $codigo <= 0) {
            resposta_json(
                FALSE,
                'O código da localização é inválido.',
                [],
                422
            );
            return;
        }

        $codigo = (int) $codigo;
        $localizacao = $this->localizacao_model->buscar_por_codigo($codigo);

        if (!$localizacao) {
            resposta_json(
                FALSE,
                'A localização informada não foi encontrada.',
                [],
                404
            );
            return;
        }

        $erros = [];

        if ($this->localizacao_model->possui_sublocalizacoes($codigo)) {
            $erros[] = 'A localização possui sublocalizações vinculadas.';
        }

        if ($this->localizacao_model->possui_documentos($codigo)) {
            $erros[] = 'A localização possui documentos vinculados.';
        }

        if (!empty($erros)) {
            resposta_json(
                FALSE,
                'Não foi possível excluir a localização.',
                ['erros' => $erros],
                409
            );
            return;
        }

        if (!$this->localizacao_model->excluir($codigo)) {
            resposta_json(
                FALSE,
                'Não foi possível excluir a localização.',
                [],
                500
            );
            return;
        }

        resposta_json(
            TRUE,
            'Localização excluída com sucesso.'
        );
    }

    public function detalhes($codigo = NULL)
    {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            show_404();
        }

        $codigo = (int) $codigo;
        $localizacao = $this->localizacao_model->buscar_por_codigo($codigo);

        if (!$localizacao) {
            show_404();
        }


        // PAGINACAO LOCALIZACAO
        $filtro_termo_localizacao = $this->input->get('termo_localizacao', TRUE) !== NULL ? $this->input->get('termo_localizacao', TRUE) : '';
        $filtro_status_localizacao = $this->input->get('status_localizacao', TRUE) !== NULL ? $this->input->get('status_localizacao', TRUE) : '';

        $pagina_atual_localizacao = (int) $this->input->get('pagina_localizacao', TRUE);
        if ($pagina_atual_localizacao < 1) {
            $pagina_atual_localizacao = 1;
        }

        $limite = 20;
        $offset_localizacao = ($pagina_atual_localizacao - 1) * $limite;

        $total_localizacoes = $this->localizacao_model->contar_filhos($codigo, $filtro_termo_localizacao, $filtro_status_localizacao);

        $dados = [
            'localizacao' => $localizacao,
            
            'filtro_termo_localizacao' => $filtro_termo_localizacao,
            'filtro_status_localizacao' => $filtro_status_localizacao,

            'caminho' => $this->localizacao_model->buscar_caminho($codigo),

            'localizacoes_filho' => $this->localizacao_model->listar_filhos($codigo, $filtro_termo_localizacao, $filtro_status_localizacao, $limite, $offset_localizacao),
            'total_localizacoes' => $total_localizacoes,

            'limite' => $limite,
            'offset_localizacao' => $offset_localizacao + 1,
            'pagina_atual_localizacao' => $pagina_atual_localizacao,
            'total_paginas_localizacao' => ceil($total_localizacoes / $limite)
        ];

        $this->load->view('localizacao/localizacao_detalhes', $dados);
    }

    private function validar($dados, $codigo = NULL)
    {
        $localizacao_codigo_pai = trim($dados['localizacao_codigo_pai'] ?? '');

        $reg = [
            'nome' => trim($dados['nome'] ?? ''),
            'descricao' => trim($dados['descricao'] ?? ''),
            'ativo' => trim($dados['ativo'] ?? ''),
            'tipo_localizacao_codigo' => trim($dados['tipo_localizacao_codigo'] ?? ''),
            'localizacao_codigo_pai' => $localizacao_codigo_pai !== '' ? $localizacao_codigo_pai : NULL
        ];

        $erros = [];

        if ($reg['nome'] === '') {
            $erros[] = 'O campo Nome é obrigatório.';
        }

        if ($reg['tipo_localizacao_codigo'] === '') {
            $erros[] = 'O campo Tipo de Localização é obrigatório.';
        } elseif (!ctype_digit($reg['tipo_localizacao_codigo']) || (int) $reg['tipo_localizacao_codigo'] <= 0) {
            $erros[] = 'O tipo de localização informado é inválido.';
        } elseif (!$this->tipo_localizacao_model->buscar_por_codigo((int) $reg['tipo_localizacao_codigo'])) {
            $erros[] = 'O tipo de localização informado não existe.';
        }

        if ($reg['ativo'] === '') {
            $erros[] = 'O campo Status é obrigatório.';
        } elseif (!in_array($reg['ativo'], ['1', '0'], TRUE)) {
            $erros[] = 'O status informado é inválido.';
        }

        if ($reg['localizacao_codigo_pai'] !== NULL) {
            if (!ctype_digit($reg['localizacao_codigo_pai']) || (int) $reg['localizacao_codigo_pai'] <= 0) {
                $erros[] = 'A localização superior informada é inválida.';
            } elseif (!$this->localizacao_model->buscar_por_codigo((int) $reg['localizacao_codigo_pai'])) {
                $erros[] = 'A localização superior informada não existe.';
            } elseif ($codigo !== NULL && (int) $reg['localizacao_codigo_pai'] === (int) $codigo) {
                $erros[] = 'Uma localização não pode ser superior a ela mesma.';
            }
        }

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        $reg['ativo'] = (int) $reg['ativo'];
        $reg['tipo_localizacao_codigo'] = (int) $reg['tipo_localizacao_codigo'];

        if ($reg['localizacao_codigo_pai'] !== NULL) {
            $reg['localizacao_codigo_pai'] = (int) $reg['localizacao_codigo_pai'];
        }

        return [
            'sucesso' => TRUE,
            'dados' => $reg
        ];
    }

    private function gerar_classificacao($localizacao_codigo_pai, $sequencial)
    {
        $segmento = str_pad((string) $sequencial, 2, '0', STR_PAD_LEFT);

        if ($localizacao_codigo_pai === NULL) {
            return $segmento;
        }

        $localizacao_pai = $this->localizacao_model->buscar_por_codigo($localizacao_codigo_pai);

        if (!$localizacao_pai) {
            return FALSE;
        }

        return $localizacao_pai['classificacao'] . '.' . $segmento;
    }
}
