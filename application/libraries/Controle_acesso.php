<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Controle_acesso
{
    protected $CI;
    private $usuario_validado = NULL;
    private $permissoes_usuario = NULL;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('session');
    }

    public function criar(array $dados_usuario)
    {
        $this->CI->session->sess_regenerate(TRUE);

        $dados_sessao = [
            'codigo' => $dados_usuario['codigo'],
            'nome' => $dados_usuario['nome'],
            'usuario' => $dados_usuario['usuario'],
            'email' => $dados_usuario['email'],
            'perfil_codigo' => $dados_usuario['perfil_codigo'],
            'perfil' => $dados_usuario['perfil'],
            'perfil_nome' => $dados_usuario['perfil_nome'],
            'logado' => TRUE
        ];

        $this->CI->session->set_userdata('usuario', $dados_sessao);

        $this->usuario_validado = TRUE;
        $this->permissoes_usuario = NULL;
    }

    public function logado()
    {
        if ($this->usuario_validado !== NULL) {
            return $this->usuario_validado;
        }

        $usuario_sessao = $this->CI->session->userdata('usuario');

        if (
            !is_array($usuario_sessao) ||
            empty($usuario_sessao['codigo']) ||
            !isset($usuario_sessao['logado']) ||
            $usuario_sessao['logado'] !== TRUE
        ) {
            $this->usuario_validado = FALSE;
            return FALSE;
        }

        $this->CI->load->model('usuario_model');

        $usuario = $this->CI->usuario_model->buscar_para_sessao(
            (int) $usuario_sessao['codigo']
        );

        if (!$usuario) {
            $this->destruir();
            return FALSE;
        }

        $this->CI->session->set_userdata('usuario', [
            'codigo' => $usuario['codigo'],
            'nome' => $usuario['nome'],
            'usuario' => $usuario['usuario'],
            'email' => $usuario['email'],
            'perfil_codigo' => $usuario['perfil_codigo'],
            'perfil' => $usuario['perfil'],
            'perfil_nome' => $usuario['perfil_nome'],
            'logado' => TRUE
        ]);

        $this->usuario_validado = TRUE;
        return TRUE;
    }

    public function valida_acesso()
    {
        if ($this->logado()) {
            return;
        }

        if ($this->CI->input->is_ajax_request()) {
            resposta_json(
                FALSE,
                'Sua sessão expirou. Entre novamente no sistema.',
                [
                    'redirecionar' => base_url('autenticacao/login')
                ],
                401
            );
        }

        redirect(base_url('autenticacao/login'));
    }

    public function tem_permissao($chave)
    {
        if (!$this->logado()) {
            return FALSE;
        }

        if ($this->permissoes_usuario === NULL) {
            $this->CI->load->model('permissao_model');

            $this->permissoes_usuario = $this->CI
                ->permissao_model
                ->listar_chaves_por_perfil(
                    (int) $this->get('perfil_codigo')
                );
        }

        return in_array(
            $chave,
            $this->permissoes_usuario,
            TRUE
        );
    }

    public function valida_permissao($chave)
    {
        $this->valida_acesso();

        if ($this->tem_permissao($chave)) {
            return;
        }

        if ($this->CI->input->is_ajax_request()) {
            resposta_json(
                FALSE,
                'Você não possui permissão para realizar esta operação.',
                [],
                403
            );
        }

        show_error(
            'Você não possui permissão para acessar este recurso.',
            403,
            'Acesso negado'
        );
    }

    public function get($propriedade = NULL)
    {
        $usuario = $this->CI->session->userdata('usuario');

        if (!is_array($usuario)) {
            return NULL;
        }

        if ($propriedade === NULL) {
            return $usuario;
        }

        return array_key_exists($propriedade, $usuario)
            ? $usuario[$propriedade]
            : NULL;
    }

    public function destruir()
    {
        $this->CI->session->unset_userdata('usuario');
        $this->CI->session->sess_destroy();

        $this->usuario_validado = FALSE;
        $this->permissoes_usuario = [];
    }
}
