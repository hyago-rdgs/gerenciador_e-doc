<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Localizacao extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_acesso();
        $this->load->library('auditoria');
        $this->load->model('localizacao_model');
        $this->load->model('tipo_localizacao_model');
        $this->load->model('tipo_documento_model');
        $this->load->model('localizacao_tipo_documento_model');
        $this->load->model('documento_model');
    }

    public function index()
    {
        $this->listar();
        return;
    }

    public function listar()
    {
        $this->controle_acesso->valida_permissao(
            'localizacoes.visualizar'
        );

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
            'localizacoes' => $this->localizacao_model->listar_raizes(
                $filtro_termo,
                $filtro_status,
                $limite,
                $offset
            ),
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
        $this->controle_acesso->valida_permissao(
            'localizacoes.gerenciar'
        );

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

            $this->db->trans_begin();

            $codigo = $this->localizacao_model->cadastrar($dados);

            $vinculo_salvo = $codigo
                ? $this->localizacao_tipo_documento_model->salvar_tipo_unico(
                    $codigo,
                    $resultado['tipo_documento_codigo']
                )
                : FALSE;

            $dados_auditoria = (
                $codigo &&
                $vinculo_salvo
            )
                ? $this->preparar_dados_auditoria_localizacao(
                    $codigo
                )
                : FALSE;

            $auditoria_salva = $dados_auditoria
                ? $this->auditoria->registrar(
                    'localizacoes',
                    'LOCALIZACAO_CADASTRADA',
                    'localizacoes',
                    $codigo,
                    NULL,
                    $dados_auditoria
                )
                : FALSE;

            if (
                !$codigo ||
                !$vinculo_salvo ||
                !$dados_auditoria ||
                !$auditoria_salva ||
                $this->db->trans_status() === FALSE
            ) {
                $this->db->trans_rollback();

                resposta_json(
                    FALSE,
                    'Não foi possível cadastrar a localização.',
                    [],
                    500
                );
            }

            $this->db->trans_commit();

            resposta_json(
                TRUE,
                'Localização cadastrada com sucesso.',
                ['codigo' => $codigo],
                201
            );
        }

        $dados = [
            'tipos_localizacao' => $this->tipo_localizacao_model->listar(),
            'tipos_documento' => $this->tipo_documento_model->listar_opcoes(),
            'localizacoes_opcoes' => $this->localizacao_model->listar_opcoes(),
            'localizacao_codigo_pai' => $localizacao_codigo_pai
        ];

        $this->load->view('localizacao/localizacao_form', $dados);
    }

    public function atualizar($codigo = NULL)
    {
        $this->controle_acesso->valida_permissao(
            'localizacoes.gerenciar'
        );

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

            $dados_anteriores =
                $this->preparar_dados_auditoria_localizacao(
                    $codigo
                );

            $this->db->trans_begin();

            $localizacao_atualizada = $this->localizacao_model->atualizar(
                $codigo,
                $dados,
                $localizacao['classificacao']
            );

            $vinculo_atualizado = $localizacao_atualizada
                ? $this->localizacao_tipo_documento_model->salvar_tipo_unico(
                    $codigo,
                    $resultado['tipo_documento_codigo']
                )
                : FALSE;

            $dados_novos = (
                $localizacao_atualizada &&
                $vinculo_atualizado
            )
                ? $this->preparar_dados_auditoria_localizacao(
                    $codigo
                )
                : FALSE;

            $auditoria_salva = (
                $dados_anteriores &&
                $dados_novos
            )
                ? $this->auditoria->registrar(
                    'localizacoes',
                    'LOCALIZACAO_ATUALIZADA',
                    'localizacoes',
                    $codigo,
                    $dados_anteriores,
                    $dados_novos
                )
                : FALSE;

            if (
                !$dados_anteriores ||
                !$localizacao_atualizada ||
                !$vinculo_atualizado ||
                !$dados_novos ||
                !$auditoria_salva ||
                $this->db->trans_status() === FALSE
            ) {
                $this->db->trans_rollback();

                resposta_json(
                    FALSE,
                    'Não foi possível atualizar a localização.',
                    [],
                    500
                );
            }

            $this->db->trans_commit();

            resposta_json(
                TRUE,
                'Localização atualizada com sucesso.',
                [
                    'codigo' => $codigo,
                    'classificacao' => $dados['classificacao']
                ]
            );
        }

        $tipo_documento = $this->localizacao_tipo_documento_model->buscar_por_localizacao(
            $codigo
        );

        $dados = [
            'localizacao' => $localizacao,
            'tipo_documento_codigo' => $tipo_documento
                ? $tipo_documento['tipo_documento_codigo']
                : NULL,
            'tipos_localizacao' => $this->tipo_localizacao_model->listar(),
            'tipos_documento' => $this->tipo_documento_model->listar_opcoes(),
            'localizacoes_opcoes' => $this->localizacao_model->listar_opcoes(
                $codigo,
                $localizacao['classificacao']
            )
        ];

        $this->load->view('localizacao/localizacao_form', $dados);
    }

    public function excluir($codigo = NULL)
    {
        $this->controle_acesso->valida_permissao(
            'localizacoes.gerenciar'
        );

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

        if ($this->documento_model->possui_documentos($codigo)) {
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

        $this->db->trans_begin();

        $dados_anteriores =
            $this->preparar_dados_auditoria_localizacao(
                $codigo
            );

        $vinculos_excluidos = $this->localizacao_tipo_documento_model->desvincular_por_localizacao(
            $codigo
        );

        $localizacao_excluida = $vinculos_excluidos
            ? $this->localizacao_model->excluir($codigo)
            : FALSE;

        $auditoria_salva = (
            $dados_anteriores &&
            $localizacao_excluida
        )
            ? $this->auditoria->registrar(
                'localizacoes',
                'LOCALIZACAO_EXCLUIDA',
                'localizacoes',
                $codigo,
                $dados_anteriores,
                NULL
            )
            : FALSE;

        if (
            !$dados_anteriores ||
            !$vinculos_excluidos ||
            !$localizacao_excluida ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível excluir a localização.',
                [],
                500
            );
            return;
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Localização excluída com sucesso.'
        );
    }

    public function detalhes($codigo = NULL)
    {
        $this->controle_acesso->valida_permissao(
            'localizacoes.visualizar'
        );

        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            show_404();
        }

        $codigo = (int) $codigo;
        $localizacao = $this->localizacao_model->buscar_por_codigo($codigo);

        if (!$localizacao) {
            show_404();
        }

        $filtro_termo_localizacao = $this->input->get('termo_localizacao', TRUE) !== NULL ? $this->input->get('termo_localizacao', TRUE) : '';
        $filtro_status_localizacao = $this->input->get('status_localizacao', TRUE) !== NULL ? $this->input->get('status_localizacao', TRUE) : '';

        $pagina_atual_localizacao = (int) $this->input->get('pagina_localizacao', TRUE);
        if ($pagina_atual_localizacao < 1) {
            $pagina_atual_localizacao = 1;
        }

        $pagina_atual_documento = (int) $this->input->get('pagina_documento', TRUE);
        if ($pagina_atual_documento < 1) {
            $pagina_atual_documento = 1;
        }

        $limite = 20;
        $offset_localizacao = ($pagina_atual_localizacao - 1) * $limite;
        $offset_documento = ($pagina_atual_documento - 1) * $limite;

        $total_localizacoes = $this->localizacao_model->contar_filhos(
            $codigo,
            $filtro_termo_localizacao,
            $filtro_status_localizacao
        );

        $pode_visualizar_documentos = $this->controle_acesso->tem_permissao(
            'documentos.visualizar'
        );

        $total_documentos = $pode_visualizar_documentos
            ? $this->documento_model->contar_por_localizacao($codigo)
            : 0;

        $dados = [
            'localizacao' => $localizacao,
            'tipo_documento' => $this->localizacao_tipo_documento_model->buscar_por_localizacao(
                $codigo
            ),
            'caminho' => $this->localizacao_model->buscar_caminho($codigo),
            'filtro_termo_localizacao' => $filtro_termo_localizacao,
            'filtro_status_localizacao' => $filtro_status_localizacao,
            'localizacoes_filho' => $this->localizacao_model->listar_filhos(
                $codigo,
                $filtro_termo_localizacao,
                $filtro_status_localizacao,
                $limite,
                $offset_localizacao
            ),
            'total_localizacoes' => $total_localizacoes,
            'pode_visualizar_documentos' => $pode_visualizar_documentos,
            'documentos' => $pode_visualizar_documentos
                ? $this->documento_model->listar_por_localizacao(
                    $codigo,
                    $limite,
                    $offset_documento
                )
                : [],
            'total_documentos' => $total_documentos,
            'limite' => $limite,
            'offset_localizacao' => $offset_localizacao + 1,
            'pagina_atual_localizacao' => $pagina_atual_localizacao,
            'total_paginas_localizacao' => ceil($total_localizacoes / $limite),
            'offset_documento' => $offset_documento + 1,
            'pagina_atual_documento' => $pagina_atual_documento,
            'total_paginas_documento' => ceil($total_documentos / $limite)
        ];

        $this->load->view('localizacao/localizacao_detalhes', $dados);
    }

    private function preparar_dados_auditoria_localizacao($codigo)
    {
        $localizacao = $this->localizacao_model->buscar_por_codigo(
            (int) $codigo
        );

        if (!$localizacao) {
            return FALSE;
        }

        return [
            'codigo' => (int) $localizacao['codigo'],
            'protocolo' => $localizacao['protocolo'],
            'tipo_localizacao_codigo' =>
                (int) $localizacao['tipo_localizacao_codigo'],
            'localizacao_codigo_pai' =>
                $localizacao['localizacao_codigo_pai'] !== NULL
                    ? (int) $localizacao['localizacao_codigo_pai']
                    : NULL,
            'classificacao' => $localizacao['classificacao'],
            'sequencial' => (int) $localizacao['sequencial'],
            'nome' => $localizacao['nome'],
            'descricao' => $localizacao['descricao'],
            'ativo' => (int) $localizacao['ativo'],
            'tipo_documento_codigo' =>
                $localizacao['tipo_documento_codigo'] !== NULL
                    ? (int) $localizacao['tipo_documento_codigo']
                    : NULL
        ];
    }

    private function validar($dados, $codigo = NULL)
    {
        $localizacao_codigo_pai = trim($dados['localizacao_codigo_pai'] ?? '');
        $tipo_documento_codigo = trim($dados['tipo_documento_codigo'] ?? '');

        $reg = [
            'nome' => trim($dados['nome'] ?? ''),
            'descricao' => trim($dados['descricao'] ?? ''),
            'ativo' => trim($dados['ativo'] ?? ''),
            'tipo_localizacao_codigo' => trim($dados['tipo_localizacao_codigo'] ?? ''),
            'localizacao_codigo_pai' => $localizacao_codigo_pai !== ''
                ? $localizacao_codigo_pai
                : NULL
        ];

        $tipo_documento_codigo = $tipo_documento_codigo !== ''
            ? $tipo_documento_codigo
            : NULL;

        $erros = [];

        if ($reg['nome'] === '') {
            $erros[] = 'O campo Nome é obrigatório.';
        } elseif (strlen($reg['nome']) > 255) {
            $erros[] = 'O campo Nome deve possuir no máximo 255 caracteres.';
        }

        if ($reg['tipo_localizacao_codigo'] === '') {
            $erros[] = 'O campo Tipo de Localização é obrigatório.';
        } elseif (!ctype_digit($reg['tipo_localizacao_codigo']) || (int) $reg['tipo_localizacao_codigo'] <= 0) {
            $erros[] = 'O tipo de localização informado é inválido.';
        } elseif (!$this->tipo_localizacao_model->buscar_por_codigo((int) $reg['tipo_localizacao_codigo'])) {
            $erros[] = 'O tipo de localização informado não existe.';
        }

        if ($tipo_documento_codigo !== NULL) {
            if (!ctype_digit($tipo_documento_codigo) || (int) $tipo_documento_codigo <= 0) {
                $erros[] = 'O tipo de documento informado é inválido.';
            } else {
                $tipo_documento = $this->tipo_documento_model->buscar_por_codigo(
                    (int) $tipo_documento_codigo
                );

                if (!$tipo_documento || (int) $tipo_documento['ativo'] !== 1) {
                    $erros[] = 'O tipo de documento informado não existe ou está inativo.';
                }
            }
        }

        if ($reg['ativo'] === '') {
            $erros[] = 'O campo Status é obrigatório.';
        } elseif (!in_array($reg['ativo'], ['1', '0'], TRUE)) {
            $erros[] = 'O status informado é inválido.';
        }

        if ($reg['localizacao_codigo_pai'] !== NULL) {
            if (!ctype_digit($reg['localizacao_codigo_pai']) || (int) $reg['localizacao_codigo_pai'] <= 0) {
                $erros[] = 'A localização superior informada é inválida.';
            } else {
                $localizacao_pai = $this->localizacao_model->buscar_por_codigo(
                    (int) $reg['localizacao_codigo_pai']
                );

                if (!$localizacao_pai) {
                    $erros[] = 'A localização superior informada não existe.';
                } elseif ((int) $localizacao_pai['ativo'] !== 1) {
                    $erros[] = 'A localização superior informada está inativa.';
                } elseif ($codigo !== NULL && (int) $reg['localizacao_codigo_pai'] === (int) $codigo) {
                    $erros[] = 'Uma localização não pode ser superior a ela mesma.';
                }
            }
        }

        if ($codigo !== NULL && empty($erros)) {
            $tipo_atual = $this->localizacao_tipo_documento_model->buscar_por_localizacao(
                $codigo
            );

            $tipo_atual_codigo = $tipo_atual
                ? (int) $tipo_atual['tipo_documento_codigo']
                : NULL;

            $novo_tipo_codigo = $tipo_documento_codigo !== NULL
                ? (int) $tipo_documento_codigo
                : NULL;

            if ($tipo_atual_codigo !== $novo_tipo_codigo && $this->documento_model->possui_documentos($codigo)) {
                if (
                    $tipo_atual_codigo === NULL &&
                    $novo_tipo_codigo !== NULL &&
                    !$this->documento_model->possui_tipo_diferente_na_localizacao(
                        $codigo,
                        $novo_tipo_codigo
                    )
                ) {
                    // Permite configurar localizações antigas quando todos
                    // os documentos existentes pertencem ao mesmo tipo.
                } else {
                    $erros[] = 'Não é possível alterar o tipo de documento da localização enquanto houver documentos vinculados.';
                }
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

        if ($tipo_documento_codigo !== NULL) {
            $tipo_documento_codigo = (int) $tipo_documento_codigo;
        }

        return [
            'sucesso' => TRUE,
            'dados' => $reg,
            'tipo_documento_codigo' => $tipo_documento_codigo
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
