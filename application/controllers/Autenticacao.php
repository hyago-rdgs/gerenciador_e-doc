<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autenticacao extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usuario_model');
    }

    public function index()
    {
        $this->login();
        return;
    }

    public function login()
    {
        if ($this->controle_acesso->logado()) {
            redirect(base_url());
            return;
        }

        if ($this->input->method() === 'post') {
            $credenciais = [
                'usuario' => $this->input->post('usuario', TRUE),
                'senha' => $this->input->post('senha')
            ];

            $resultado = $this->autenticar($credenciais);

            if (!$resultado['sucesso']) {
                resposta_json(
                    FALSE,
                    'Não foi possível realizar o login.',
                    ['erros' => $resultado['erros']],
                    401
                );
            }

            $this->controle_acesso->criar($resultado['dados']);

            resposta_json(
                TRUE,
                'Login realizado com sucesso.',
                [],
                200
            );
        }

        $this->load->view('autenticacao/login');
    }

    private function autenticar(array $reg)
    {
        $erros = [];

        $usuario_informado = isset($reg['usuario']) && is_string($reg['usuario'])
            ? trim($reg['usuario'])
            : '';

        $senha_informada = isset($reg['senha']) && is_string($reg['senha'])
            ? $reg['senha']
            : '';

        if ($usuario_informado === '') {
            $erros[] = 'O campo Usuário é obrigatório.';
        }

        if ($senha_informada === '') {
            $erros[] = 'O campo Senha é obrigatório.';
        }

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        $usuario = $this->usuario_model->buscar_por_usuario(
            $usuario_informado
        );

        if (
            !$usuario ||
            !isset($usuario['senha']) ||
            !password_verify($senha_informada, $usuario['senha'])
        ) {
            return [
                'sucesso' => FALSE,
                'erros' => ['Usuário ou senha inválidos.']
            ];
        }

        return [
            'sucesso' => TRUE,
            'dados' => [
                'codigo' => $usuario['codigo'],
                'nome' => $usuario['nome'],
                'usuario' => $usuario['usuario'],
                'email' => $usuario['email']
            ]
        ];
    }

    public function logout()
    {
        $this->controle_acesso->destruir();

        redirect(base_url('autenticacao/login'));
        return;
    }
}
