<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perfil extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->controle_acesso->valida_permissao('perfis.gerenciar');

        $this->load->library('auditoria');
        $this->load->database();
        $this->load->model('perfil_model');
        $this->load->model('permissao_model');
        $this->load->model('perfil_permissao_model');
    }

    public function index()
    {
        $this->listar();
        return;
    }

    public function listar()
    {
        $dados['perfis'] = $this->perfil_model->listar_tudo();

        $this->load->view('perfil/perfil_lista', $dados);
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

            $codigo = $this->perfil_model->cadastrar(
                $resultado['dados']
            );

            $sincronizado = $codigo
                ? $this->perfil_permissao_model->sincronizar(
                    $codigo,
                    $resultado['permissoes']
                )
                : FALSE;

            $dados_auditoria = (
                $codigo &&
                $sincronizado
            )
                ? $this->preparar_dados_auditoria_perfil(
                    $codigo
                )
                : FALSE;

            $auditoria_salva = $dados_auditoria
                ? $this->auditoria->registrar(
                    'perfis',
                    'PERFIL_CADASTRADO',
                    'perfis',
                    $codigo,
                    NULL,
                    $dados_auditoria
                )
                : FALSE;

            if (
                !$codigo ||
                !$sincronizado ||
                !$dados_auditoria ||
                !$auditoria_salva ||
                $this->db->trans_status() === FALSE
            ) {
                $this->db->trans_rollback();

                resposta_json(
                    FALSE,
                    'Não foi possível cadastrar o perfil.',
                    [],
                    500
                );
            }

            $this->db->trans_commit();

            resposta_json(
                TRUE,
                'Perfil cadastrado com sucesso.',
                ['codigo' => $codigo],
                201
            );
        }

        $dados = $this->preparar_formulario();

        $this->load->view('perfil/perfil_form', $dados);
    }

    public function atualizar($codigo = NULL)
    {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            show_404();
        }

        $codigo = (int) $codigo;
        $perfil = $this->perfil_model->buscar_por_codigo($codigo);

        if (!$perfil) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $resultado = $this->validar(
                $this->input->post(),
                $perfil
            );

            if (!$resultado['sucesso']) {
                resposta_json(
                    FALSE,
                    'Verifique os campos informados.',
                    ['erros' => $resultado['erros']],
                    422
                );
            }

            $dados_anteriores =
                $this->preparar_dados_auditoria_perfil(
                    $codigo
                );

            $this->db->trans_begin();

            $atualizado = $this->perfil_model->atualizar(
                $codigo,
                $resultado['dados']
            );

            $sincronizado = $atualizado
                ? $this->perfil_permissao_model->sincronizar(
                    $codigo,
                    $resultado['permissoes']
                )
                : FALSE;

            $dados_novos = (
                $atualizado &&
                $sincronizado
            )
                ? $this->preparar_dados_auditoria_perfil(
                    $codigo
                )
                : FALSE;

            $auditoria_salva = (
                $dados_anteriores &&
                $dados_novos
            )
                ? $this->auditoria->registrar(
                    'perfis',
                    'PERFIL_ATUALIZADO',
                    'perfis',
                    $codigo,
                    $dados_anteriores,
                    $dados_novos
                )
                : FALSE;

            if (
                !$dados_anteriores ||
                !$atualizado ||
                !$sincronizado ||
                !$dados_novos ||
                !$auditoria_salva ||
                $this->db->trans_status() === FALSE
            ) {
                $this->db->trans_rollback();

                resposta_json(
                    FALSE,
                    'Não foi possível atualizar o perfil.',
                    [],
                    500
                );
            }

            $this->db->trans_commit();

            resposta_json(
                TRUE,
                'Perfil atualizado com sucesso.',
                ['codigo' => $codigo]
            );
        }

        $dados = $this->preparar_formulario($perfil);

        $this->load->view('perfil/perfil_form', $dados);
    }

    public function excluir($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            resposta_json(
                FALSE,
                'Não foi possível identificar o perfil.',
                ['erros' => ['O código do perfil é inválido.']],
                422
            );
        }

        $codigo = (int) $codigo;
        $perfil = $this->perfil_model->buscar_por_codigo($codigo);

        if (!$perfil) {
            resposta_json(
                FALSE,
                'Perfil não encontrado ou já excluído.',
                [],
                404
            );
        }

        if ($codigo === (int) $this->controle_acesso->get('perfil_codigo')) {
            resposta_json(
                FALSE,
                'Não é possível excluir o perfil atualmente autenticado.',
                [
                    'erros' => [
                        'Altere o perfil da sua conta antes de excluir este registro.'
                    ]
                ],
                422
            );
        }

        if ($this->perfil_model->possui_usuarios($codigo)) {
            resposta_json(
                FALSE,
                'Não é possível excluir um perfil que possui usuários.',
                [
                    'erros' => [
                        'Altere o perfil dos usuários vinculados antes de continuar.'
                    ]
                ],
                422
            );
        }

        $this->db->trans_begin();

        $dados_anteriores =
            $this->preparar_dados_auditoria_perfil(
                $codigo
            );

        $excluido = $this->perfil_model->excluir(
            $codigo
        );

        $auditoria_salva = (
            $dados_anteriores &&
            $excluido
        )
            ? $this->auditoria->registrar(
                'perfis',
                'PERFIL_EXCLUIDO',
                'perfis',
                $codigo,
                $dados_anteriores,
                NULL
            )
            : FALSE;

        if (
            !$dados_anteriores ||
            !$excluido ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível excluir o perfil.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Perfil excluído com sucesso.',
            ['codigo' => $codigo]
        );
    }

    private function preparar_dados_auditoria_perfil($codigo)
    {
        $perfil = $this->perfil_model->buscar_por_codigo(
            (int) $codigo
        );

        if (!$perfil) {
            return FALSE;
        }

        $permissoes =
            $this->permissao_model
                ->listar_chaves_por_perfil($codigo);

        sort($permissoes);

        return [
            'perfil' => [
                'codigo' => (int) $perfil['codigo'],
                'nome' => $perfil['nome'],
                'chave' => $perfil['chave']
            ],
            'permissoes' => $permissoes
        ];
    }

    private function preparar_formulario($perfil = NULL)
    {
        $permissoes = $this->permissao_model->listar_todas();

        return [
            'perfil' => $perfil,
            'modulos' => $this->agrupar_permissoes($permissoes),
            'permissoes_selecionadas' => $perfil
                ? $this->perfil_permissao_model->listar_codigos_por_perfil(
                    $perfil['codigo']
                )
                : []
        ];
    }

    private function agrupar_permissoes($permissoes)
    {
        $modulos = [];

        foreach ($permissoes as $permissao) {
            $modulo_codigo = (int) $permissao['modulo_codigo'];

            if (!isset($modulos[$modulo_codigo])) {
                $modulos[$modulo_codigo] = [
                    'codigo' => $modulo_codigo,
                    'nome' => $permissao['modulo_nome'],
                    'chave' => $permissao['modulo_chave'],
                    'permissoes' => []
                ];
            }

            $modulos[$modulo_codigo]['permissoes'][] = $permissao;
        }

        return array_values($modulos);
    }

    private function validar($reg, $perfil_atual = NULL)
    {
        $nome = trim($reg['nome'] ?? '');
        $chave = $perfil_atual
            ? $perfil_atual['chave']
            : strtolower(trim($reg['chave'] ?? ''));

        $permissoes_informadas = isset($reg['permissoes']) &&
            is_array($reg['permissoes'])
                ? $reg['permissoes']
                : [];

        $erros = [];

        if ($nome === '') {
            $erros[] = 'O campo Nome é obrigatório.';
        } elseif (strlen($nome) > 100) {
            $erros[] = 'O campo Nome deve possuir no máximo 100 caracteres.';
        }

        if ($chave === '') {
            $erros[] = 'O campo Chave é obrigatório.';
        } elseif (
            strlen($chave) > 50 ||
            !preg_match('/^[a-z0-9_]+$/', $chave)
        ) {
            $erros[] = 'A chave deve conter apenas letras minúsculas, números e _.';
        } elseif ($this->perfil_model->chave_em_uso(
            $chave,
            $perfil_atual['codigo'] ?? NULL
        )) {
            $erros[] = 'A chave informada já está em uso.';
        }

        $permissoes = [];

        foreach ($permissoes_informadas as $permissao_codigo) {
            $permissao_codigo = (string) $permissao_codigo;

            if (!ctype_digit($permissao_codigo)) {
                $erros[] = 'Foi informada uma permissão inválida.';
                break;
            }

            $permissoes[] = (int) $permissao_codigo;
        }

        $permissoes = array_values(array_unique($permissoes));

        if (empty($permissoes)) {
            $erros[] = 'Selecione pelo menos uma permissão.';
        } else {
            $permissoes_validas = $this->permissao_model
                ->listar_codigos_validos($permissoes);

            sort($permissoes);
            sort($permissoes_validas);

            if ($permissoes !== $permissoes_validas) {
                $erros[] = 'Uma ou mais permissões informadas são inválidas.';
            }
        }

        if (
            $perfil_atual &&
            (int) $perfil_atual['codigo'] ===
                (int) $this->controle_acesso->get('perfil_codigo')
        ) {
            $chaves = $this->permissao_model->listar_chaves_por_codigos(
                $permissoes
            );

            if (
                !in_array('perfis.gerenciar', $chaves, TRUE) ||
                !in_array('usuarios.gerenciar', $chaves, TRUE)
            ) {
                $erros[] = 'O perfil autenticado deve manter as permissões de gerenciar perfis e usuários.';
            }
        }

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => array_values(array_unique($erros))
            ];
        }

        return [
            'sucesso' => TRUE,
            'dados' => [
                'nome' => $nome,
                'chave' => $chave
            ],
            'permissoes' => $permissoes
        ];
    }
}
