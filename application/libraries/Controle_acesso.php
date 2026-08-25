<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Controle_acesso
{
    protected $CI;

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
            'logado' => TRUE
        ];

        $this->CI->session->set_userdata('usuario', $dados_sessao);
    }

    public function logado()
    {
        $usuario = $this->CI->session->userdata('usuario');
        return (
            is_array($usuario) &&
            !empty($usuario['codigo']) &&
            isset($usuario['logado']) &&
            $usuario['logado'] === TRUE
        );
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
    }
}